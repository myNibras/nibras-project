<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactSubmissionController extends Controller
{
    /**
     * Display a listing of contact submissions (or DataTables JSON).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getAjaxData($request);
        }

        return view('contact-submissions.index');
    }

    /**
     * Return DataTables JSON for contact submissions list.
     */
    protected function getAjaxData(Request $request)
    {
        try {
            $query = ContactSubmission::query();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            }

            $columns = [
                'contact_submissions.id',
                'contact_submissions.full_name',
                'contact_submissions.email',
                'contact_submissions.subject',
                'contact_submissions.created_at',
                'action',
            ];

            if ($request->has('order.0')) {
                $orderColumnIndex = (int) $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir', 'asc');
                $columnName = $columns[$orderColumnIndex] ?? 'contact_submissions.created_at';

                if ($columnName !== 'action') {
                    $query->orderBy($columnName, $orderDir);
                } else {
                    $query->orderBy('contact_submissions.created_at', $orderDir);
                }
            } else {
                $query->orderBy('contact_submissions.created_at', 'desc');
            }

            $total = ContactSubmission::count();
            $filtered = (clone $query)->count();

            $data = $query
                ->skip($request->input('start'))
                ->take($request->input('length'))
                ->get();

            $formatted = $data->map(function ($submission) {
                $viewDetailsUrl = route('contact-submissions.show', $submission->id);
                $deleteUrl = route('contact-submissions.destroy', $submission->id);

                $actionHtml = '
                    <button type="button" class="btn btn-info btn-view-details me-1" data-id="' . $submission->id . '" data-url="' . $viewDetailsUrl . '" title="' . __('app.view') . '">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-delete" data-table="contact-submissions" data-id="' . $submission->id . '" title="' . __('app.delete') . '">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                ';

                return [
                    'id' => $submission->id,
                    'full_name' => $submission->full_name,
                    'email' => $submission->email,
                    'subject' => ContactSubmission::localizedSubject($submission->subject),
                    'created_at' => $submission->created_at->format('Y-m-d H:i'),
                    'action' => $actionHtml,
                ];
            });

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('Contact submissions AJAX error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => __('app.something went wrong while fetching contact submissions'),
            ], 500);
        }
    }

    /**
     * Return contact submission details as JSON (for modal).
     */
    public function show(ContactSubmission $contact_submission)
    {
        return response()->json([
            'id' => $contact_submission->id,
            'full_name' => $contact_submission->full_name,
            'email' => $contact_submission->email,
            'phone' => $contact_submission->phone ?? '—',
            'country' => $contact_submission->country ?? '—',
            'subject' => ContactSubmission::localizedSubject($contact_submission->subject),
            'message' => $contact_submission->message,
            'created_at' => $contact_submission->created_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Soft delete the specified contact submission.
     */
    public function destroy(ContactSubmission $contact_submission)
    {
        try {
            $contact_submission->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully'),
            ]);
        } catch (\Exception $e) {
            Log::error('Contact submission delete error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the contact submission'),
            ], 500);
        }
    }
}
