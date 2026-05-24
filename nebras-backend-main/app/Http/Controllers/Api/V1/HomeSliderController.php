<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HomeSliderResource;
use App\Models\HomeSlider;
use App\Traits\Api\V1\ApiResponse;

class HomeSliderController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $sliders = HomeSlider::latest()->get();
            return $this->success(HomeSliderResource::collection($sliders), 'Home sliders fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch sliders', 500, [$e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $slider = HomeSlider::find($id);
            if (!$slider) {
                return $this->error('Slider not found', 404);
            }
            return $this->success(new HomeSliderResource($slider), 'Slider details fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch slider', 500, [$e->getMessage()]);
        }
    }
}
