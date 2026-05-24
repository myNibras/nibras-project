@extends('layouts.teacher')
@section('title'){{ __('app.notifications') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white">
                    <h6 class="mb-0">{{ __('app.notifications') }}</h6>
                </div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <p class="text-secondary mb-0">{{ __('app.no results found') }}</p>
                    @else
                        <ul class="list-group">
                            @foreach($notifications as $notification)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-1 text-sm fw-bold">{{ $notification->title ?? '' }}</p>
                                        <p class="mb-0 text-xs text-secondary">{{ $notification->body ?? '' }}</p>
                                    </div>
                                    <span class="badge bg-secondary text-xs">{{ $notification->created_at ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

