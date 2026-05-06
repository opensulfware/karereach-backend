<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\SystemCode;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Register a new Community Health Worker.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'pin' => 'required|string|size:6',
            'region' => 'nullable|string',
            'health_program_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'pin' => Hash::make($request->pin),
            'region' => $request->region,
            'health_program_id' => $request->health_program_id,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful', 201, SystemCode::AUTH_REGISTER_SUCCESS);
    }

    /**
     * Login a CHW using phone and PIN.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors(), SystemCode::ERR_VALIDATION);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->pin, $user->pin)) {
            return $this->error('Invalid credentials', 401, null, SystemCode::ERR_AUTH_INVALID_CREDENTIALS);
        }

        if (!$user->is_active) {
            return $this->error('Account is deactivated', 403, null, SystemCode::ERR_AUTH_ACCOUNT_DEACTIVATED);
        }

        $user->update(['last_login_at' => now()]);
        
        // Revoke old tokens if necessary
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful', 200, SystemCode::AUTH_LOGIN_SUCCESS);
    }

    /**
     * Logout the current CHW.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully', 200, SystemCode::AUTH_LOGOUT_SUCCESS);
    }
}
