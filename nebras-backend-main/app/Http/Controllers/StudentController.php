<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use App\Mail\RegistrationMail;
use Illuminate\Support\Facades\Mail;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('students.index');
    }

    public function getAjaxData(Request $request){
        $query = Student::with(['classRoom']);

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('classRoom', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'name',
            'email',
            'phone',
            'class',
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
        $total = Student::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($student) {
            $viewUrl = route('students.show', $student->id);
            $editUrl = route('students.edit', $student->id);
            $deleteUrl = route('students.destroy', $student->id);

            return [
                "id" => $student->id,
                "name" => $student->name,
                "email" => $student->email,
                "phone" => $student->phone,
                "class" => ($student->classRoom) ? $student->classRoom->getLocalizationName() : '-',
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="students" data-id="'.$student->id.'" title="'.__('app.delete').'">
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
     * Show the form for creating a new student.
     */
    public function create()
    {
        $classes = ClassRoom::all();
        return view('students.create', compact('classes'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'password' => 'required|string|min:6|confirmed',
            'age' => 'required|integer',
            'gender' => 'required|in:0,1', // e.g., 0 = male, 1 = female
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $student = Student::create([
            'name' => $request->name,
            'email'=> $request->email,
            'password'=> $request->password, // auto-hashed by model
            'age' => $request->age,
            'gender'=> $request->gender,
            'phone'=> $request->phone,
            'class_id'=> $request->class_id,
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $student->addMedia($request->file('profile_picture'))->toMediaCollection('profile_pictures');
        }

        Mail::to($student->email)->send(new RegistrationMail($student));

        return redirect()->route('students.index')
            ->with('success', __('Student created successfully.'));
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the student.
     */
    public function edit(Student $student)
    {
        $classes = ClassRoom::all();
        return view('students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:students,email,{$student->id}",
            'password' => 'nullable|string|min:6|confirmed',
            'age' => 'required|integer',
            'gender' => 'required|in:0,1',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $student->update([
            'name' => $request->name,
            'email'=> $request->email,
            'password'=> $request->password ? $request->password : $student->password,
            'age' => $request->age,
            'gender'=> $request->gender,
            'phone'=> $request->phone,
            'class_id'=> $request->class_id,
        ]);

        // Handle profile picture removal if requested
        if ($request->filled('remove_image') && $request->input('remove_image') == '1') {
            $student->clearMediaCollection('profile_pictures');
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $student->clearMediaCollection('profile_pictures');
            $student->addMedia($request->file('profile_picture'))->toMediaCollection('profile_pictures');
        }

        return redirect()->route('students.index')
            ->with('success', __('Student updated successfully.'));
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student)
    {
        try {
            $suffix = '_'.time().'_deleted';
            $student->email = $student->email.$suffix;
            if ($student->google_id) {
                $student->google_id = $student->google_id.$suffix;
            }
            $student->update();
            $student->delete();

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

}
