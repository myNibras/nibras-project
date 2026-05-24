<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('faqs.index');
    }

    public function getAjaxData(Request $request)
    {
        // Always return all FAQs ordered by manual `order`, NULLs last (then by id).
        // Drag-and-drop ordering needs the full list in the same order on every reload.
        $faqs = Faq::orderedAsc()->get();

        $formatted = $faqs->map(function ($faq) {
            $viewUrl = route('faqs.show', $faq->id);
            $editUrl = route('faqs.edit', $faq->id);

            $checked = $faq->status ? 'checked' : '';
            $message = $faq->status
                ? __('app.are you sure you want to deactivate this faq?')
                : __('app.are you sure you want to activate this faq?');

            $statusHtml = "<div class='form-check form-switch mt-2'>
                <input class='form-check-input update-status'
                    name='status'
                    type='checkbox'
                    value='1'
                    role='switch'
                    id='status-{$faq->id}'
                    data-table='faqs'
                    data-id='{$faq->id}'
                    data-message='{$message}'
                    {$checked}
                />
            </div>";

            $dragHandle = '<span class="drag-handle text-secondary" style="cursor: grab;" title="'.__('app.drag to reorder').'"><i class="fa-solid fa-grip-vertical"></i></span>';

            return [
                'DT_RowAttr' => ['data-id' => $faq->id],
                'drag' => $dragHandle,
                'id' => $faq->id,
                'question' => Str::limit($faq->getLocalizationQuestion(), 50),
                'answer' => Str::limit(strip_tags($faq->getLocalizationAnswer()), 60),
                'status' => $statusHtml,
                'created_at' => $faq->created_at->format('Y-m-d'),
                'action' => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="faqs" data-id="'.$faq->id.'" title="'.__('app.delete').'">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $faqs->count(),
            'recordsFiltered' => $faqs->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Persist a new manual ordering for FAQs.
     * Expects: { ids: [12, 7, 3, ...] } — the ids in the desired display order.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:faqs,id',
        ]);

        try {
            \DB::transaction(function () use ($request) {
                foreach ($request->input('ids') as $position => $id) {
                    Faq::where('id', $id)->update(['order' => $position + 1]);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => __('app.order updated successfully'),
            ]);
        } catch (\Exception $e) {
            Log::error('FAQ Reorder Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => __('app.something went wrong'),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        return view('faqs.create');
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'question' => 'required|string|max:100',
                'question_en' => 'required|string|max:100',
                'answer' => 'required|string',
                'answer_en' => 'required|string',
                'status' => 'nullable|boolean',
                'order' => 'nullable|integer|min:1',
            ],
            [
                'question.required' => __('messages.faq_question_required'),
                'question.string' => __('messages.faq_question_string'),
                'question.max' => __('messages.faq_question_max'),
                'question_en.required' => __('messages.faq_question_en_required'),
                'question_en.string' => __('messages.faq_question_en_string'),
                'question_en.max' => __('messages.faq_question_en_max'),
                'answer.required' => __('messages.faq_answer_required'),
                'answer.string' => __('messages.faq_answer_string'),
                'answer_en.required' => __('messages.faq_answer_en_required'),
                'answer_en.string' => __('messages.faq_answer_en_string'),
            ]
        );

        // Order is managed via drag-and-drop in the index view, not by the form.
        // New FAQs are appended to the end of the list.
        $nextOrder = (int) (Faq::max('order') ?? 0) + 1;

        Faq::create([
            'question' => $request->question,
            'question_en' => $request->question_en,
            'answer' => $request->answer,
            'answer_en' => $request->answer_en,
            'status' => $request->boolean('status'),
            'order' => $nextOrder,
        ]);

        return redirect()->route('faqs.index')
            ->with('success', __('app.created successfully'));
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq)
    {
        return view('faqs.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq)
    {
        return view('faqs.edit', compact('faq'));
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate(
            [
                'question' => 'required|string|max:100',
                'question_en' => 'required|string|max:100',
                'answer' => 'required|string',
                'answer_en' => 'required|string',
                'status' => 'nullable|boolean',
                'order' => 'nullable|integer|min:1',
            ],
            [
                'question.required' => __('messages.faq_question_required'),
                'question.string' => __('messages.faq_question_string'),
                'question.max' => __('messages.faq_question_max'),
                'question_en.required' => __('messages.faq_question_en_required'),
                'question_en.string' => __('messages.faq_question_en_string'),
                'question_en.max' => __('messages.faq_question_en_max'),
                'answer.required' => __('messages.faq_answer_required'),
                'answer.string' => __('messages.faq_answer_string'),
                'answer_en.required' => __('messages.faq_answer_en_required'),
                'answer_en.string' => __('messages.faq_answer_en_string'),
            ]
        );

        // Preserve the existing manual order — it's only changed via drag-and-drop.
        $faq->update([
            'question' => $request->question,
            'question_en' => $request->question_en,
            'answer' => $request->answer,
            'answer_en' => $request->answer_en,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('faqs.index')
            ->with('success', __('app.updated successfully'));
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq)
    {
        try {
            $faq->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while deleting the faq'),
            ], 500);
        }
    }

    /**
     * Toggle FAQ status (active/inactive).
     */
    public function change_status($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->status = !$faq->status;
            $faq->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get additional information for FAQ type.
     */
    public function getAdditionalInfo($type)
    {
        try {
            $additionalInfo = AdditionalInformation::where('type', $type)->first();

            return response()->json([
                'status' => 'success',
                'data' => $additionalInfo,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while fetching additional information'),
            ], 500);
        }
    }

    /**
     * Store or update additional information for FAQ type.
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
                ['type' => 'faq'],
                [
                    'title' => $validated['title'],
                    'title_en' => $validated['title_en'],
                    'description' => $validated['description'] ?? null,
                    'description_en' => $validated['description_en'] ?? null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => __('app.saved successfully'),
                'data' => $additionalInfo,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.validation failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Additional Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('app.something went wrong while saving additional information'),
            ], 500);
        }
    }
}
