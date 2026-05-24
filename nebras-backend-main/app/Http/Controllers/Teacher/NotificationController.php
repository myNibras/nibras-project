<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show teacher notifications list (placeholder for now).
     */
    public function index(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $notifications = collect(); // Replace with real notifications later

        return view('teacher.notifications', compact('teacher', 'notifications'));
    }
}

