<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $query = User::with(['role', 'authority', 'department']);

        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by authority
        if ($request->has('authority_id')) {
            $query->where('authority_id', $request->authority_id);
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name or phone
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(10);

        $users->getCollection()->transform(function ($user) {
            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'verification'  => $user->verification,
                'score'         => $user->score,
                'is_active'     => $user->is_active,
                'role'          => $user->role ? [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name,
                    'level' => $user->role->level,
                ] : null,
                'authority'     => $user->authority ? [
                    'id'   => $user->authority->id,
                    'name' => $user->authority->name,
                ] : null,
                'department'    => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
                'created_at'    => $user->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $users,
        ], 200);
    }

    public function show(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::with(['role', 'authority', 'department'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'verification' => $user->verification,
                'score'        => $user->score,
                'is_active'    => $user->is_active,
                'role'         => $user->role ? [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name,
                    'level' => $user->role->level,
                ] : null,
                'authority'    => $user->authority ? [
                    'id'   => $user->authority->id,
                    'name' => $user->authority->name,
                ] : null,
                'department'   => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
                'created_at'   => $user->created_at,
            ],
        ], 200);
    }

    public function assignRole(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'authority_id'  => 'nullable|exists:authorities,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update([
            'role_id'       => $request->role_id,
            'authority_id'  => $request->authority_id,
            'department_id' => $request->department_id,
        ]);

        $user->load(['role', 'authority', 'department']);

        return response()->json([
            'success' => true,
            'message' => 'User role assigned successfully.',
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'role'         => $user->role ? [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                ] : null,
                'authority'    => $user->authority ? [
                    'id'   => $user->authority->id,
                    'name' => $user->authority->name,
                ] : null,
                'department'   => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
            ],
        ], 200);
    }

    public function toggleActive(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.',
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }
}