<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\AdditionalInformation;
use App\Http\Resources\Api\V1\TeacherListResource;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TeacherController extends Controller
{
    use ApiResponse;

    /**
     * List active teachers only.
     * Returns: image, name, position, reviews, number of classes, number of students.
     *
     * Query filters (all optional):
     * - search: text search on teacher name (AR/EN) and position name (AR/EN)
     * - class_id: filter teachers that teach in this class
     * - course_id: filter teachers that teach this course
     * - academic_level_id: filter teachers that have at least one course in this academic level
     */
    public function index(Request $request)
    {
        try {
            $query = Teacher::getActive()->with([
                'position',
                'courses' => fn ($q) => $q->where('status', true),
            ]);

            // 1. Search text (teacher name + position name)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhereHas('position', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                        });
                });
            }

            // 2. Filter by class (supports single id, comma-separated ids, or array)
            $classIds = $this->parseIdsFilter($request->input('class_id'));
            if (!empty($classIds)) {
                $query->whereHas('courses', function ($q) use ($classIds) {
                    $q->whereIn('class_id', $classIds);
                });
            }

            // 3. Filter by course (supports single id, comma-separated ids, or array)
            $courseIds = $this->parseIdsFilter($request->input('course_id'));
            if (!empty($courseIds)) {
                $query->whereHas('courses', function ($q) use ($courseIds) {
                    $q->whereIn('courses.id', $courseIds);
                });
            }

            // 4. Filter by academic level (teachers that have at least one course in this academic level)
            if ($request->filled('academic_level_id')) {
                $query->whereHas('courses', function ($q) use ($request) {
                    $q->where('academic_level_id', $request->input('academic_level_id'));
                });
            }

            $teachers = $query->orderBy('name')->get();

            $collection = TeacherListResource::collection($teachers);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'teachers')->first();
            $sectionTitle = $additionalInfo ? $additionalInfo->getLocalizationTitle() : 'Teachers';
            $sectionDescription = $additionalInfo ? $additionalInfo->getLocalizationDescription() : '';

            return $this->success([
                'section_title'       => $sectionTitle,
                'section_description' => $sectionDescription,
                'data'                => $resolved['data'] ?? $resolved,
            ], 'Teachers fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch teachers', 500, [$e->getMessage()]);
        }
    }

    /**
     * Show a single active teacher by id.
     */
    public function show($id)
    {
        try {
            $teacher = Teacher::getActive()
                ->with([
                    'position',
                    'courses' => fn ($q) => $q->where('status', true),
                ])
                ->find($id);

            if (!$teacher) {
                return $this->error('Teacher not found', 404);
            }

            return $this->success(
                new TeacherListResource($teacher),
                'Teacher details fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch teacher', 500, [$e->getMessage()]);
        }
    }

    /**
     * Normalize ids filter from:
     * - single value: 3
     * - comma-separated: "1,2,3"
     * - array: [1,2,3]
     *
     * @return array<int>
     */
    private function parseIdsFilter(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $ids = array_map(static fn ($id) => (int) $id, Arr::flatten($value));
        $ids = array_values(array_unique(array_filter($ids, static fn ($id) => $id > 0)));

        return $ids;
    }
}
