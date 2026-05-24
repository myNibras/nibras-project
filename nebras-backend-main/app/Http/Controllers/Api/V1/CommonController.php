<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassRoom;
use App\Http\Resources\Api\V1\ClassRoomResource;
use App\Traits\Api\V1\ApiResponse;

class CommonController extends Controller
{
    use ApiResponse;

    /**
     * Return all classes.
     */
    public function getAllClasses()
    {
        try {
            $classes = ClassRoom::all();
            return $this->success(ClassRoomResource::collection($classes), 'Classes fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch classes', 500, [$e->getMessage()]);
        }
    }
}
