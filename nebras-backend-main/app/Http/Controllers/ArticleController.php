<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContentImage;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('article.index');
    }

    /**
     * Get AJAX data for DataTables.
     */
    public function getAjaxData(Request $request)
    {
        try {
            $query = Article::query();

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
                'articles.id',
                'articles.title_ar',
                'articles.small_description_ar',
                'articles.status',
                'articles.created_at',
                'action',
            ];

            // Sorting logic
            if ($request->has('order.0')) {
                $orderColumnIndex = (int) $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir', 'asc');
                $columnName = $columns[$orderColumnIndex] ?? 'articles.created_at';

                if ($columnName === 'action') {
                    $query->orderBy('articles.created_at', $orderDir);
                } else {
                    if ($columnName === 'created_at') {
                        $columnName = 'articles.created_at';
                    }
                    $query->orderBy($columnName, $orderDir);
                }
            } else {
                $query->orderBy('articles.created_at', 'desc');
            }

            // Count totals
            $total = Article::count();
            $filtered = (clone $query)->count();

            // Fetch data with pagination
            $data = $query
                ->skip($request->input('start'))
                ->take($request->input('length'))
                ->get();

            // Format response
            $formatted = $data->map(function ($article) {
                $editUrl = route('article.edit', $article->id);
                $deleteUrl = route('article.destroy', $article->id);

                $statusBadge = $article->status ? '<span class="badge bg-success">'.__('app.active').'</span>' : '<span class="badge bg-danger">'.__('app.inactive').'</span>';

                return [
                    "id" => $article->id,
                    "title" => $article->getLocalizationTitle(),
                    "small_description" => Str::limit($article->getLocalizationSmallDescription(), 50),
                    "status" => $statusBadge,
                    "created_at" => $article->created_at->format('Y-m-d'),
                    "action" => '
                        <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-delete" data-table="article" data-id="'.$article->id.'" title="'.__('app.delete').'">
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
            Log::error('Article AJAX Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => __('app.something went wrong while fetching articles'),
                'message' => config('app.debug') ? $e->getMessage() : __('app.something went wrong while fetching articles')
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('article.create');
        } catch (\Exception $e) {
            return redirect()->route('article.index')
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
                    'small_description_ar' => 'required|string|max:100',
                    'small_description_en' => 'required|string|max:100',
                    'full_description_ar' => 'required|string',
                    'full_description_en' => 'required|string',
                    'creation_date' => 'nullable|date',
                    'status' => 'nullable|boolean',
                    'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
                ],
                [
                    'title_ar.required' => __('app.title_ar is required'),
                    'title_ar.max' => __('app.title_ar must not exceed 50 characters'),
                    'title_en.required' => __('app.title_en is required'),
                    'title_en.max' => __('app.title_en must not exceed 50 characters'),
                    'small_description_ar.required' => __('app.small_description_ar is required'),
                    'small_description_ar.max' => __('app.small_description_ar must not exceed 100 characters'),
                    'small_description_en.required' => __('app.small_description_en is required'),
                    'small_description_en.max' => __('app.small_description_en must not exceed 100 characters'),
                    'full_description_ar.required' => __('app.full_description_ar is required'),
                    'full_description_en.required' => __('app.full_description_en is required'),
                    'creation_date.date' => __('app.creation_date must be a valid date'),
                    'image.required' => __('app.image is required'),
                    'image.image' => __('app.image must be an image'),
                    'image.mimes' => __('app.image must be jpeg, jpg, png, or webp'),
                    'image.max' => __('app.image must not exceed 2MB'),
                ]
            );

            $article = Article::create([
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'small_description_ar' => $validated['small_description_ar'],
                'small_description_en' => $validated['small_description_en'],
                'full_description_ar' => $validated['full_description_ar'],
                'full_description_en' => $validated['full_description_en'],
                'creation_date' => $validated['creation_date'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $article->addMedia($request->file('image'))->toMediaCollection('article');
            }

            return redirect()->route('article.index')
                ->with('success', __('app.created successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Article Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while creating the article'))
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        try {
            return view('article.edit', compact('article'));
        } catch (\Exception $e) {
            Log::error('Article Edit Error: ' . $e->getMessage());
            return redirect()->route('article.index')
                ->with('error', __('app.something went wrong while loading the form'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        try {
            $hasExistingImage = $article->getMedia('article')->isNotEmpty();
            $isRemovingImage = $request->input('remove_image') == '1';
            $imageRule = ($hasExistingImage && !$isRemovingImage)
                ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240'
                : 'required|image|mimes:jpg,jpeg,png,webp|max:10240';

            $validated = $request->validate(
                [
                    'title_ar' => 'required|string|max:50',
                    'title_en' => 'required|string|max:50',
                    'small_description_ar' => 'required|string|max:100',
                    'small_description_en' => 'required|string|max:100',
                    'full_description_ar' => 'required|string',
                    'full_description_en' => 'required|string',
                    'creation_date' => 'nullable|date',
                    'status' => 'nullable|boolean',
                    'image' => $imageRule,
                ],
                [
                    'title_ar.required' => __('app.title_ar is required'),
                    'title_ar.max' => __('app.title_ar must not exceed 50 characters'),
                    'title_en.required' => __('app.title_en is required'),
                    'title_en.max' => __('app.title_en must not exceed 50 characters'),
                    'small_description_ar.required' => __('app.small_description_ar is required'),
                    'small_description_ar.max' => __('app.small_description_ar must not exceed 100 characters'),
                    'small_description_en.required' => __('app.small_description_en is required'),
                    'small_description_en.max' => __('app.small_description_en must not exceed 100 characters'),
                    'full_description_ar.required' => __('app.full_description_ar is required'),
                    'full_description_en.required' => __('app.full_description_en is required'),
                    'creation_date.date' => __('app.creation_date must be a valid date'),
                    'image.required' => __('app.image is required'),
                    'image.image' => __('app.image must be an image'),
                    'image.mimes' => __('app.image must be jpeg, jpg, png, or webp'),
                    'image.max' => __('app.image must not exceed 2MB'),
                ]
            );

            $article->update([
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'small_description_ar' => $validated['small_description_ar'],
                'small_description_en' => $validated['small_description_en'],
                'full_description_ar' => $validated['full_description_ar'],
                'full_description_en' => $validated['full_description_en'],
                'creation_date' => $validated['creation_date'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            // Handle image removal if requested
            if ($request->filled('remove_image') && $request->input('remove_image') == '1') {
                $article->clearMediaCollection('article');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $article->clearMediaCollection('article');
                $article->addMedia($request->file('image'))->toMediaCollection('article');
            }

            return redirect()->route('article.index')
                ->with('success', __('app.updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Article Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('app.something went wrong while updating the article'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        try {
            $article->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the article')
            ], 500);
        }
    }

    /**
     * Change article status.
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);
            
            $request->validate([
                'status' => 'required|boolean'
            ]);

            $oldStatus = $article->status;
            $newStatus = (bool) $request->input('status');

            $article->status = $newStatus;
            $article->save();

            return response()->json([
                'status' => 'success',
                'message' => __('app.status updated successfully')
            ]);
        } catch (\Exception $e) {
            Log::error('Article Status Change Error: ' . $e->getMessage());
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
            
            // Upload the image to media library in article-content collection
            $media = $contentImage->addMedia($request->file('image'))
                ->toMediaCollection('article-content');

            return response()->json([
                'success' => true,
                'url' => $media->getUrl()
            ]);
        } catch (\Exception $e) {
            Log::error('Article Image Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('app.something went wrong while uploading the image')
            ], 500);
        }
    }

    /**
     * Get additional information for article type.
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
     * Store or update additional information for article type.
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
                ['type' => 'article'],
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
