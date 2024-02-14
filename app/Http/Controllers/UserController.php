<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'search' => 'nullable|string',
            'take'   => 'nullable|numeric',
            'skip'   => 'nullable|numeric'
        ]);

        $users = User::when(
            $request->search, 
            fn($q) => $q->where(
                'name', 'like', '%'.$request->search.'%'
            )->orWhere(
                'email', 'like', '%'.$request->search.'%'
            )
        )->when(
            $request->take,
            fn($q) => $q->take($request->take)
        )->when(
            $request->skip,
            fn($q) => $q->skip($request->skip)
        )->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Success fetch users.',
            'data'    => $users
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|confirmed',
            'role'     => 'required|in:ADMIN,USER'
        ]);

        try {
            $user = User::create([
                'name'     => ucwords($request->name),
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed add users.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success add users.',
            'data'    => $user
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch(\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success fetch users.',
            'data'    => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        $this->validate($request, [
            'name'     => 'required|string',
            'email'    => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at')->ignore($user->id)
            ],
            'role'     => 'required|in:ADMIN,USER'
        ]);

        try {
            $user->update([
                'name'     => ucwords($request->name),
                'email'    => $request->email,
                'role'     => $request->role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed update users.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success update users.',
            'data'    => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed delete.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success delete users.',
            'data'    => $user
        ], 200);
    }

    public function changePassword(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        $this->validate($request, [
            'password' => 'required|string|confirmed',
        ]);

        try {
            $user->update([
                'password'     => Hash::make($request->password),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed update user password.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success update user password.',
            'data'    => $user
        ], 200);
    }
}
