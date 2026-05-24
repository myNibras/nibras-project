<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContactSubmissionController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created contact submission.
     */
    public function store(StoreContactSubmissionRequest $request): JsonResponse
    {
        try {
            $submission = ContactSubmission::create($request->validated());

            return $this->success(
                [
                    'id'         => $submission->id,
                    'full_name'  => $submission->full_name,
                    'email'      => $submission->email,
                    'subject'    => $submission->subject,
                    'created_at' => $submission->created_at->toIso8601String(),
                ],
                'Contact form submitted successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->error('Failed to submit contact form', 500, [$e->getMessage()]);
        }
    }
}
