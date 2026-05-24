<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsResource;
use App\Models\News;
use App\Models\AdditionalInformation;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use ApiResponse;

    /**
     * Get all active and non-expired news.
     */
    public function index()
    {
        try {
            $news = News::active()
                ->latest()
                ->get();

            $collection = NewsResource::collection($news);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'news')->first();
            $sectionTitle = $additionalInfo ? $additionalInfo->getLocalizationTitle() : 'News';
            $sectionDescription = $additionalInfo ? $additionalInfo->getLocalizationDescription() : '';

            return $this->success([
                'section_title'       => $sectionTitle,
                'section_description' => $sectionDescription,
                'data'                => $resolved['data'] ?? $resolved,
            ], 'News fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch news', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get a single news item by ID.
     */
    public function show($id)
    {
        try {
            $news = News::active()
                ->find($id);

            if (!$news) {
                return $this->error('News not found', 404);
            }

            return $this->success(
                new NewsResource($news),
                'News fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch news', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get 3 related news items, excluding the provided ID if given.
     */
    public function relatedNews(Request $request)
    {
        try {
            $query = News::active();

            // Exclude the provided ID if given
            if ($request->has('id') && $request->id) {
                $query->where('id', '!=', $request->id);
            }

            $relatedNews = $query->inRandomOrder()
                ->limit(3)
                ->get();

            return $this->success(
                NewsResource::collection($relatedNews),
                'Related news fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch related news', 500, [$e->getMessage()]);
        }
    }
}

