<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\AcademicLevel;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('classes.index');
    }

    public function getAjaxData(Request $request){
        $query = new ClassRoom();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'name',
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
        $total = ClassRoom::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($class) {
            $viewUrl = route('classes.show', $class->id);
            $editUrl = route('classes.edit', $class->id);
            $deleteUrl = route('classes.destroy', $class->id);

            return [
                "id" => $class->id,
                "name" => $class->getLocalizationName(),
                "created_at" => $class->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="classes" data-id="'.$class->id.'" title="'.__('app.delete').'">
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
        $academicLevels = AcademicLevel::all();
        return view('classes.create', compact('academicLevels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'academic_level_id' => 'nullable|exists:academic_levels,id'
        ]);

        ClassRoom::create($request->all());

        return redirect()->route('classes.index')->with('success', __('created successfully'));
    }

    public function show(ClassRoom $class)
    {
        $class->load('academicLevel');
        return view('classes.show', compact('class'));
    }

    public function edit(ClassRoom $class)
    {
        $academicLevels = AcademicLevel::all();
        return view('classes.edit', compact('class', 'academicLevels'));
    }

    public function update(Request $request, ClassRoom $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'academic_level_id' => 'nullable|exists:academic_levels,id'
        ]);

        $class->update($request->all());

        return redirect()->route('classes.index')->with('success', __('app.updated successfully'));
    }

    public function destroy(ClassRoom $class)
    {
        try {
            if ($class->courses()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('app.class_cannot_be_deleted_has_courses'),
                ], 422);
            }

            $class->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.deletion_failed'),
            ], 500);
        }
    }
}
