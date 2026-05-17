<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function getAllTeachers(): JsonResponse
    {
        $teachers = User::where('role', UserRole::Teacher)->select('id', 'name', 'email')->orderBy('name')->get();

        return response()->json([
            'message' => 'Teachers fetched successfully.',
            'data' => $teachers,
        ]);
    }
}
