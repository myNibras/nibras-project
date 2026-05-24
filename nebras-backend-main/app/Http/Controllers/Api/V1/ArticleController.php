<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use App\Models\AdditionalInformation;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponse;

    /**
     * Get all active and non-expired articles.
     */
    public function index()
    {
        try {
            $articles = Article::active()
                ->latest()
                ->get();

            $collection = ArticleResource::collection($articles);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'article')->first();
            $sectionTitle = $additionalInfo ? $additionalInfo->getLocalizationTitle() : 'Articles';
            $sectionDescription = $additionalInfo ? $additionalInfo->getLocalizationDescription() : '';

            return $this->success([
                'section_title'       => $sectionTitle,
                'section_description' => $sectionDescription,
                'data'                => $resolved['data'] ?? $resolved,
            ], 'Articles fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch articles', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get a single article item by ID.
     */
    public function show($id)
    {
        try {
            $article = Article::active()
                ->find($id);

            if (!$article) {
                return $this->error('Article not found', 404);
            }

            return $this->success(
                new ArticleResource($article),
                'Article fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch article', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get 3 related articles, excluding the article with the provided ID if given.
     */
    public function relatedArticles(Request $request)
    {
        try {
            $query = Article::active();

            // Exclude the article with the provided ID if given
            if ($request->has('id') && $request->id) {
                $query->where('id', '!=', $request->id);
            }

            // Get 3 random articles
            $relatedArticles = $query->inRandomOrder()
                ->limit(3)
                ->get();

            return $this->success(
                ArticleResource::collection($relatedArticles),
                'Related articles fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch related articles', 500, [$e->getMessage()]);
        }
    }
}
