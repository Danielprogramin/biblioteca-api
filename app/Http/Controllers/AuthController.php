<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    use ApiResponses;

    public function login(LoginRequest $request)
    {

        if (!Auth::attempt($request->only("username", "password"))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('username', $request->username);

        return $this->ok(
            'Login successful',
            [
                'token' => $user->createToken('auth_token', ['*'], now()->addMonth())->plainTextToken
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logout successful');
    }

}
