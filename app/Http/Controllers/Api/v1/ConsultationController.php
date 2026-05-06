<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\SystemCode;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\v1\ConsultationResource;
use App\Http\Resources\v1\ImageResource;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends ApiController
{
    /**
     * List paginated consultations for the authenticated CHW.
     */
    public function index(Request $request): JsonResponse
    {
        $consultations = $request->user()->consultations()
            ->latest()
            ->paginate($request->query('per_page', 15));

        return $this->success([
            'consultations' => ConsultationResource::collection($consultations),
            'pagination' => [
                'current_page' => $consultations->currentPage(),
                'last_page' => $consultations->lastPage(),
                'total' => $consultations->total(),
            ]
        ], 'Consultations retrieved successfully', 200, SystemCode::CONSULTATION_RETRIEVED);
    }

    /**
     * Create a new consultation record.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'patient_age' => 'required|integer|min:0',
            'patient_sex' => 'required|in:male,female,other',
            'chief_complaint' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'symptoms' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        $consultation = $request->user()->consultations()->create([
            'patient_age' => $request->patient_age,
            'patient_sex' => $request->patient_sex,
            'chief_complaint' => $request->chief_complaint,
            'duration_days' => $request->duration_days,
            'symptoms' => $request->symptoms,
            'notes' => $request->notes,
            'status' => 'draft', // Initial status
        ]);

        return $this->success(
            new ConsultationResource($consultation),
            'Consultation created successfully',
            201,
            SystemCode::CONSULTATION_CREATED
        );
    }

    /**
     * Retrieve a single consultation.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $consultation = $request->user()->consultations()->with('images')->find($id);

        if (!$consultation) {
            return $this->error('Consultation not found', 404, null, SystemCode::ERR_CONSULTATION_NOT_FOUND);
        }

        return $this->success(new ConsultationResource($consultation), 'Consultation retrieved successfully', 200, SystemCode::CONSULTATION_RETRIEVED);
    }

    /**
     * Upload images for a consultation.
     */
    public function uploadImages(Request $request, $id): JsonResponse
    {
        $consultation = $request->user()->consultations()->find($id);

        if (!$consultation) {
            return $this->error('Consultation not found', 404, null, SystemCode::ERR_CONSULTATION_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        // Check current image count
        if ($consultation->images()->count() + count($request->file('images')) > 3) {
            return $this->error('A consultation cannot have more than 3 images in total.', 422, null, SystemCode::ERR_IMAGE_LIMIT_EXCEEDED);
        }

        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('consultations/' . $consultation->id, 'public');
            
            $imgRecord = $consultation->images()->create([
                'file_path' => $path,
                'file_size' => $image->getSize(),
            ]);

            $uploadedImages[] = new ImageResource($imgRecord);
        }

        return $this->success($uploadedImages, 'Images uploaded successfully', 201, SystemCode::IMAGE_UPLOAD_SUCCESS);
    }
}
