<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('admins.index');
    }

    public function getAjaxData(Request $request){
        $query = new Admin();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'name',
            'email',
            'created_at',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'id';
            if ($columnName === 'created_at') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Count totals (cloned for filtering)
        $total = Admin::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($admin) {
            $viewUrl = route('admins.show', $admin->id);
            $editUrl = route('admins.edit', $admin->id);
            $deleteUrl = route('admins.destroy', $admin->id);
            
            $adminRole = (app()->getLocale() == "ar") ? $admin->roles->pluck('name')->first() : $admin->roles->pluck('name_en')->first();
            $checked = ($admin->status) ? "checked" : "";
            $message = ($admin->status) ? __('app.are you sure you want to deactivate this admin?'): __('app.are you sure you want to activate this admin?');
            return [
                "id" => $admin->id,
                "name" => $admin->name,
                "email" => $admin->email,
                "role" => $adminRole,
                "status" => "<div class='form-check form-switch mt-2'>
                    <input class='form-check-input update-status' 
                        name='status' 
                        type='checkbox'
                        value='1' 
                        role='switch' 
                        id='status-$admin->id'
                        data-table='admins'
                        data-id='$admin->id'
                        data-message='$message'
                        $checked
                    />
                </div>",
                "created_at" => $admin->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="admins" data-id="'.$admin->id.'" title="'.__('app.delete').'">
                        <i class="fa-solid fa-trash"></i>
                    </button>
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

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $admin->assignRole($request->role);

        return redirect()->route('admins.index')->with('success', __('Admin created successfully.'));
    }

    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        $adminRole = (app()->getLocale() == "ar") ? $admin->roles->pluck('name')->first() : $admin->roles->pluck('name_en')->first();
        return view('admins.show', compact('admin', 'adminRole'));
    }

    /**
     * Show the form for editing the admin.
     */
    public function edit(Admin $admin)
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $adminRole = $admin->roles->pluck('name')->first();
        return view('admins.edit', compact('admin', 'roles', 'adminRole'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:admins,email,{$admin->id}",
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : $admin->password,
        ]);

        $admin->syncRoles([$request->role]);

        return redirect()->route('admins.index')->with('success', __('Admin updated successfully.'));
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(Admin $admin)
    {
        try {
            // Prevent self-delete
            if (auth()->id() === $admin->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('You cannot delete your own account.')
                ], 403);
            }
            $admin->email = $admin->email."_deleted";
            $admin->update();
            $admin->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the admin.')
            ], 500);
        }
    }

    public function change_status($id)
    {
        try {
            // Set only the selected semester to true
            $item = Admin::findOrFail($id);
            $item->status = !$item->status;
            $item->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'error'  => $e->getMessage()
            ]);
        }
    }
}
