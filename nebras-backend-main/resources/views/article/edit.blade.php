@extends('layouts.app')
@section('title'){{ __('app.article') }} - {{ __('app.edit') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.article') }} - {{ __('app.edit') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    <form action="{{ route('article.update', $article->id) }}" method="POST" enctype="multipart/form-data" id="article-form">
                        @csrf
                        @method('PUT')
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row mb-3">
                                {{-- Title AR --}}
                                <div class="col-md-6">
                                    <label for="title_ar" class="form-label fw-bold">{{ __('app.title') }} (AR) <small class="text-muted">({{ __('app.max') }}: 50 {{ __('app.characters') }})</small></label>
                                    <input type="text" name="title_ar" id="title_ar" placeholder="{{ __('app.title') }} (AR)"
                                        class="form-control" value="{{ old('title_ar', $article->title_ar) }}" maxlength="50" required>
                                    @error('title_ar')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Title EN --}}
                                <div class="col-md-6">
                                    <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN) <small class="text-muted">({{ __('app.max') }}: 50 {{ __('app.characters') }})</small></label>
                                    <input type="text" name="title_en" id="title_en" placeholder="{{ __('app.title') }} (EN)"
                                        class="form-control" value="{{ old('title_en', $article->title_en) }}" maxlength="50" required>
                                    @error('title_en')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Short Description AR --}}
                                <div class="col-md-6">
                                    <label for="small_description_ar" class="form-label fw-bold">{{ __('app.short description') }} (AR) <small class="text-muted">({{ __('app.max') }}: 100 {{ __('app.characters') }})</small></label>
                                    <textarea name="small_description_ar" id="small_description_ar" class="form-control" rows="3" maxlength="100" required>{{ old('small_description_ar', $article->small_description_ar) }}</textarea>
                                    <small class="text-muted"><span id="small_description_ar_count">0</span>/100</small>
                                    @error('small_description_ar')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Short Description EN --}}
                                <div class="col-md-6">
                                    <label for="small_description_en" class="form-label fw-bold">{{ __('app.short description') }} (EN) <small class="text-muted">({{ __('app.max') }}: 100 {{ __('app.characters') }})</small></label>
                                    <textarea name="small_description_en" id="small_description_en" class="form-control" rows="3" maxlength="100" required>{{ old('small_description_en', $article->small_description_en) }}</textarea>
                                    <small class="text-muted"><span id="small_description_en_count">0</span>/100</small>
                                    @error('small_description_en')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Full Description AR --}}
                                <div class="col-md-6">
                                    <label for="full_description_ar" class="form-label fw-bold">{{ __('app.full description') }} (AR)</label>
                                    <div id="editor-ar" style="height: 300px;"></div>
                                    <input type="hidden" name="full_description_ar" id="full_description_ar_input">
                                    @error('full_description_ar')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Full Description EN --}}
                                <div class="col-md-6">
                                    <label for="full_description_en" class="form-label fw-bold">{{ __('app.full description') }} (EN)</label>
                                    <div id="editor-en" style="height: 300px;"></div>
                                    <input type="hidden" name="full_description_en" id="full_description_en_input">
                                    @error('full_description_en')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Image --}}
                                <div class="col-md-12">
                                    <label for="image">{{ __('app.image') }} ({{ __('app.extension') }}: jpg, jpeg, png, webp — {{ __('app.max size') }}: 10MB)</label>
                                    <input type="file"
                                           id="image"
                                           name="image"
                                           data-default-file="{{ $article->image }}"
                                           data-plugins="dropify"
                                           data-height="150"
                                           data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                    {{-- Flag to remove existing image when cleared --}}
                                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                                    @error('image')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Creation Date --}}
                                <div class="col-md-6">
                                    <label for="creation_date" class="form-label fw-bold">{{ __('app.creation date') }}</label>
                                    <input type="text" name="creation_date" id="creation_date" class="form-control" 
                                        placeholder="{{ __('app.creation date') }}" 
                                        value="{{ old('creation_date', $article->creation_date ? $article->creation_date->format('Y-m-d H:i') : '') }}">
                                    @error('creation_date')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" 
                                            name="status" 
                                            type="checkbox" 
                                            value="1" 
                                            role="switch" 
                                            id="status"
                                            {{ old('status', $article->status ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">{{ __('app.status') }}</label>
                                    </div>
                                    @error('status')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('article.index') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.update') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    $(document).ready(function() {
        // Custom image handler function
        function imageHandler(quillInstance) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function() {
                var file = input.files[0];
                if (file) {
                    var formData = new FormData();
                    formData.append('image', file);

                    // Show loading indicator
                    var range = quillInstance.getSelection(true);
                    quillInstance.insertText(range.index, 'Uploading image...', 'user');
                    quillInstance.setSelection(range.index + 19);

                    $.ajax({
                        url: '{{ route("article.upload-image") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove loading text
                                var currentIndex = quillInstance.getSelection().index;
                                quillInstance.deleteText(currentIndex - 19, 19);
                                
                                // Insert image with URL
                                quillInstance.insertEmbed(currentIndex - 19, 'image', response.url);
                            } else {
                                alert('Failed to upload image: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr) {
                            var currentIndex = quillInstance.getSelection().index;
                            quillInstance.deleteText(currentIndex - 19, 19);
                            var errorMsg = xhr.responseJSON && xhr.responseJSON.message 
                                ? xhr.responseJSON.message 
                                : 'Failed to upload image';
                            alert(errorMsg);
                        }
                    });
                }
            };
        }

        // Initialize Quill editors with existing content
        var quillAr = new Quill('#editor-ar', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        'image': function() {
                            imageHandler(quillAr);
                        }
                    }
                }
            }
        });

        var quillEn = new Quill('#editor-en', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        'image': function() {
                            imageHandler(quillEn);
                        }
                    }
                }
            }
        });

        // Set existing content
        var contentAr = {!! json_encode(old('full_description_ar', $article->full_description_ar)) !!};
        var contentEn = {!! json_encode(old('full_description_en', $article->full_description_en)) !!};
        if (contentAr) {
            quillAr.root.innerHTML = contentAr;
        }
        if (contentEn) {
            quillEn.root.innerHTML = contentEn;
        }

        // Initialize Flatpickr for creation date
        var creationDatePicker = flatpickr("#creation_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            @if(app()->getLocale() == 'ar')
            locale: "ar"
            @endif
        });

        // Handle image removal via Dropify
        var drEvent = $('#image').dropify();

        drEvent.on('dropify.afterClear', function(event, element){
            // Mark image for removal when user clears it
            $('#remove_image').val('1');
        });

        drEvent.on('dropify.errors', function(event, element){
            // If there are errors, do not mark for removal automatically
            $('#remove_image').val('0');
        });

        // Update hidden inputs before form submission
        $('#article-form').on('submit', function() {
            $('#full_description_ar_input').val(quillAr.root.innerHTML);
            $('#full_description_en_input').val(quillEn.root.innerHTML);
        });

        // Character counter for small descriptions
        function updateCharCount(textareaId, counterId, maxLength) {
            var textarea = $('#' + textareaId);
            var counter = $('#' + counterId);
            
            textarea.on('input', function() {
                var length = $(this).val().length;
                counter.text(length);
                if (length > maxLength) {
                    counter.addClass('text-danger');
                } else {
                    counter.removeClass('text-danger');
                }
            });
            
            // Initialize counter on page load
            var initialLength = textarea.val().length;
            counter.text(initialLength);
        }

        updateCharCount('small_description_ar', 'small_description_ar_count', 100);
        updateCharCount('small_description_en', 'small_description_en_count', 100);
    });
</script>
@endpush
