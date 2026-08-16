<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
