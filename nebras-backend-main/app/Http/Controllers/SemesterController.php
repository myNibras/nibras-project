<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('semesters.index');
    }

    public function getAjaxData(Request $request){
        $query = new Semester();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'title',
            'type',
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
        $total = Semester::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($semester) {
            $viewUrl = route('semesters.show', $semester->id);
            $editUrl = route('semesters.edit', $semester->id);
            $deleteUrl = route('semesters.destroy', $semester->id);
            $checked = ($semester->status) ? "checked" : "";
            $message = ($semester->status) ? __("app.before deactivating this semester, choose another semester from the below list to be activated instead of this semester.") : __("app.by activating this semester, its information will be displayed on the platform, and the currently active semester will be deactivated.");
            return [
                "id" => $semester->id,
                "title" => $semester->getLocalizationTitle(),
                "type" => $semester->getTypeNameAttribute(),
                "status" => "<div class='form-check form-switch mt-2'>
                    <input class='form-check-input update-status' 
                        name='status' 
                        type='checkbox'
                        value='1' 
                        role='switch' 
                        id='status-$semester->id'
                        data-table='semesters'
                        data-id='$semester->id'
                        data-message='$message'
                        $checked
                    />
                </div>",
                "created_at" => $semester->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="semesters" data-id="'.$semester->id.'" title="'.__('app.delete').'">
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

    public function create()
    {
        return view('semesters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'type' => 'required|in:1,2',
            'status' => 'sometimes|boolean',
        ]);
        Semester::create($request->all());

        return redirect()->route('semesters.index')
            ->with('success', __('Semester created successfully.'));
    }

    public function show(Semester $semester)
    {
        return view('semesters.show', compact('semester'));
    }

    public function edit(Semester $semester)
    {
        return view('semesters.edit', compact('semester'));
    }

    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'type' => 'required|in:1,2',
            'status' => 'sometimes|boolean',
        ]);

        $semester->update($request->all());

        return redirect()->route('semesters.index')
            ->with('success', __('Semester updated successfully.'));
    }

    public function destroy(Semester $semester)
    {
        try {
            $semester->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the student.')
            ], 500);
        }
    }

    public function change_status($id)
    {
        try {
            // Set all semesters to false
            Semester::query()->update(['status' => false]);

            // Set only the selected semester to true
            $item = Semester::findOrFail($id);
            $item->status = true;
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
