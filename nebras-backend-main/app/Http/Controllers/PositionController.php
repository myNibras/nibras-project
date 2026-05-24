<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('positions.index');
    }

    public function getAjaxData(Request $request)
    {
        $query = Position::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id',
            'name',
            'status',
            'created_at',
            'action',
        ];

        if ($request->has('order.0')) {
            $orderColumnIndex = (int) $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir', 'asc');
            $columnName = $columns[$orderColumnIndex] ?? 'created_at';
            if ($columnName === 'action') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = Position::count();
        $filtered = (clone $query)->count();

        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        $formatted = $data->map(function ($position) {
            $editUrl = route('positions.edit', $position->id);
            $checked = $position->status ? 'checked' : '';
            $message = $position->status
                ? __('app.are you sure you want to deactivate this position?')
                : __('app.are you sure you want to activate this position?');

            return [
                'id' => $position->id,
                'name' => $position->getLocalizationName(),
                'status' => "<div class='form-check form-switch mt-2'>
                    <input class='form-check-input update-status'
                        name='status'
                        type='checkbox'
                        value='1'
                        role='switch'
                        id='status-{$position->id}'
                        data-table='positions'
                        data-id='{$position->id}'
                        data-message='{$message}'
                        {$checked}
                    />
                </div>",
                'created_at' => $position->created_at->format('Y-m-d'),
                'action' => '
                    <a href="' . $editUrl . '" class="btn btn-primary me-1" title="' . __('app.edit') . '">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="positions" data-id="' . $position->id . '" title="' . __('app.delete') . '">
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');
        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', __('app.created successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        return view('positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');
        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', __('app.updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        try {
            $position->delete();
            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong'),
            ], 500);
        }
    }

    /**
     * Toggle position status.
     */
    public function changeStatus($id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->status = !$position->status;
            $position->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
