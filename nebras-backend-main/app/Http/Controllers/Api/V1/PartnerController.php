<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PartnerResource;
use App\Models\Partner;
use App\Models\AdditionalInformation;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Get all active partners.
     */
    public function index()
    {
        try {
            $partners = Partner::where('status', true)
                ->latest()
                ->get();

            // Get additional information for partners type
            $additionalInfo = AdditionalInformation::where('type', 'partners')->first();
            
            $sectionTitle = $additionalInfo ? $additionalInfo->getLocalizationTitle() : 'Our Partners';
            $sectionDescription = $additionalInfo ? $additionalInfo->getLocalizationDescription() : '';

            return $this->success([
                'section_title' => $sectionTitle,
                'section_description' => $sectionDescription,
                'data' => PartnerResource::collection($partners)
                ], 'Partners fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch partners', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get a single partner by ID.
     */
    public function show($id)
    {
        try {
            $partner = Partner::where('status', true)
                ->find($id);

            if (!$partner) {
                return $this->error('Partner not found', 404);
            }

            return $this->success(
                new PartnerResource($partner),
                'Partner fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch partner', 500, [$e->getMessage()]);
        }
    }
}
