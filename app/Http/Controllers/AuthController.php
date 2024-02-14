<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where(
            'email',
            $request->email
        )
        ->first();

        if (empty($user)) {
            return response()->json([
                'code'    => 400,
                'message' => 'User not found.',
            ], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'code'    => 400,
                'message' => 'Wrong password.',
            ], 400);
        }

        $credentials = [
            'tokenType'   => 'Bearer',
            'accessToken' => $user->createToken($user->name)->accessToken
        ];

        return response()->json([
            'code'    => 200,
            'message' => 'Success login.',
            'data'    => $credentials
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'code'    => 200,
            'message' => 'Success logout.'
        ], 200);
    }
}
