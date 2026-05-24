<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Display a listing of candidates (DataTables).
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('candidates.index');
    }

    /**
     * Get AJAX data for DataTables.
     */
    public function getAjaxData(Request $request)
    {
        $query = Candidate::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('major_specialization', 'like', "%{$search}%")
                    ->orWhere('years_of_experience', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id',
            'full_name',
            'email',
            'major_specialization',
            'years_of_experience',
            'created_at',
            'action',
        ];

        if ($request->has('order.0')) {
            $orderColumnIndex = (int) $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir', 'asc');
            $columnName = $columns[$orderColumnIndex] ?? 'id';
            if ($columnName === 'action') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = Candidate::count();
        $filtered = (clone $query)->count();

        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        $formatted = $data->map(function ($candidate) {
            $viewUrl = route('candidates.show', $candidate->id);
            $cvUrl = $candidate->cv_url;
            $downloadCvBtn = $cvUrl
                ? '<a href="'.e($cvUrl).'" target="_blank" rel="noopener" class="btn btn-success me-1" title="'.__('app.download cv').'"><i class="fa-solid fa-download"></i></a>'
                : '<span class="btn btn-secondary me-1 disabled" title="'.__('app.no cv uploaded').'"><i class="fa-solid fa-download"></i></span>';

            return [
                'id' => $candidate->id,
                'full_name' => $candidate->full_name,
                'email' => $candidate->email,
                'major_specialization' => $candidate->major_specialization,
                'years_of_experience' => $candidate->years_of_experience,
                'created_at' => $candidate->created_at->format('Y-m-d H:i'),
                'action' => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    '.$downloadCvBtn.'
                    <button type="button" class="btn btn-danger btn-delete" data-table="candidates" data-id="'.$candidate->id.'" title="'.__('app.delete').'">
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

    /**
     * Display the specified candidate.
     */
    public function show(Candidate $candidate)
    {
        return view('candidates.show', compact('candidate'));
    }

    /**
     * Remove the specified candidate.
     */
    public function destroy(Candidate $candidate)
    {
        try {
            $candidate->delete();
            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the candidate'),
            ], 500);
        }
    }
}
