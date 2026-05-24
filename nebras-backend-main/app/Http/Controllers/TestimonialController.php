<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Semester;
use App\Models\AcademicLevel;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     * Redirects to admins page by default.
     */
    public function index(Request $request)
    {
        return redirect()->route('testimonials.admins');
    }

    /**
     * Display a listing of admin testimonials.
     */
    public function admins(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request, 'admin');
        }
        $semesters = Semester::orderByDesc('created_at')->get();
        $academicLevels = AcademicLevel::orderBy('id')->get();

        return view('testimonials.admins', compact('semesters', 'academicLevels'));
    }

    /**
     * Display a listing of student testimonials.
     */
    public function students(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request, 'student');
        }
        $semesters = Semester::orderByDesc('created_at')->get();
        $academicLevels = AcademicLevel::orderBy('id')->get();

        return view('testimonials.students', compact('semesters', 'academicLevels'));
    }

    /**
     * Courses for testimonial filters (optional semester + academic level).
     */
    public function filterCourses(Request $request)
    {
        try {
            $query = Course::with('teacher');

            if ($request->filled('semester_id')) {
                $query->where('semester_id', (int) $request->input('semester_id'));
            }
            if ($request->filled('academic_level_id')) {
                $query->where('academic_level_id', (int) $request->input('academic_level_id'));
            }

            $courses = $query->orderBy('title')->get()->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'label' => $course->getLocalizationTitleWithTeacher(),
                ];
            });

            return response()->json(['courses' => $courses]);
        } catch (\Exception $e) {
            Log::error('Testimonials filter courses: ' . $e->getMessage());

            return response()->json(['courses' => []], 500);
        }
    }

    public function getAjaxData(Request $request, $createdType = null)
    {
        try {
            $query = Testimonial::with(['classRoom', 'course.teacher']);

            // Filter by created_type if provided
            $filterType = $createdType ?? $request->input('created_type');
            if ($filterType !== null && $filterType !== '') {
                $query->where('created_type', $filterType);
            }

            // Filter by course / semester / academic level (course is most specific)
            $semesterId = $request->input('semester_id');
            $courseId = $request->input('course_id');
            $academicLevelId = $request->input('academic_level_id');

            if (! empty($courseId)) {
                $query->where('testimonials.course_id', (int) $courseId);
            } elseif (! empty($semesterId) || ! empty($academicLevelId)) {
                $query->whereHas('course', function ($q) use ($semesterId, $academicLevelId) {
                    if (! empty($semesterId)) {
                        $q->where('semester_id', (int) $semesterId);
                    }
                    if (! empty($academicLevelId)) {
                        $q->where('academic_level_id', (int) $academicLevelId);
                    }
                });
            }

            // Search logic
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('classRoom', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                        })
                        ->orWhereHas('course', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('title_en', 'like', "%{$search}%")
                                ->orWhereHas('teacher', function ($tq) use ($search) {
                                    $tq->where('name', 'like', "%{$search}%")
                                        ->orWhere('name_en', 'like', "%{$search}%");
                                });
                        });
                });
            }

            // Column mapping for sorting (must match DataTables column order)
            // 0: id, 1: image, 2: name, 3: text, 4: class_room, 5: course, 6: status, 7: created_at, 8: action
            $columns = [
                'testimonials.id',          // id
                'image',                    // image (not sortable)
                'testimonials.name',        // name
                'testimonials.text',        // text
                'class_room',               // class (not directly sortable column)
                'course',                   // course (not directly sortable column)
                'testimonials.status',      // status
                'testimonials.created_at',  // created_at
                'action',                   // action (not sortable)
            ];

            // Sorting logic
            if ($request->has('order.0')) {
                $orderColumnIndex = (int) $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir', 'asc');
                $columnName = $columns[$orderColumnIndex] ?? 'testimonials.created_at';

                // Columns that should NOT change ordering (image, class, course, action)
                if (in_array($columnName, ['image', 'class_room', 'course', 'action'], true)) {
                    $query->orderBy('testimonials.created_at', $orderDir);
                } else {
                    // Normalize created_at column name
                    if ($columnName === 'created_at') {
                        $columnName = 'testimonials.created_at';
                    }
                    $query->orderBy($columnName, $orderDir);
                }
            } else {
                $query->orderBy('testimonials.created_at', 'desc');
            }

            // Count totals (cloned for filtering)
            // If filtering by created_type, count only that type, otherwise count all
            $filterType = $createdType ?? $request->input('created_type');
            if ($filterType !== null && $filterType !== '') {
                $total = Testimonial::where('created_type', $filterType)->count();
            } else {
                $total = Testimonial::count();
            }
            $filtered = (clone $query)->count();

            // Fetch data with pagination
            $data = $query
                ->skip($request->input('start'))
                ->take($request->input('length'))
                ->get();

            // Format response
            $formatted = $data->map(function ($testimonial) {
                $viewUrl = route('testimonials.show', $testimonial->id);
                $editUrl = route('testimonials.edit', $testimonial->id);
                $deleteUrl = route('testimonials.destroy', $testimonial->id);

                $statusSelect = '<select class="form-select form-select-sm change-testimonial-status" 
                    data-id="' . $testimonial->id . '" 
                    data-current-status="' . $testimonial->status . '"
                    style="min-width: 120px;">
                    <option value="pending" ' . ($testimonial->status == 'pending' ? 'selected' : '') . '>' . __('app.pending') . '</option>
                    <option value="approved" ' . ($testimonial->status == 'approved' ? 'selected' : '') . '>' . __('app.approved') . '</option>
                    <option value="rejected" ' . ($testimonial->status == 'rejected' ? 'selected' : '') . '>' . __('app.rejected') . '</option>
                </select>';

                return [
                    "id" => $testimonial->id,
                    "name" => $testimonial->name,
                    "text" => Str::limit($testimonial->text, 50),
                    "image" => $testimonial->image ? "<img src='".$testimonial->image."' style='height:40px;'>" : '-',
                    "class_room" => $testimonial->classRoom ? $testimonial->classRoom->getLocalizationName() : '-',
                    "course" => $testimonial->course ? $testimonial->course->getLocalizationTitleWithTeacher() : '-',
                    "status" => $statusSelect,
                    "created_at" => $testimonial->created_at->format('Y-m-d'),
                    "action" => '
                        <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-delete" data-table="testimonials" data-id="'.$testimonial->id.'" title="'.__('app.delete').'">
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
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Testimonials AJAX Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => __('app.something went wrong while fetching testimonials'),
                'message' => config('app.debug') ? $e->getMessage() : __('app.something went wrong while fetching testimonials')
            ], 500);
        }
    }

    /**
     * Get courses by class_id (AJAX endpoint)
     */
    public function getCoursesByClass(Request $request)
    {
        try {
            $classId = $request->input('class_id');
            
            if (!$classId) {
                return response()->json(['courses' => []]);
            }

            $courses = Course::with('teacher')
                ->where('class_id', $classId)
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->getLocalizationTitleWithTeacher(),
                    ];
                });

            return response()->json(['courses' => $courses]);
        } catch (\Exception $e) {
            Log::error('Get Courses By Class Error: ' . $e->getMessage());
            return response()->json(['courses' => []], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $classRooms = ClassRoom::has('courses')->get();
            $courses = Course::with('teacher')->get();

            return view('testimonials.create', compact('classRooms', 'courses'));
        } catch (\Exception $e) {
            return redirect()->route('testimonials.admins')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'name' => 'required|string|max:50',
                    'text' => 'required|string|max:255',
                    'rate' => 'required|integer|min:0|max:5',
                    'class_id' => 'required|exists:classes,id',
                    'course_id' => 'required|exists:courses,id',
                    'status' => 'required|in:pending,approved,rejected',
                    'created_by' => 'nullable|integer',
                    'created_type' => 'nullable|in:student,admin',
                    'image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:10240',
                ],
                [
                    'name.required'      => __('messages.testimonial_name_required'),
                    'name.string'        => __('messages.testimonial_name_string'),
                    'name.max'           => __('messages.testimonial_name_max'),
                    'text.required'      => __('messages.testimonial_text_required'),
                    'text.string'        => __('messages.testimonial_text_string'),
                    'text.max'           => __('messages.testimonial_text_max'),
                    'rate.required'      => __('messages.testimonial_rate_required'),
                    'rate.integer'       => __('messages.testimonial_rate_integer'),
                    'rate.min'           => __('messages.testimonial_rate_min'),
                    'rate.max'           => __('messages.testimonial_rate_max'),
                    'class_id.required'  => __('messages.testimonial_class_required'),
                    'class_id.exists'    => __('messages.testimonial_class_exists'),
                    'course_id.required' => __('messages.testimonial_course_required'),
                    'course_id.exists'   => __('messages.testimonial_course_exists'),
                    'image.image'        => __('messages.testimonial_image_image'),
                    'image.mimes'        => __('messages.testimonial_image_mimes'),
                    'image.max'          => __('messages.testimonial_image_max'),
                ]
            );

            $validated['created_by'] = auth()->user()->id;
            $validated['created_type'] = "admin";
            $testimonial = Testimonial::create($validated);

            // Handle image upload
            if ($request->hasFile('image')) {
                $testimonial->addMedia($request->file('image'))->toMediaCollection('testimonials');
            }

            return redirect()->route('testimonials.admins')
                ->with('success', __('app.created successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Testimonial Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while creating the testimonial'))
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        try {
            $testimonial->load(['classRoom', 'course.teacher']);

            return view('testimonials.show', compact('testimonial'));
        } catch (\Exception $e) {
            Log::error('Testimonial Show Error: ' . $e->getMessage());
            return redirect()->route('testimonials.admins')
                ->with('error', __('app.something went wrong while loading the testimonial'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        try {
            $classRooms = ClassRoom::has('courses')->get();
            // Get courses filtered by the testimonial's class_id if it exists
            $courses = $testimonial->class_id
                ? Course::with('teacher')->where('class_id', $testimonial->class_id)->get()
                : Course::with('teacher')->get();

            return view('testimonials.edit', compact('testimonial', 'classRooms', 'courses'));
        } catch (\Exception $e) {
            Log::error('Testimonial Edit Error: ' . $e->getMessage());
            return redirect()->route('testimonials.admins')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        try {
            $validated = $request->validate(
                [
                    'name' => 'required|string|max:50',
                    'text' => 'required|string|max:255',
                    'rate' => 'required|integer|min:0|max:5',
                    'class_id' => 'required|exists:classes,id',
                    'course_id' => 'required|exists:courses,id',
                    'status' => 'required|in:pending,approved,rejected',
                    'image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:10240',
                ],
                [
                    'name.required'      => __('messages.testimonial_name_required'),
                    'name.string'        => __('messages.testimonial_name_string'),
                    'name.max'           => __('messages.testimonial_name_max'),
                    'text.required'      => __('messages.testimonial_text_required'),
                    'text.string'        => __('messages.testimonial_text_string'),
                    'text.max'           => __('messages.testimonial_text_max'),
                    'rate.required'      => __('messages.testimonial_rate_required'),
                    'rate.integer'       => __('messages.testimonial_rate_integer'),
                    'rate.min'           => __('messages.testimonial_rate_min'),
                    'rate.max'           => __('messages.testimonial_rate_max'),
                    'class_id.required'  => __('messages.testimonial_class_required'),
                    'class_id.exists'    => __('messages.testimonial_class_exists'),
                    'course_id.required' => __('messages.testimonial_course_required'),
                    'course_id.exists'   => __('messages.testimonial_course_exists'),
                    'image.image'        => __('messages.testimonial_image_image'),
                    'image.mimes'        => __('messages.testimonial_image_mimes'),
                    'image.max'          => __('messages.testimonial_image_max'),
                ]
            );

            $testimonial->update($validated);

            // Handle image removal if requested
            if ($request->filled('remove_image') && $request->input('remove_image') == '1') {
                $testimonial->clearMediaCollection('testimonials');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $testimonial->clearMediaCollection('testimonials');
                $testimonial->addMedia($request->file('image'))->toMediaCollection('testimonials');
            }

            return redirect()->route('testimonials.admins')
                ->with('success', __('app.updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Testimonial Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while updating the testimonial'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        try {
            $testimonial->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the testimonial')
            ], 500);
        }
    }

    /**
     * Change testimonial status.
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $testimonial = Testimonial::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $oldStatus = $testimonial->status;
            $newStatus = $request->input('status');

            $testimonial->status = $newStatus;
            $testimonial->save();

            return response()->json([
                'status' => 'success',
                'message' => __('app.status updated successfully')
            ]);
        } catch (\Exception $e) {
            Log::error('Testimonial Status Change Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while updating the status')
            ], 500);
        }
    }

    /**
     * Get additional information for student testimonials list.
     */
    public function getAdditionalInfo($type)
    {
        try {
            $additionalInfo = AdditionalInformation::where('type', $type)->first();

            return response()->json([
                'status' => 'success',
                'data'   => $additionalInfo,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Additional Info Error (testimonials): ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => __('app.something went wrong while fetching additional information'),
            ], 500);
        }
    }

    /**
     * Store or update additional information for student testimonials list.
     */
    public function storeAdditionalInfo(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'title'          => 'required|string|max:50',
                    'title_en'       => 'required|string|max:50',
                    'description'    => 'nullable|string|max:100',
                    'description_en' => 'nullable|string|max:100',
                ],
                [
                    'title.required'          => __('app.title is required'),
                    'title_en.required'       => __('app.title_en is required'),
                    'description.required'    => __('app.description is required'),
                    'description_en.required' => __('app.description_en is required'),
                ]
            );

            $additionalInfo = AdditionalInformation::updateOrCreate(
                ['type' => 'students_testimonials'],
                [
                    'title'          => $validated['title'],
                    'title_en'       => $validated['title_en'],
                    'description'    => $validated['description'],
                    'description_en' => $validated['description_en'],
                ]
            );

            return response()->json([
                'status'  => 'success',
                'message' => __('app.saved successfully'),
                'data'    => $additionalInfo,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('app.validation failed'),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Additional Info Error (testimonials): ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => __('app.something went wrong while saving additional information'),
            ], 500);
        }
    }
}
