<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Store a newly submitted candidate (public API).
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'full_name' => 'required|string|max:50',
            'email' => 'required|email',
            'phone_number' => 'required|string|regex:/^[0-9+\-\s()]{10,20}$/',
            'years_of_experience' => 'required|integer|min:0|max:99',
            'major_specialization' => 'required|string|max:50',
            'cv' => 'nullable|file|mimes:docx,pdf|max:10240', // 10MB
        ], [
            'full_name.required' => __('messages.candidate_full_name_required'),
            'full_name.max' => __('messages.candidate_full_name_max'),
            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_invalid'),
            'phone_number.required' => __('messages.candidate_phone_required'),
            'phone_number.regex' => __('messages.candidate_phone_invalid'),
            'years_of_experience.required' => __('messages.candidate_years_required'),
            'years_of_experience.integer' => __('messages.candidate_years_integer'),
            'years_of_experience.min' => __('messages.candidate_years_range'),
            'years_of_experience.max' => __('messages.candidate_years_range'),
            'major_specialization.required' => __('messages.candidate_major_required'),
            'major_specialization.max' => __('messages.candidate_major_max'),
            'cv.mimes' => __('messages.candidate_cv_mimes'),
            'cv.max' => __('messages.candidate_cv_max'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.validation_failed'),
                'data' => $validator->errors(),
            ], 422);
        }

        $candidate = Candidate::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'years_of_experience' => (int) $request->years_of_experience,
            'major_specialization' => $request->major_specialization,
        ]);

        if ($request->hasFile('cv')) {
            $candidate->addMediaFromRequest('cv')->toMediaCollection('cv');
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.candidate_submitted_successfully'),
            'data' => [
                'id' => $candidate->id,
            ],
        ], 201);
    }
}
