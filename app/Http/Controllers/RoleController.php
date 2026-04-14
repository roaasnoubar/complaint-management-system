<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->latest()->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a role.
     */
    public function create(): View
    {
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'level' => 'required|integer|min:1',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'type' => $validated['type'],
            'level' => $validated['level'],
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('success', __('Role created successfully.'));
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);

        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the role.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::all();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'level' => 'required|integer|min:1',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'type' => $validated['type'],
            'level' => $validated['level'],
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.show', $role)
            ->with('success', __('Role updated successfully.'));
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', __('Role deleted successfully.'));
    }
}
