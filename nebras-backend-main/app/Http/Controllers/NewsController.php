<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\ContentImage;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('news.index');
    }

    /**
     * Get AJAX data for DataTables.
     */
    public function getAjaxData(Request $request)
    {
        try {
            $query = News::query();

            // Search logic
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title_ar', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('small_description_ar', 'like', "%{$search}%")
                        ->orWhere('small_description_en', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            // Column mapping for sorting
            $columns = [
                'news.id',
                'news.title_ar',
                'news.small_description_ar',
                'news.expiry_date',
                'news.status',
                'news.created_at',
                'action',
            ];

            // Sorting logic
            if ($request->has('order.0')) {
                $orderColumnIndex = (int) $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir', 'asc');
                $columnName = $columns[$orderColumnIndex] ?? 'news.created_at';

                if ($columnName === 'action') {
                    $query->orderBy('news.created_at', $orderDir);
                } else {
                    if ($columnName === 'created_at') {
                        $columnName = 'news.created_at';
                    }
                    $query->orderBy($columnName, $orderDir);
                }
            } else {
                $query->orderBy('news.created_at', 'desc');
            }

            // Count totals
            $total = News::count();
            $filtered = (clone $query)->count();

            // Fetch data with pagination
            $data = $query
                ->skip($request->input('start'))
                ->take($request->input('length'))
                ->get();

            // Format response
            $formatted = $data->map(function ($news) {
                $editUrl = route('news.edit', $news->id);
                $deleteUrl = route('news.destroy', $news->id);

                $statusBadge = $news->status ? '<span class="badge bg-success">'.__('app.active').'</span>' : '<span class="badge bg-danger">'.__('app.inactive').'</span>';

                $expiryDate = $news->expiry_date ? $news->expiry_date->format('Y-m-d') : '-';

                return [
                    "id" => $news->id,
                    "title" => $news->getLocalizationTitle(),
                    "small_description" => Str::limit($news->getLocalizationSmallDescription(), 50),
                    "expiry_date" => $expiryDate,
                    "status" => $statusBadge,
                    "created_at" => $news->created_at->format('Y-m-d'),
                    "action" => '
                        <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-delete" data-table="news" data-id="'.$news->id.'" title="'.__('app.delete').'">
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
            Log::error('News AJAX Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => __('app.something went wrong while fetching news'),
                'message' => config('app.debug') ? $e->getMessage() : __('app.something went wrong while fetching news')
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('news.create');
        } catch (\Exception $e) {
            return redirect()->route('news.index')
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
                    'title_ar' => 'required|string|max:50',
                    'title_en' => 'required|string|max:50',
                    'small_description_ar' => 'required|string|max:150',
                    'small_description_en' => 'required|string|max:150',
                    'full_description_ar' => 'required|string',
                    'full_description_en' => 'required|string',
                    'expiry_date' => 'nullable|date|after:today',
                    'status' => 'nullable|boolean',
                    'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
                ],
                [
                    'title_ar.required' => __('app.title_ar is required'),
                    'title_ar.max' => __('app.title_ar must not exceed 50 characters'),
                    'title_en.required' => __('app.title_en is required'),
                    'title_en.max' => __('app.title_en must not exceed 50 characters'),
                    'small_description_ar.required' => __('app.small_description_ar is required'),
                    'small_description_ar.max' => __('app.small_description_ar must not exceed 150 characters'),
                    'small_description_en.required' => __('app.small_description_en is required'),
                    'small_description_en.max' => __('app.small_description_en must not exceed 150 characters'),
                    'full_description_ar.required' => __('app.full_description_ar is required'),
                    'full_description_en.required' => __('app.full_description_en is required'),
                    'image.required' => __('app.image is required'),
                    'image.image' => __('app.image must be an image'),
                    'image.mimes' => __('app.image must be jpeg, jpg, png, or webp'),
                    'image.max' => __('app.image must not exceed 2MB'),
                    'expiry_date.date' => __('app.expiry_date must be a valid date'),
                    'expiry_date.after' => __('app.expiry_date must be after today'),
                ]
            );

            $news = News::create([
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'small_description_ar' => $validated['small_description_ar'],
                'small_description_en' => $validated['small_description_en'],
                'full_description_ar' => $validated['full_description_ar'],
                'full_description_en' => $validated['full_description_en'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $news->addMedia($request->file('image'))->toMediaCollection('news');
            }

            return redirect()->route('news.index')
                ->with('success', __('app.created successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('News Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while creating the news'))
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        try {
            return view('news.edit', compact('news'));
        } catch (\Exception $e) {
            Log::error('News Edit Error: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        try {
            $hasExistingImage = $news->getMedia('news')->isNotEmpty();
            $isRemovingImage = $request->input('remove_image') == '1';
            $imageRule = ($hasExistingImage && !$isRemovingImage)
                ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240'
                : 'required|image|mimes:jpg,jpeg,png,webp|max:10240';

            $validated = $request->validate(
                [
                    'title_ar' => 'required|string|max:50',
                    'title_en' => 'required|string|max:50',
                    'small_description_ar' => 'required|string|max:150',
                    'small_description_en' => 'required|string|max:150',
                    'full_description_ar' => 'required|string',
                    'full_description_en' => 'required|string',
                    'expiry_date' => 'nullable|date|after:today',
                    'status' => 'nullable|boolean',
                    'image' => $imageRule,
                ],
                [
                    'title_ar.required' => __('app.title_ar is required'),
                    'title_ar.max' => __('app.title_ar must not exceed 50 characters'),
                    'title_en.required' => __('app.title_en is required'),
                    'title_en.max' => __('app.title_en must not exceed 50 characters'),
                    'small_description_ar.required' => __('app.small_description_ar is required'),
                    'small_description_ar.max' => __('app.small_description_ar must not exceed 150 characters'),
                    'small_description_en.required' => __('app.small_description_en is required'),
                    'small_description_en.max' => __('app.small_description_en must not exceed 150 characters'),
                    'full_description_ar.required' => __('app.full_description_ar is required'),
                    'full_description_en.required' => __('app.full_description_en is required'),
                    'image.required' => __('app.image is required'),
                    'image.image' => __('app.image must be an image'),
                    'image.mimes' => __('app.image must be jpeg, jpg, png, or webp'),
                    'image.max' => __('app.image must not exceed 2MB'),
                    'expiry_date.date' => __('app.expiry_date must be a valid date'),
                    'expiry_date.after' => __('app.expiry_date must be after today'),
                ]
            );

            $news->update([
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'small_description_ar' => $validated['small_description_ar'],
                'small_description_en' => $validated['small_description_en'],
                'full_description_ar' => $validated['full_description_ar'],
                'full_description_en' => $validated['full_description_en'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle image removal if requested
            if ($request->filled('remove_image') && $request->input('remove_image') == '1') {
                $news->clearMediaCollection('news');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $news->clearMediaCollection('news');
                $news->addMedia($request->file('image'))->toMediaCollection('news');
            }

            return redirect()->route('news.index')
                ->with('success', __('app.updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('News Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while updating the news'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        try {
            $news->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the news')
            ], 500);
        }
    }

    /**
     * Change news status.
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);
            
            $request->validate([
                'status' => 'required|boolean'
            ]);

            $oldStatus = $news->status;
            $newStatus = (bool) $request->input('status');

            $news->status = $newStatus;
            $news->save();

            return response()->json([
                'status' => 'success',
                'message' => __('app.status updated successfully')
            ]);
        } catch (\Exception $e) {
            Log::error('News Status Change Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while updating the status')
            ], 500);
        }
    }

    /**
     * Upload image from WYSIWYG editor.
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240'
            ]);

            // Create a content image record to attach the media
            $contentImage = ContentImage::create([]);
            
            // Upload the image to media library in news-content collection
            $media = $contentImage->addMedia($request->file('image'))
                ->toMediaCollection('news-content');

            return response()->json([
                'success' => true,
                'url' => $media->getUrl()
            ]);
        } catch (\Exception $e) {
            Log::error('News Image Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('app.something went wrong while uploading the image')
            ], 500);
        }
    }

    /**
     * Get additional information for news type.
     */
    public function getAdditionalInfo($type)
    {
        try {
            $additionalInfo = AdditionalInformation::where('type', $type)->first();
            return response()->json(['status' => 'success', 'data' => $additionalInfo]);
        } catch (\Exception $e) {
            Log::error('Get Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while fetching additional information')
            ], 500);
        }
    }

    /**
     * Store or update additional information for news type.
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
                ['type' => 'news'],
                [
                    'title' => $validated['title'],
                    'title_en' => $validated['title_en'],
                    'description' => $validated['description'] ?? null,
                    'description_en' => $validated['description_en'] ?? null,
                ]
            );
            return response()->json(['status' => 'success', 'message' => __('app.saved successfully'), 'data' => $additionalInfo]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.validation failed'), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Store Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while saving additional information')
            ], 500);
        }
    }
}

