<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::with('authority');

        if ($request->has('authority_id')) {
            $query->where('authority_id', $request->authority_id);
        }

        $departments = $query->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data'    => $departments,
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $department = Department::with('authority')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $department,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $validated = $request->validate([
            'authority_id' => 'required|exists:authorities,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
        ]);

        $department = Department::create([
            'authority_id' => $validated['authority_id'],
            'name'         => $validated['name'],
            'description'  => $validated['description'] ?? null,
            'is_active'    => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully.',
            'data'    => $department->load('authority'),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'authority_id' => 'nullable|exists:authorities,id',
            'name'         => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
        ]);

        $department->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
            'data'    => $department->load('authority'),
        ], 200);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully.',
        ], 200);
    }
}