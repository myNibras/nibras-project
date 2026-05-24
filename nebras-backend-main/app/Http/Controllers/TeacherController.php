<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Position;
use App\Models\Course;
use App\Models\PaymentItem;
use App\Models\Testimonial;
use App\Models\AdditionalInformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('teachers.index');
    }

    /**
     * Log in as the given teacher (admin impersonation). Admin can view teacher panel without password.
     */
    public function loginAs(Teacher $teacher): RedirectResponse
    {
        Auth::guard('teacher')->login($teacher);
        session(['impersonate.admin_id' => Auth::guard('web')->id()]);

        return redirect()->route('teacher.dashboard');
    }

    public function getAjaxData(Request $request)
    {
        $query = Teacher::query()->with('position');

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhereHas('position', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        // Column mapping for sorting (Name, Position, Years of experience, Reviews, ...)
        $columns = [
            'name',
            'position',
            'years_of_experience',
            'reviews',
            'classes_count',
            'students_count',
            'updated_at',
            'status',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = (int) $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir', 'asc');
            $columnName = $columns[$orderColumnIndex] ?? 'name';
            if ($columnName === 'name') {
                $query->orderBy(app()->getLocale() === 'ar' ? 'teachers.name' : 'teachers.name_en', $orderDir);
            } elseif ($columnName === 'position') {
                $query->leftJoin('positions', 'teachers.position_id', '=', 'positions.id')
                    ->orderBy(app()->getLocale() === 'ar' ? 'positions.name' : 'positions.name_en', $orderDir)
                    ->select('teachers.*');
            } elseif ($columnName === 'years_of_experience') {
                $query->orderBy('teachers.years_of_experience', $orderDir);
            } elseif ($columnName === 'reviews') {
                $query->orderByRaw('(SELECT COALESCE(AVG(t.rate), 0) FROM testimonials t INNER JOIN courses c ON c.id = t.course_id WHERE c.teacher_id = teachers.id AND t.status = ?) ' . ($orderDir === 'desc' ? 'DESC' : 'ASC'), ['approved']);
            } elseif ($columnName === 'classes_count') {
                $query->orderByRaw('(SELECT COUNT(DISTINCT c2.class_id) FROM courses c2 WHERE c2.teacher_id = teachers.id) ' . ($orderDir === 'desc' ? 'DESC' : 'ASC'));
            } elseif ($columnName === 'students_count') {
                $query->orderByRaw('(SELECT COUNT(DISTINCT p.student_id) FROM payment_items pi INNER JOIN courses c ON c.id = pi.course_id INNER JOIN payments p ON p.id = pi.payment_id WHERE c.teacher_id = teachers.id AND p.status = ?) ' . ($orderDir === 'desc' ? 'DESC' : 'ASC'), ['success']);
            } elseif ($columnName === 'updated_at') {
                $query->orderBy('teachers.updated_at', $orderDir);
            } elseif ($columnName === 'status') {
                $query->orderBy('teachers.status', $orderDir);
            } else {
                $query->orderBy('teachers.name', $orderDir);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        // Count totals (cloned for filtering)
        $total = Teacher::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($teacher) {
            $viewUrl = route('teachers.show', $teacher->id);
            $editUrl = route('teachers.edit', $teacher->id);

            $classesCount = Course::where('teacher_id', $teacher->id)->distinct('class_id')->count('class_id');
            $reviewsAvg = Testimonial::whereHas('course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->where('status', 'approved')->avg('rate');
            $reviews = $reviewsAvg ? round($reviewsAvg, 1) : 0;

            $studentsCount = PaymentItem::whereHas('course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
                ->whereHas('payment', function ($q) {
                    $q->where('status', 'success');
                })
                ->with('payment:id,student_id')
                ->get()
                ->pluck('payment.student_id')
                ->filter()
                ->unique()
                ->count();

            $statusChecked = $teacher->status ? 'checked' : '';
            $statusMessage = $teacher->status
                ? __('app.are you sure you want to deactivate this teacher?')
                : __('app.are you sure you want to activate this teacher?');
            $statusHtml = "<div class='form-check form-switch mt-2'>
                <input class='form-check-input update-status'
                    name='status'
                    type='checkbox'
                    value='1'
                    role='switch'
                    id='status-{$teacher->id}'
                    data-table='teachers'
                    data-id='{$teacher->id}'
                    data-message='{$statusMessage}'
                    {$statusChecked}
                />
            </div>";

            return [
                'name' => $teacher->getLocalizationName(),
                'position' => $teacher->position ? $teacher->position->getLocalizationName() : '-',
                'years_of_experience' => (int) ($teacher->years_of_experience ?? 0),
                'reviews' => $reviews,
                'classes_count' => $classesCount,
                'students_count' => $studentsCount,
                'updated_at' => $teacher->updated_at ? $teacher->updated_at->format('Y-m-d H:i') : '-',
                'status' => $statusHtml,
                'action' => '
                    <a href="' . $viewUrl . '" class="btn btn-info me-1" title="' . __('app.view') . '">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-primary me-1" title="' . __('app.edit') . '">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="' . route('teachers.login-as', $teacher) . '" class="btn btn-success me-1" title="' . __('app.login as') . '" target="_self">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="teachers" data-id="' . $teacher->id . '" title="' . __('app.delete') . '">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $formatted,
        ]);
    }

    public function create()
    {
        $positions = Position::getActive()->orderBy('name')->get();

        // For new teacher, stats are zero
        $stats = [
            'reviews'        => 0,
            'classes_count'  => 0,
            'students_count' => 0,
        ];

        return view('teachers.create', compact('positions', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'name_en' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:teachers,email',
            'password' => 'required|string|min:8',
            'status' => 'nullable|boolean',
            'position_id' => 'nullable|exists:positions,id',
            'years_of_experience' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
        ]);

        $teacher = Teacher::create([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => $request->has('status') ? 1 : 0,
            'position_id' => $validated['position_id'] ?? null,
            'years_of_experience' => (int) ($validated['years_of_experience'] ?? 0),
            'description' => $validated['description'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $teacher->addMedia($request->file('image'))->toMediaCollection('teachers');
        }

        if ($request->hasFile('video')) {
            $teacher->addMedia($request->file('video'))->toMediaCollection('teacher_videos');
        }

        return redirect()->route('teachers.index')->with('success', __('Teacher created successfully.'));
    }

    public function show(Teacher $teacher)
    {
        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $positions = Position::getActive()->orderBy('name')->get();

        // Calculate stats for this teacher
        // Number of distinct classes across teacher's courses
        $classesCount = Course::where('teacher_id', $teacher->id)
            ->distinct('class_id')
            ->count('class_id');

        // Number of distinct students who have successful payments for teacher's courses
        $studentIds = PaymentItem::whereHas('course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('payment', function ($q) {
                $q->where('status', 'success');
            })
            ->with('payment:id,student_id')
            ->get()
            ->pluck('payment.student_id')
            ->filter()
            ->unique();

        $studentsCount = $studentIds->count();

        // Average review rating from testimonials of teacher's courses
        $reviewsAvg = Testimonial::whereHas('course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'approved')
            ->avg('rate');

        $stats = [
            'reviews'        => $reviewsAvg ? round($reviewsAvg, 1) : 0,
            'classes_count'  => $classesCount,
            'students_count' => $studentsCount,
        ];

        return view('teachers.edit', compact('teacher', 'positions', 'stats'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'name_en' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $teacher->id,
            'password' => 'nullable|string|min:8',
            'status' => 'nullable|boolean',
            'position_id' => 'nullable|exists:positions,id',
            'years_of_experience' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'name_en' => $validated['name_en'],
            'email' => $validated['email'],
            'status' => $request->has('status') ? 1 : 0,
            'position_id' => $validated['position_id'] ?? null,
            'years_of_experience' => (int) ($validated['years_of_experience'] ?? 0),
            'description' => $validated['description'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $teacher->update($updateData);

        if ($request->filled('remove_image') && $request->input('remove_image') == '1') {
            $teacher->clearMediaCollection('teachers');
        }

        if ($request->hasFile('image')) {
            $teacher->clearMediaCollection('teachers');
            $teacher->addMedia($request->file('image'))->toMediaCollection('teachers');
        }

        if ($request->filled('remove_video') && $request->input('remove_video') == '1') {
            $teacher->clearMediaCollection('teacher_videos');
        }

        if ($request->hasFile('video')) {
            $teacher->clearMediaCollection('teacher_videos');
            $teacher->addMedia($request->file('video'))->toMediaCollection('teacher_videos');
        }

        return redirect()->route('teachers.index')->with('success', __('Teacher updated successfully.'));
    }

    public function destroy(Teacher $teacher)
    {
        try {
            // Prevent delete if teacher is assigned to any course
            $hasCourses = \App\Models\Course::where('teacher_id', $teacher->id)->exists();
            if ($hasCourses) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('app.teacher_cannot_be_deleted_has_courses'),
                ], 422);
            }

            $teacher->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the teacher.')
            ], 500);
        }
    }

    /**
     * Toggle teacher status (active/inactive).
     */
    public function changeStatus($id)
    {
        try {
            $teacher = Teacher::findOrFail($id);
            $teacher->status = !$teacher->status;
            $teacher->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get additional information for teachers type.
     */
    public function getAdditionalInfo($type)
    {
        try {
            $additionalInfo = AdditionalInformation::where('type', $type)->first();
            return response()->json(['status' => 'success', 'data' => $additionalInfo]);
        } catch (\Exception $e) {
            Log::error('Get Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while fetching additional information')
            ], 500);
        }
    }

    /**
     * Store or update additional information for teachers type.
     */
    public function storeAdditionalInfo(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'title' => 'required|string|max:50',
                    'title_en' => 'required|string|max:50',
                    'description' => 'nullable|string|max:100',
                    'description_en' => 'nullable|string|max:100',
                ],
                [
                    'title.required' => __('app.title is required'),
                    'title_en.required' => __('app.title_en is required'),
                    'description.required' => __('app.description is required'),
                    'description_en.required' => __('app.description_en is required'),
                ]
            );
            $additionalInfo = AdditionalInformation::updateOrCreate(
                ['type' => 'teachers'],
                [
                    'title' => $validated['title'],
                    'title_en' => $validated['title_en'],
                    'description' => $validated['description'] ?? null,
                    'description_en' => $validated['description_en'] ?? null,
                ]
            );
            return response()->json(['status' => 'success', 'message' => __('app.saved successfully'), 'data' => $additionalInfo]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.validation failed'), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Store Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while saving additional information')
            ], 500);
        }
    }
}
