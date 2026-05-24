<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('partners.index');
    }

    /**
     * Get AJAX data for DataTables.
     */
    public function getAjaxData(Request $request)
    {
        try {
            $query = Partner::query();

            // Search logic
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            // Column mapping for sorting
            $columns = [
                'partners.id',
                'logo',
                'partners.name_ar',
                'partners.name_en',
                'partners.status',
                'partners.created_at',
                'action',
            ];

            // Sorting logic
            if ($request->has('order.0')) {
                $orderColumnIndex = (int) $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir', 'asc');
                $columnName = $columns[$orderColumnIndex] ?? 'partners.created_at';

                // Columns that should NOT change ordering (logo, action)
                if (in_array($columnName, ['logo', 'action'], true)) {
                    $query->orderBy('partners.created_at', $orderDir);
                } else {
                    if ($columnName === 'created_at') {
                        $columnName = 'partners.created_at';
                    }
                    $query->orderBy($columnName, $orderDir);
                }
            } else {
                $query->orderBy('partners.created_at', 'desc');
            }

            // Count totals
            $total = Partner::count();
            $filtered = (clone $query)->count();

            // Fetch data with pagination
            $data = $query
                ->skip($request->input('start'))
                ->take($request->input('length'))
                ->get();

            // Format response
            $formatted = $data->map(function ($partner) {
                $editUrl = route('partners.edit', $partner->id);
                $deleteUrl = route('partners.destroy', $partner->id);

                $statusBadge = $partner->status ? '<span class="badge bg-success">'.__('app.active').'</span>' : '<span class="badge bg-danger">'.__('app.inactive').'</span>';

                return [
                    "id" => $partner->id,
                    "logo" => $partner->logo ? "<img src='".$partner->logo."' style='height:40px;'>" : '-',
                    "name_ar" => $partner->name_ar,
                    "name_en" => $partner->name_en,
                    "status" => $statusBadge,
                    "created_at" => $partner->created_at->format('Y-m-d'),
                    "action" => '
                        <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-delete" data-table="partners" data-id="'.$partner->id.'" title="'.__('app.delete').'">
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
        } catch (\Exception $e) {
            Log::error('Partners AJAX Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => __('app.something went wrong while fetching partners'),
                'message' => config('app.debug') ? $e->getMessage() : __('app.something went wrong while fetching partners')
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('partners.create');
        } catch (\Exception $e) {
            return redirect()->route('partners.index')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'name_ar' => 'nullable|string|max:50',
                    'name_en' => 'nullable|string|max:50',
                    'status' => 'nullable|boolean',
                    'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
                ],
                [
                    'name_ar.string' => __('app.name_ar must be a string'),
                    'name_ar.max' => __('app.name_ar must not exceed 50 characters'),
                    'name_en.string' => __('app.name_en must be a string'),
                    'name_en.max' => __('app.name_en must not exceed 50 characters'),
                    'logo.required' => __('app.logo is required'),
                    'logo.image' => __('app.logo must be an image'),
                    'logo.mimes' => __('app.logo must be jpeg, jpg, png, or webp'),
                    'logo.max' => __('app.logo must not exceed 2MB'),
                ]
            );

            $partner = Partner::create([
                'name_ar' => $validated['name_ar'] ?? null,
                'name_en' => $validated['name_en'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $partner->addMedia($request->file('logo'))->toMediaCollection('partners');
            }

            return redirect()->route('partners.index')
                ->with('success', __('app.created successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Partner Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while creating the partner'))
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partner $partner)
    {
        try {
            return view('partners.edit', compact('partner'));
        } catch (\Exception $e) {
            Log::error('Partner Edit Error: ' . $e->getMessage());
            return redirect()->route('partners.index')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        try {
            $logoRequired = !$partner->getMedia('partners')->count() || $request->input('remove_logo') == '1';

            $validated = $request->validate(
                [
                    'name_ar' => 'nullable|string|max:50',
                    'name_en' => 'nullable|string|max:50',
                    'status' => 'nullable|boolean',
                    'logo' => [
                        Rule::requiredIf($logoRequired),
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:10240',
                    ],
                ],
                [
                    'name_ar.string' => __('app.name_ar must be a string'),
                    'name_ar.max' => __('app.name_ar must not exceed 50 characters'),
                    'name_en.string' => __('app.name_en must be a string'),
                    'name_en.max' => __('app.name_en must not exceed 50 characters'),
                    'logo.required' => __('app.logo is required'),
                    'logo.image' => __('app.logo must be an image'),
                    'logo.mimes' => __('app.logo must be jpeg, jpg, png, or webp'),
                    'logo.max' => __('app.logo must not exceed 2MB'),
                ]
            );

            $partner->update([
                'name_ar' => $validated['name_ar'] ?? null,
                'name_en' => $validated['name_en'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle logo removal if requested
            if ($request->filled('remove_logo') && $request->input('remove_logo') == '1') {
                $partner->clearMediaCollection('partners');
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $partner->clearMediaCollection('partners');
                $partner->addMedia($request->file('logo'))->toMediaCollection('partners');
            }

            return redirect()->route('partners.index')
                ->with('success', __('app.updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Partner Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while updating the partner'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner)
    {
        try {
            $partner->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the partner')
            ], 500);
        }
    }

    /**
     * Change partner status.
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $partner = Partner::findOrFail($id);
            
            $request->validate([
                'status' => 'required|boolean'
            ]);

            $oldStatus = $partner->status;
            $newStatus = (bool) $request->input('status');

            $partner->status = $newStatus;
            $partner->save();

            return response()->json([
                'status' => 'success',
                'message' => __('app.status updated successfully')
            ]);
        } catch (\Exception $e) {
            Log::error('Partner Status Change Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while updating the status')
            ], 500);
        }
    }

    /**
     * Get additional information for partners type.
     */
    public function getAdditionalInfo($type)
    {
        try {
            $additionalInfo = AdditionalInformation::where('type', $type)->first();
            
            return response()->json([
                'status' => 'success',
                'data' => $additionalInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Get Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while fetching additional information')
            ], 500);
        }
    }

    /**
     * Store or update additional information for partners type.
     */
    public function storeAdditionalInfo(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'title' => 'required|string|max:50',
                    'title_en' => 'required|string|max:50',
                    'description' => 'nullable|string|max:100',
                    'description_en' => 'nullable|string|max:100',
                ],
                [
                    'title.required' => __('app.title is required'),
                    'title_en.required' => __('app.title_en is required'),
                    'description.required' => __('app.description is required'),
                    'description_en.required' => __('app.description_en is required'),
                ]
            );

            $additionalInfo = AdditionalInformation::updateOrCreate(
                ['type' => 'partners'],
                [
                    'title' => $validated['title'],
                    'title_en' => $validated['title_en'],
                    'description' => $validated['description'] ?? null,
                    'description_en' => $validated['description_en'] ?? null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => __('app.saved successfully'),
                'data' => $additionalInfo
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.validation failed'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while saving additional information')
            ], 500);
        }
    }
}
