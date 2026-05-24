@extends('layouts.teacher')
@section('title'){{ __('app.edit') }} {{ __('app.profile') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                    <h6 class="text-white text-capitalize mb-0">{{ __('app.edit') }} {{ __('app.profile') }}</h6>
                    <a href="{{ route('teacher.profile') }}" class="btn btn-light btn-sm mb-0">
                        <i class="fa-solid fa-reply me-1"></i>{{ __('app.back') }}
                    </a>
                </div>
                <div class="card-body px-0 pb-2">
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teacher.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mx-4 row-gap-3 w-100">

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }} (AR)</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('app.name') }} (AR)"
                                        value="{{ old('name', $teacher->name) }}" maxlength="50" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label fw-bold">{{ __('app.name') }} (EN)</label>
                                    <input type="text" name="name_en" id="name_en" class="form-control" placeholder="{{ __('app.name') }} (EN)"
                                        value="{{ old('name_en', $teacher->name_en) }}" maxlength="50" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-bold">{{ __('app.email') }}</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('app.email') }}"
                                        value="{{ old('email', $teacher->email) }}" maxlength="255" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-bold">
                                        {{ __('app.password') }}
                                        <small class="text-muted">({{ __('app.leave blank to keep current') }})</small>
                                    </label>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('app.password') }}" minlength="8">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="position_id" class="form-label fw-bold">{{ __('app.position') }}</label>
                                    <select name="position_id" id="position_id" class="form-select">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', $teacher->position_id) == $position->id ? 'selected' : '' }}>
                                                {{ $position->getLocalizationName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="years_of_experience" class="form-label fw-bold">{{ __('app.years of experience') }}</label>
                                    <input type="number" name="years_of_experience" id="years_of_experience" min="0" step="1"
                                        class="form-control" value="{{ old('years_of_experience', $teacher->years_of_experience ?? 0) }}" placeholder="0">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="description" class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                                    <textarea name="description" id="description" class="form-control" rows="4" maxlength="5000" placeholder="{{ __('app.description') }} (AR)">{{ old('description', $teacher->description) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="description_en" class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                                    <textarea name="description_en" id="description_en" class="form-control" rows="4" maxlength="5000" placeholder="{{ __('app.description') }} (EN)">{{ old('description_en', $teacher->description_en) }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label for="image" class="form-label fw-bold">{{ __('app.image') }}</label>
                                    <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <small class="mt-2 d-block">{{ __('app.size') }} (275x325px) — Allow (jpg, jpeg, png, webp) — Max 10MB</small>
                                    @if($teacher->image)
                                        <div class="mt-2">
                                            <img src="{{ $teacher->image }}" alt="" class="rounded" style="max-height: 120px;">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                                <label class="form-check-label" for="remove_image">{{ __('app.remove image') }}</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">{{ __('app.video') }}</label>

                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <span class="badge bg-info text-white">
                                            <i class="material-symbols-rounded align-middle" style="font-size: 16px;">info</i>
                                            {{ __('app.maximum video size') }}: 50MB
                                        </span>
                                        <span class="text-muted small">
                                            {{ __('app.allowed formats') }}: MP4, MOV, WEBM
                                        </span>
                                    </div>

                                    @if($teacher->video_embed_url)
                                        <div class="mb-2">
                                            <iframe src="{{ $teacher->video_embed_url }}" class="rounded"
                                                style="width: 100%; max-width: 400px; height: 225px; border: 0;"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen></iframe>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_video" id="remove_video" value="1">
                                                <label class="form-check-label" for="remove_video">{{ __('app.remove video') }}</label>
                                            </div>
                                        </div>
                                    @elseif($teacher->video)
                                        <div class="mb-2">
                                            <video src="{{ $teacher->video }}" controls style="max-width: 100%; max-height: 200px;" class="rounded"></video>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_video" id="remove_video" value="1">
                                                <label class="form-check-label" for="remove_video">{{ __('app.remove video') }}</label>
                                            </div>
                                        </div>
                                    @endif

                                    <ul class="nav nav-tabs mb-2" role="tablist" id="videoSourceTabs">
                                        <li class="nav-item" role="presentation">
                                            <button type="button" class="nav-link active" id="video-upload-tab"
                                                data-bs-toggle="tab" data-bs-target="#video-upload-pane"
                                                role="tab" aria-controls="video-upload-pane" aria-selected="true">
                                                {{ __('app.upload file') }}
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button type="button" class="nav-link" id="video-youtube-tab"
                                                data-bs-toggle="tab" data-bs-target="#video-youtube-pane"
                                                role="tab" aria-controls="video-youtube-pane" aria-selected="false">
                                                {{ __('app.youtube link') }}
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="video-upload-pane" role="tabpanel" aria-labelledby="video-upload-tab">
                                            <input type="file" id="video" name="video" class="form-control" accept="video/mp4,video/quicktime,video/webm">
                                            <small class="mt-2 d-block text-muted">
                                                MP4, MOV, WEBM &mdash; {{ __('app.maximum video size') }}: 50MB.
                                                {{ ($teacher->video || $teacher->video_url) ? __('app.upload new to replace') : '' }}
                                            </small>
                                        </div>
                                        <div class="tab-pane fade" id="video-youtube-pane" role="tabpanel" aria-labelledby="video-youtube-tab">
                                            <input type="url" id="video_url" name="video_url" class="form-control"
                                                placeholder="https://youtu.be/..."
                                                value="{{ old('video_url', $teacher->video_url) }}" maxlength="500">
                                            <small class="mt-2 d-block text-muted">{{ __('app.paste a public youtube link') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @push('scripts')
                            <script>
                                (function () {
                                    var videoInput = document.getElementById('video');
                                    var videoUrlInput = document.getElementById('video_url');
                                    var uploadTab = document.getElementById('video-upload-tab');
                                    var youtubeTab = document.getElementById('video-youtube-tab');
                                    var MAX_BYTES = 50 * 1024 * 1024;
                                    var hasYoutubeUrl = videoUrlInput && videoUrlInput.value.trim().length > 0;

                                    if (hasYoutubeUrl && youtubeTab) {
                                        var tab = new bootstrap.Tab(youtubeTab);
                                        tab.show();
                                    }

                                    if (videoInput) {
                                        videoInput.addEventListener('change', function (e) {
                                            var file = e.target.files && e.target.files[0];
                                            if (!file) return;
                                            if (file.size > MAX_BYTES) {
                                                if (typeof toastr !== 'undefined') {
                                                    toastr.error(@json(__('app.video too large 50mb max')));
                                                } else {
                                                    alert(@json(__('app.video too large 50mb max')));
                                                }
                                                e.target.value = '';
                                                return;
                                            }
                                            if (videoUrlInput) videoUrlInput.value = '';
                                        });
                                    }

                                    if (videoUrlInput) {
                                        videoUrlInput.addEventListener('input', function () {
                                            if (videoUrlInput.value.trim() && videoInput) {
                                                videoInput.value = '';
                                            }
                                        });
                                    }

                                    if (uploadTab) {
                                        uploadTab.addEventListener('shown.bs.tab', function () {
                                            if (videoUrlInput) videoUrlInput.value = '';
                                        });
                                    }
                                    if (youtubeTab) {
                                        youtubeTab.addEventListener('shown.bs.tab', function () {
                                            if (videoInput) videoInput.value = '';
                                        });
                                    }
                                })();
                            </script>
                            @endpush

                            <div class="d-flex justify-content-end mt-4 gap-2 align-items-center">
                                @if(session('impersonate.admin_id'))
                                <span class="text-muted small me-2">{{ __('app.view only mode no changes allowed') }}</span>
                                <button type="button" class="btn btn-primary" disabled>{{ __('app.update') }}</button>
                                @else
                                <a href="{{ route('teacher.profile') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
