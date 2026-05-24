<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('roles.index');
    }

    public function getAjaxData(Request $request)
    {
        $query = Role::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'name', 'name_en', 'created_at', 'action'];

        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($columnName, $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = Role::count();
        $filtered = (clone $query)->count();

        $data = $query->skip($request->input('start'))->take($request->input('length'))->get();

        $formatted = $data->map(function ($role) {
            $viewUrl = route('roles.show', $role->id);
            $editUrl = route('roles.edit', $role->id);

            return [
                "id" => $role->id,
                "name" => $role->getLocalizationName(),
                "created_at" => $role->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $formatted
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('type');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'name_en' => 'required|string|max:255|unique:roles,name_en',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'name_en' => $request->name_en
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('type');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'name_en' => 'required|string|max:255|unique:roles,name_en,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update([
            'name' => $request->name,
            'name_en' => $request->name_en
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['status' => 'success', 'message' => 'app.deleted successfully']);
    }
}
