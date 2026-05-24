<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicLevel;
use App\Http\Resources\Api\V1\AcademicLevelResource;
use App\Traits\Api\V1\ApiResponse;

class AcademicLevelController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $academic_levels = AcademicLevel::latest()->get();
            return $this->success(
                [
                'section_title' => 'Our Academic Levels',
                'section_description' => 'Choose the best level for you',
                'data' => AcademicLevelResource::collection($academic_levels)], 
                'Academic Level fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch academic Levels', 500, [$e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $academic_level = AcademicLevel::find($id);
            if (!$academic_level) {
                return $this->error('Academic Level not found', 404);
            }
            return $this->success(new AcademicLevelResource($academic_level), 'Academic Level details fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch academic Level', 500, [$e->getMessage()]);
        }
    }

    public function showBySlug($slug)
    {
        try {
            $locale = app()->getLocale();
            $isArabic = $locale === 'ar';

            $academic_level = AcademicLevel::where($isArabic ? 'slug' : 'slug_en', $slug)->first();

            if (!$academic_level) {
                return $this->error('Academic Level not found', 404);
            }

            return $this->success(
                new AcademicLevelResource($academic_level),
                'Academic Level details fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch academic level', 500, [$e->getMessage()]);
        }
    }

}
