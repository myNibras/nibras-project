<?php

namespace App\Http\Controllers;

use App\Models\AcademicLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicLevelController extends Controller
{
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('academic_levels.index');
    }

    public function getAjaxData(Request $request){
        $query = new AcademicLevel();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'title',
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
        $total = AcademicLevel::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($level) {
            $viewUrl = route('academic-levels.show', $level->id);
            $editUrl = route('academic-levels.edit', $level->id);
            $deleteUrl = route('academic-levels.destroy', $level->id);

            return [
                "id" => $level->id,
                "image" => $level->image
                    ? "<img src='".$level->image."' style='height:40px;' alt=''>"
                    : '—',
                "title" => $level->getLocalizationTitle(),
                "created_at" => $level->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="academic-levels" data-id="'.$level->id.'" title="'.__('app.delete').'">
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
        return view('academic_levels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'quote_icon_color' => ['required', Rule::in(AcademicLevel::QUOTE_ICON_COLORS)],
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'thumbnail_male' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'thumbnail_female' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $academic_level = AcademicLevel::create($request->all());

        $academic_level->addMedia($request->file('image'))->toMediaCollection('academic_level');

        if ($request->hasFile('thumbnail_male')) {
            $academic_level->addMedia($request->file('thumbnail_male'))->toMediaCollection('academic_level_thumbnail_male');
        }
        if ($request->hasFile('thumbnail_female')) {
            $academic_level->addMedia($request->file('thumbnail_female'))->toMediaCollection('academic_level_thumbnail_female');
        }
        
        return redirect()->route('academic-levels.index')
            ->with('success', __('Academic Level created successfully.'));
    }

    public function show(AcademicLevel $academicLevel)
    {
        return view('academic_levels.show', compact('academicLevel'));
    }

    public function edit(AcademicLevel $academicLevel)
    {
        return view('academic_levels.edit', compact('academicLevel'));
    }

    public function update(Request $request, AcademicLevel $academicLevel)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'quote_icon_color' => ['required', Rule::in(AcademicLevel::QUOTE_ICON_COLORS)],
            'image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:10240',
            'thumbnail_male' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'thumbnail_female' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $academicLevel->update($request->all());

        if ($request->hasFile('image')) {
            $academicLevel->clearMediaCollection('academic_level');
            $academicLevel->addMedia($request->file('image'))->toMediaCollection('academic_level');
        }

        if ($request->hasFile('thumbnail_male')) {
            $academicLevel->clearMediaCollection('academic_level_thumbnail_male');
            $academicLevel->addMedia($request->file('thumbnail_male'))->toMediaCollection('academic_level_thumbnail_male');
        }

        if ($request->hasFile('thumbnail_female')) {
            $academicLevel->clearMediaCollection('academic_level_thumbnail_female');
            $academicLevel->addMedia($request->file('thumbnail_female'))->toMediaCollection('academic_level_thumbnail_female');
        }

        return redirect()->route('academic-levels.index')
            ->with('success', __('Academic Level updated successfully.'));
    }
    
    public function destroy(AcademicLevel $academicLevel)
    {
        try {
            $academicLevel->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the academic Level.')
            ], 500);
        }
    }
}
