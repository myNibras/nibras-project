<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\Course;
use App\Models\AcademicLevel;
use App\Models\Teacher;
use App\Models\Curriculum;
use App\Models\Unit;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('courses.index');
    }

    public function getAjaxData(Request $request){
        $query = Course::query();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'image',
            'courses.title',
            'semesters.title',
            'semester_type',
            'teachers.name',
            'status',
            'courses.updated_at',
            'action',
        ];

        // Sorting logic (only DB columns; action/semester/teacher are not sortable without joins)
        $sortableColumns = ['courses.updated_at', 'courses.title'];
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'courses.updated_at';
            $orderBy = in_array($columnName, $sortableColumns, true) ? $columnName : 'courses.updated_at';
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Count totals (cloned for filtering)
        $total = Course::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($course) {
            $viewUrl = route('courses.show', $course->id);
            $editUrl = route('courses.edit', $course->id);
            $deleteUrl = route('courses.destroy', $course->id);

            $checked = ($course->status) ? "checked" : "";
            $message = ($course->status) ? __('app.are you sure you want to deactivate this course?'): __('app.are you sure you want to activate this course?');

            return [
                "updated_at" => optional($course->updated_at)->format('Y-m-d H:i'),
                "image" => "<img src='".$course->image."'/ style='height:40px;'>",
                "title" => $course->getLocalizationTitle(),
                "semester" => $course->semester->getLocalizationTitle(),
                "semester_type" => $course->semester->getTypeNameAttribute(),
                "teacher" => ($course->teacher) ? $course->teacher->getLocalizationName() : '',
                "status" => "<div class='form-check form-switch mt-2'>
                    <input class='form-check-input update-status' 
                        name='status' 
                        type='checkbox'
                        value='1' 
                        role='switch' 
                        id='status-$course->id'
                        data-table='courses'
                        data-id='$course->id'
                        data-message='$message'
                        $checked
                    />
                </div>",
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button class="btn btn-secondary me-1 btn-copy-course" title="'.__('app.copy').'" data-id="'.$course->id.'">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-delete" data-table="courses" data-id="'.$course->id.'" title="'.__('app.delete').'">
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $semesters = Semester::all();
        $levels = AcademicLevel::all();
        $teachers = Teacher::getActive()->get();
        $classes = ClassRoom::all();

        return view('courses.create', compact('semesters', 'levels', 'teachers', 'classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
        public function store(Request $request)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'short_description' => 'required|string',
            'short_description_en' => 'required|string',
            'semester_id' => 'required|exists:semesters,id',
            'academic_level_id' => 'required|exists:academic_levels,id',
            'class_id' => 'required|exists:classes,id',

            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:one-off,monthly,both',
            'semester_months' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->payment_type, ['monthly', 'both']) && empty($value)) {
                        $fail(__('validation.required', ['attribute' => __('app.semester_months')]));
                    }
                },
                'nullable',
                'integer',
                'min:1'
            ],
            'monthly_amount' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->payment_type, ['monthly', 'both']) && empty($value)) {
                        $fail(__('validation.required', ['attribute' => __('app.monthly_amount')]));
                    }
                },
                'nullable',
                'numeric',
                'min:0'
            ],

            'course_type' => 'required|in:recorded,online',
            'course_link' => 'required_if:course_type,online|nullable|string|max:255',

            'teacher_id' => 'required|exists:teachers,id',
            'duration' => 'required|string|max:255',
            'duration_en' => 'required|string|max:255',
            'schedule' => 'required|string|max:255',
            'schedule_en' => 'required|string|max:255',
            'available_seats' => 'nullable|integer|min:0',

            'description' => 'nullable|string',
            'description_en' => 'nullable|string',

            'curriculums' => 'nullable|array',
            'curriculums.*.title' => 'required_with:curriculums|string|max:255',
            'curriculums.*.title_en' => 'required_with:curriculums|string|max:255',
            'curriculums.*.units' => 'nullable|array',
            'curriculums.*.units.*.title' => 'required_with:curriculums.*.units|string|max:255',
            'curriculums.*.units.*.title_en' => 'required_with:curriculums.*.units|string|max:255',
            'curriculums.*.units.*.registered_students' => 'nullable|boolean',
            'curriculums.*.units.*.link' => 'nullable|url|max:255',
            'curriculums.*.units.*.open_in_new_tab' => 'nullable|boolean',

            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        // Calculate monthly_amount if payment_type is monthly or both
        $monthlyAmount = null;
        $semesterMonths = null;
        
        if ($validated['payment_type'] === 'one-off') {
            // For one-off payment, set semester_months and monthly_amount to null
            $semesterMonths = null;
            $monthlyAmount = null;
        } elseif (in_array($validated['payment_type'], ['monthly', 'both']) && !empty($validated['semester_months']) && $validated['semester_months'] > 0) {
            // Use discount_price if available, otherwise use price
            $finalPrice = !empty($validated['discount_price']) && $validated['discount_price'] > 0 
                ? $validated['discount_price'] 
                : $validated['price'];
            // Auto-calculate if not provided, otherwise use provided value
            $monthlyAmount = $validated['monthly_amount'] ?? ($finalPrice / $validated['semester_months']);
            $semesterMonths = $validated['semester_months'];
        }

        // ✅ Create course
        $course = Course::create([
            'title' => $validated['title'],
            'title_en' => $validated['title_en'],
            'short_description' => $validated['short_description'],
            'short_description_en' => $validated['short_description_en'],
            'semester_id' => $validated['semester_id'],
            'academic_level_id' => $validated['academic_level_id'],
            'class_id' => $validated['class_id'],
            'course_type' => $validated['course_type'],
            'course_link' => $validated['course_type'] === 'online' ? ($validated['course_link'] ?? null) : null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'payment_type' => $validated['payment_type'],
            'semester_months' => $semesterMonths,
            'monthly_amount' => $monthlyAmount,
            'teacher_id' => $validated['teacher_id'],
            'duration' => $validated['duration'],
            'duration_en' => $validated['duration_en'],
            'schedule' => $validated['schedule'],
            'schedule_en' => $validated['schedule_en'],
            'available_seats' => $validated['available_seats'] ?? null,
            'description' => $validated['description'],
            'description_en' => $validated['description_en'],
        ]);

        $course->addMedia($request->file('image'))->toMediaCollection('courses');

        // ✅ Handle curriculums & units
        if (!empty($validated['curriculums'])) {
            foreach ($validated['curriculums'] as $curriculumData) {
                $curriculum = Curriculum::create([
                    'course_id' => $course->id,
                    'title' => $curriculumData['title'],
                    'title_en' => $curriculumData['title_en'],
                ]);

                if (!empty($curriculumData['units'])) {
                    foreach ($curriculumData['units'] as $unitData) {
                        Unit::create([
                            'curriculum_id' => $curriculum->id,
                            'title' => $unitData['title'],
                            'title_en' => $unitData['title_en'],
                            'registered_students' => !empty($unitData['registered_students']),
                            'link' => $unitData['link'] ?? null,
                            'open_in_new_tab' => !empty($unitData['link'])
                                ? !empty($unitData['open_in_new_tab'])
                                : true,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('courses.index')
            ->with('success', __('app.created successfully'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        // Eager load relationships
        $course->load(['semester', 'academicLevel', 'teacher', 'classRoom', 'curriculums.units']);

        return view('courses.show', compact('course'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        // Eager load curriculums and units
        $course->load('curriculums.units');

        // Fetch related data for selects
        $semesters = Semester::all();
        $levels = AcademicLevel::all();
        $teachers = Teacher::getActive()->get();
        $classes = ClassRoom::all();

        return view('courses.edit', compact('course', 'semesters', 'classes', 'levels', 'teachers'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'short_description' => 'required|string',
            'short_description_en' => 'required|string',
            'semester_id' => 'required|exists:semesters,id',
            'academic_level_id' => 'required|exists:academic_levels,id',
            'class_id' => 'required|exists:classes,id',
            'price' => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'payment_type' => 'required|in:one-off,monthly,both',
            'semester_months' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->payment_type, ['monthly', 'both']) && empty($value)) {
                        $fail(__('validation.required', ['attribute' => __('app.semester_months')]));
                    }
                },
                'nullable',
                'integer',
                'min:1'
            ],
            'monthly_amount' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->payment_type, ['monthly', 'both']) && empty($value)) {
                        $fail(__('validation.required', ['attribute' => __('app.monthly_amount')]));
                    }
                },
                'nullable',
                'numeric',
                'min:0'
            ],
            'duration' => 'required|string',
            'duration_en' => 'required|string',
            'schedule' => 'required|string',
            'schedule_en' => 'required|string',
            'available_seats' => 'nullable|integer|min:0',
            'course_type' => 'required|in:recorded,online',
            'course_link' => 'required_if:course_type,online|nullable|string|max:255',

            'teacher_id' => 'required|exists:teachers,id',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'curriculums' => 'nullable|array',
            'curriculums.*.title' => 'required_with:curriculums|string',
            'curriculums.*.title_en' => 'required_with:curriculums|string',
            'curriculums.*.units' => 'nullable|array',
            'curriculums.*.units.*.title' => 'required_with:curriculums.*.units|string',
            'curriculums.*.units.*.title_en' => 'required_with:curriculums.*.units|string',
            'curriculums.*.units.*.registered_students' => 'nullable|boolean',
            'curriculums.*.units.*.link' => 'nullable|url|max:255',
            'curriculums.*.units.*.open_in_new_tab' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        // Calculate monthly_amount if payment_type is monthly or both
        $monthlyAmount = null;
        $semesterMonths = null;
        
        if ($request->payment_type === 'one-off') {
            // For one-off payment, set semester_months and monthly_amount to null
            $semesterMonths = null;
            $monthlyAmount = null;
        } elseif (in_array($request->payment_type, ['monthly', 'both']) && !empty($request->semester_months) && $request->semester_months > 0) {
            // Use discount_price if available, otherwise use price
            $finalPrice = !empty($request->discount_price) && $request->discount_price > 0 
                ? $request->discount_price 
                : $request->price;
            // Auto-calculate if not provided, otherwise use provided value
            $monthlyAmount = $request->monthly_amount ?? ($finalPrice / $request->semester_months);
            $semesterMonths = $request->semester_months;
        }

        // Update course main data
        $course->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'short_description' => $request->short_description,
            'short_description_en' => $request->short_description_en,
            'semester_id' => $request->semester_id,
            'academic_level_id' => $request->academic_level_id,
            'class_id' => $request->class_id,
            'course_type' => $request->course_type,
            'course_link' => $request->course_type === 'online' ? $request->course_link : null,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'payment_type' => $request->payment_type,
            'semester_months' => $semesterMonths,
            'monthly_amount' => $monthlyAmount,
            'duration' => $request->duration,
            'duration_en' => $request->duration_en,
            'schedule' => $request->schedule,
            'schedule_en' => $request->schedule_en,
            'available_seats' => $request->available_seats,
            'teacher_id' => $request->teacher_id,
            'description' => $request->description,
            'description_en' => $request->description_en,
        ]);

        if ($request->hasFile('image')) {
            $course->clearMediaCollection('courses');
            $course->addMedia($request->file('image'))->toMediaCollection('courses');
        }

        // Delete old curriculums & units
        $course->curriculums()->each(function ($curriculum) {
            $curriculum->units()->delete();
            $curriculum->delete();
        });

        // Insert new curriculums with units
        if ($request->has('curriculums')) {
            foreach ($request->curriculums as $c) {
                $curriculum = $course->curriculums()->create([
                    'title' => $c['title'],
                    'title_en' => $c['title_en'],
                ]);

                if (isset($c['units'])) {
                    foreach ($c['units'] as $u) {
                        $curriculum->units()->create([
                            'title' => $u['title'],
                            'title_en' => $u['title_en'],
                            'registered_students' => !empty($u['registered_students']),
                            'link' => $u['link'] ?? null,
                            'open_in_new_tab' => !empty($u['link'])
                                ? !empty($u['open_in_new_tab'])
                                : true,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('courses.index')->with('success', __('app.updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        try {
            $course->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the course.')
            ], 500);
        }
    }

    public function copy($id)
    {
        try {
            $course = Course::with(['curriculums', 'curriculums.units', 'media'])->findOrFail($id);

            // Duplicate the course
            $newCourse = $course->replicate();
            $newCourse->title = $course->title . ' (نسخة)';
            $newCourse->title_en = $course->title_en . ' (Copy)';
            $newCourse->slug = null; // let sluggable regenerate
            $newCourse->slug_en = null;
            $newCourse->status = false;
            $newCourse->push();

            // Copy media (images)
            foreach ($course->getMedia('courses') as $media) {
                $media->copy($newCourse, 'courses');
            }

            // Copy curriculums
            foreach ($course->curriculums as $curriculum) {
                $newCurriculum = $curriculum->replicate();
                $newCurriculum->course_id = $newCourse->id;
                $newCurriculum->save();

                // Copy units under this curriculum
                foreach ($curriculum->units as $unit) {
                    $newUnit = $unit->replicate();
                    $newUnit->curriculum_id = $newCurriculum->id;
                    $newUnit->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => __('app.course copied successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the course.')
            ], 500);
        }
    }

    public function change_status($id)
    {
        try {
            // Set only the selected semester to true
            $item = Course::findOrFail($id);
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
