<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('id')->get();

        return view('settings.index', compact('settings'));
    }

    public function updateAll(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'boolean',
        ]);

        foreach ($validated['settings'] as $id => $value) {
            Setting::where('id', $id)->update(['value' => (bool) $value]);
        }

        return redirect()->route('settings.index')
            ->with('success', __('app.updated successfully'));
    }
}

