<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoursesController extends Controller
{
    /**
     * Scope: course must belong to the authenticated teacher.
     */
    private function findCourse(int $id): Course
    {
        $course = Course::where('id', $id)
            ->where('teacher_id', Auth::guard('teacher')->id())
            ->with(['semester', 'academicLevel', 'classRoom', 'curriculums.units'])
            ->firstOrFail();
        return $course;
    }

    /**
     * Show the teacher's courses (all assigned, active and inactive).
     */
    public function index(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $courses = $teacher->courses()
            ->with(['semester', 'academicLevel', 'classRoom'])
            ->orderBy('title')
            ->get();

        return view('teacher.courses', compact('teacher', 'courses'));
    }

    /**
     * Show one course's basic details and curriculum (view only).
     */
    public function show(int $id): View
    {
        $course = $this->findCourse($id);
        return view('teacher.course-show', compact('course'));
    }

    /**
     * Show form to edit course details and curriculum (teacher-editable fields only).
     */
    public function edit(int $id): View
    {
        $course = $this->findCourse($id);
        return view('teacher.course-edit', compact('course'));
    }

    /**
     * Update course (description, duration, schedule, curriculum & units). Teacher can only update own course.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $course = $this->findCourse($id);

        $request->validate([
            'curriculums' => 'nullable|array',
            'curriculums.*.title' => 'required_with:curriculums|string|max:255',
            'curriculums.*.title_en' => 'required_with:curriculums|string|max:255',
            'curriculums.*.units' => 'nullable|array',
            'curriculums.*.units.*.title' => 'required_with:curriculums.*.units|string|max:255',
            'curriculums.*.units.*.title_en' => 'required_with:curriculums.*.units|string|max:255',
        ]);

        $course->curriculums()->each(function (Curriculum $curriculum) {
            $curriculum->units()->delete();
            $curriculum->delete();
        });

        if ($request->has('curriculums')) {
            foreach ($request->curriculums as $c) {
                $curriculum = $course->curriculums()->create([
                    'title' => $c['title'],
                    'title_en' => $c['title_en'],
                ]);
                if (isset($c['units'])) {
                    foreach ($c['units'] as $u) {
                        $curriculum->units()->create([
                            'title' => $u['title'] ?? '',
                            'title_en' => $u['title_en'] ?? '',
                        ]);
                    }
                }
            }
        }

        return redirect()->route('teacher.courses.show', $course->id)->with('success', __('app.updated successfully'));
    }
}
