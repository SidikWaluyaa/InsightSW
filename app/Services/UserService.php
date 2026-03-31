<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'Viewer',
                'status' => (int) ($data['status'] ?? 1),
            ]);

            Log::info("User created: {$user->email} by ID " . Auth::id());
            return $user;
        });
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'] ?? $user->role,
                'status' => (int) ($data['status'] ?? $user->status),
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);
            Log::info("User updated: {$user->email} by ID " . Auth::id());
            return true;
        });
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(User $user): bool
    {
        if ($user->id === Auth::id()) {
            throw new \Exception("Anda tidak bisa menonaktifkan akun sendiri.");
        }

        $user->status = ($user->status == 1) ? 0 : 1;
        $user->save();
        
        Log::info("User status toggled: {$user->email} to " . ($user->status ? 'Active' : 'Inactive') . " by ID " . Auth::id());
        return true;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        if ($user->id === Auth::id()) {
            throw new \Exception("Anda tidak bisa menghapus akun sendiri.");
        }

        Log::warning("User deleting: {$user->email} by ID " . Auth::id());
        return $user->delete();
    }
}
