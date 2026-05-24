<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FaqResource;
use App\Models\Faq;
use App\Models\AdditionalInformation;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of active FAQs only.
     */
    public function index(Request $request)
    {
        try {
            $limit = (int) $request->query('limit', 0);
            $query = Faq::getActive()->orderedAsc();
            $faqs = $limit > 0 ? $query->limit($limit)->get() : $query->get();
            $collection = FaqResource::collection($faqs);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'faq')->first();
            $locale = app()->getLocale();
            $defaultTitle = $locale === 'ar' ? 'الأسئلة الشائعة' : 'Frequently Asked Questions';
            $defaultDescription = $locale === 'ar'
                ? 'تجد إجابات أسئلتك الشائعة أدناه'
                : 'Find answers to common questions below';
            $sectionTitle = $additionalInfo ? $additionalInfo->getLocalizationTitle() : $defaultTitle;
            $sectionDescription = $additionalInfo ? $additionalInfo->getLocalizationDescription() : $defaultDescription;

            return $this->success([
                'section_title'       => $sectionTitle,
                'section_description' => $sectionDescription,
                'data'                => $resolved['data'] ?? $resolved,
            ], 'FAQs fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch FAQs', 500, [$e->getMessage()]);
        }
    }

    /**
     * Display the specified FAQ.
     */
    public function show($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return $this->error('FAQ not found', 404);
            }

            return $this->success(
                new FaqResource($faq),
                'FAQ details fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch FAQ', 500, [$e->getMessage()]);
        }
    }
}
