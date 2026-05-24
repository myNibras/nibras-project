<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Rules\YoutubeUrlRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the teacher profile page.
     */
    public function index(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $teacher->load('position');

        return view('teacher.profile', compact('teacher'));
    }

    /**
     * Show the form for editing the teacher's own profile.
     */
    public function edit(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $teacher->load('position');
        $positions = Position::getActive()->orderBy('name')->get();

        return view('teacher.profile-edit', compact('teacher', 'positions'));
    }

    /**
     * Update the teacher's own profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $teacher = Auth::guard('teacher')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'name_en' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $teacher->id,
            'password' => 'nullable|string|min:8',
            'position_id' => 'nullable|exists:positions,id',
            'years_of_experience' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
            'video_url' => ['nullable', 'string', 'max:500', 'url', new YoutubeUrlRule()],
        ]);

        if ($request->hasFile('video') && $request->filled('video_url')) {
            return back()
                ->withInput()
                ->withErrors(['video_url' => __('app.only one video source allowed')]);
        }

        $updateData = [
            'name' => $validated['name'],
            'name_en' => $validated['name_en'],
            'email' => $validated['email'],
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
            $teacher->update(['video_url' => null]);
        } elseif ($request->filled('video_url')) {
            $teacher->clearMediaCollection('teacher_videos');
            $teacher->update(['video_url' => $validated['video_url']]);
        } elseif ($request->hasFile('video')) {
            $teacher->clearMediaCollection('teacher_videos');
            $teacher->update(['video_url' => null]);
            $teacher->addMedia($request->file('video'))->toMediaCollection('teacher_videos');
        }

        return redirect()->route('teacher.profile')->with('success', __('app.updated successfully'));
    }
}
