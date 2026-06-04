<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
            'description' => 'nullable|string',
        ]);

        Role::create($request->all());

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль создана');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
         return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($request->all());

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
         if ($role->users()->count() > 0) {
            return back()->with('error', 'Нельзя удалить роль, у которой есть пользователи');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль удалена');
    }
}
