<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AuthController extends Controller
{
    use HttpResponses;

    public function register(RegisterRequest $request): JsonResponse
    {
        $request->validated();
        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->domain,
            'database' => 'domain_' . rand(1000, 9999),
        ]);
        
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->generateToken();

        return $this->successResponse([
            'token' => $token,
            'user' => $user,
            'domain_name' => $tenant->domain
        ], 'Registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            return $this->errorResponse('Invalid Credentials', 401);
        }

        $user = User::where('email', $data['email'])->first();
        
        try {
            $token = $user->generateToken();
            return $this->successResponse([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tenant not found for the given user', 404);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(message: 'Successfully logged out');
    }
}
