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
            'duration_days' => 'nullable|integer|min:1',
            'duration' => 'nullable|string', // Support Flutter string format
            'symptoms' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        // Convert duration string to days if provided
        $durationDays = $request->duration_days;
        if (!$durationDays && $request->duration) {
            $durationDays = $this->parseDurationToDays($request->duration);
        }

        $consultation = $request->user()->consultations()->create([
            'patient_age' => $request->patient_age,
            'patient_sex' => $request->patient_sex,
            'chief_complaint' => $request->chief_complaint,
            'duration_days' => $durationDays ?? 1,
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

    /**
     * Sync offline consultations (batch upload).
     */
    public function syncOffline(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consultations' => 'required|array',
            'consultations.*.patient_age' => 'required|integer|min:0',
            'consultations.*.patient_sex' => 'required|in:male,female,other',
            'consultations.*.chief_complaint' => 'required|string',
            'consultations.*.duration' => 'nullable|string',
            'consultations.*.symptoms' => 'required|array',
            'consultations.*.notes' => 'nullable|string',
            'consultations.*.created_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        $synced = [];
        $failed = [];

        foreach ($request->consultations as $consultationData) {
            try {
                $durationDays = $this->parseDurationToDays($consultationData['duration'] ?? 'Less than 24 hours');

                $consultation = $request->user()->consultations()->create([
                    'patient_age' => $consultationData['patient_age'],
                    'patient_sex' => $consultationData['patient_sex'],
                    'chief_complaint' => $consultationData['chief_complaint'],
                    'duration_days' => $durationDays,
                    'symptoms' => $consultationData['symptoms'],
                    'notes' => $consultationData['notes'] ?? null,
                    'status' => 'draft',
                    'created_at' => $consultationData['created_at'] ?? now(),
                ]);

                $synced[] = new ConsultationResource($consultation);
            } catch (\Exception $e) {
                $failed[] = [
                    'data' => $consultationData,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->success([
            'synced' => $synced,
            'failed' => $failed,
            'synced_count' => count($synced),
            'failed_count' => count($failed),
        ], 'Sync completed', 200, SystemCode::CONSULTATION_SYNC_SUCCESS);
    }

    /**
     * Parse duration string to days.
     */
    private function parseDurationToDays(string $duration): int
    {
        $duration = strtolower($duration);

        if (str_contains($duration, 'less than 24') || str_contains($duration, '< 24')) {
            return 1;
        } elseif (str_contains($duration, '1-3 days') || str_contains($duration, '1 to 3')) {
            return 2;
        } elseif (str_contains($duration, '4-7 days') || str_contains($duration, '4 to 7')) {
            return 5;
        } elseif (str_contains($duration, 'more than 1 week') || str_contains($duration, '> 1 week')) {
            return 10;
        }

        // Default fallback
        return 1;
    }
}
