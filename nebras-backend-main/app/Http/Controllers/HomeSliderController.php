<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use Illuminate\Http\Request;

class HomeSliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('home_sliders.index');
    }

    public function getAjaxData(Request $request){
        $query = new HomeSlider();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('button_title', 'like', "%{$search}%")
                    ->orWhere('button_title_en', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'image',
            'title',
            'button_title',
            'created_at',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'id';
            if ($columnName === 'created_at') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Count totals (cloned for filtering)
        $total = HomeSlider::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($slider) {
            $viewUrl = route('home-sliders.show', $slider->id);
            $editUrl = route('home-sliders.edit', $slider->id);
            $deleteUrl = route('home-sliders.destroy', $slider->id);

            return [
                "id" => $slider->id,
                "image" => "<img src='".$slider->image."' style='height:40px;'>",
                "title" => $slider->getLocalizationTitle(),
                "button_title" => "<a class='btn btn-primary' target='_blank' href='".$slider->getLocalizationButtonLink()."'>".$slider->getLocalizationButtonTitle()."</a>",
                "created_at" => $slider->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye "></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="home-sliders" data-id="'.$slider->id.'" title="'.__('app.delete').'">
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
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('home_sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'title_en'        => 'required|string|max:255',
            'description'     => 'required|string',
            'description_en'  => 'required|string',
            'button_title'    => 'required|string|max:255',
            'button_title_en' => 'required|string|max:255',
            'button_link'     => 'nullable|url|max:255',
            'button_link_en'  => 'nullable|url|max:255',
            'image'           => 'required|image|mimes:jpg,jpeg,png,webp|max:10240'
        ]);

        $home_slider = HomeSlider::create($validated);
        
        $home_slider->addMedia($request->file('image'))->toMediaCollection('home_sliders');

        return redirect()->route('home-sliders.index')
            ->with('success', __('app.created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(HomeSlider $homeSlider)
    {
        return view('home_sliders.show', compact('homeSlider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HomeSlider $homeSlider)
    {
        return view('home_sliders.edit', compact('homeSlider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HomeSlider $homeSlider)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'title_en'        => 'required|string|max:255',
            'description'     => 'required|string',
            'description_en'  => 'required|string',
            'button_title'    => 'required|string|max:255',
            'button_title_en' => 'required|string|max:255',
            'button_link'     => 'nullable|url|max:255',
            'button_link_en'  => 'nullable|url|max:255',
            'image'           => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:10240'
        ]);

        $homeSlider->update($validated);

        if ($request->hasFile('image')) {
            $homeSlider->clearMediaCollection('home_sliders');
            $homeSlider->addMedia($request->file('image'))->toMediaCollection('home_sliders');
        }

        return redirect()->route('home-sliders.index')
            ->with('success', __('app.updated successfully'));
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(HomeSlider $homeSlider)
    {
        try {
            $homeSlider->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the slider.')
            ], 500);
        }
    }
}
