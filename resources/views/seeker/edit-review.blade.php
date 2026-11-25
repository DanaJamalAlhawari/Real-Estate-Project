@extends('layouts.app', ['homes' => []])

@section('title', __('Edit Review'))
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />
    <main class="app-page container">
        <div class="page-head">
            <h1>{{ __('Edit Review') }}</h1>
        </div>

        <div class="panel">
            <form action="{{ route('seeker.reviews.update', ['review' => $review->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="rating" class="form-label">{{ __('Rating') }}</label>

                    <select name="rating" id="rating" class="form-select" required>
                        <option value="1" @selected(old('rating', $review->rating) == 1)>{{ __('★ — 1 star') }}</option>
                        <option value="2" @selected(old('rating', $review->rating) == 2)>{{ __('★★ — 2 stars') }}</option>
                        <option value="3" @selected(old('rating', $review->rating) == 3)>{{ __('★★★ — 3 stars') }}</option>
                        <option value="4" @selected(old('rating', $review->rating) == 4)>{{ __('★★★★ — 4 stars') }}</option>
                        <option value="5" @selected(old('rating', $review->rating) == 5)>{{ __('★★★★★ — 5 stars') }}</option>
                    </select>

                    @error('rating')
                        <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">{{ __('Comment (optional)') }}</label>

                    <textarea
                        name="comment"
                        id="comment"
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                    >{{ old('comment', $review->comment) }}</textarea>

                    @error('comment')
                        <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ __('Save changes') }}
                </button>

                <a
                    href="{{ route('seeker.reviews.index', ['property' => $review->property_id]) }}"
                    class="btn btn-outline-primary"
                >
                    {{ __('Cancel') }}
                </a>
            </form>
        </div>
    </main>
@endsection