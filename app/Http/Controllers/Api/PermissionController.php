<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(): View
    {
        $permissions = Permission::withCount('roles')->latest()->paginate(15);

        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a permission.
     */
    public function create(): View
    {
        return view('permissions.create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'description' => 'nullable|string',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', __('Permission created successfully.'));
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the permission.
     */
    public function edit(Permission $permission): View
    {
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.show', $permission)
            ->with('success', __('Permission updated successfully.'));
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', __('Permission deleted successfully.'));
    }
}
