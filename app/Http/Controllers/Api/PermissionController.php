<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
   
    public function index(): View
    {
        $permissions = Permission::withCount('roles')->latest()->paginate(15);

        return view('permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('permissions.create');
    }

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


    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('permissions.show', compact('permission'));
    }

    public function edit(Permission $permission): View
    {
        return view('permissions.edit', compact('permission'));
    }

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

   
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', __('Permission deleted successfully.'));
    }
}
