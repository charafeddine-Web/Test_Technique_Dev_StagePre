<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        // Check if user already exists
        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            throw new \Exception('User with this email already exists');
        }

        // Create user
        $user = $this->userRepository->create($data);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'User registered successfully'
        ];
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($credentials['email']);
        
        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ];
    }

    /**
     * Get current authenticated user
     *
     * @return \App\Models\User|null
     */
    public function getCurrentUser(): ?\App\Models\User
    {
        return Auth::user();
    }
}
