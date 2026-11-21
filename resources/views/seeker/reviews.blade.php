@extends('layouts.app', ['homes' => []])

@section('title', 'Property Reviews')
@section('roleStyle', 'seeker')

@section('content')
    <style>
        .property-reviews {
            max-width: 1120px;
            padding-top: 44px;
            padding-bottom: 72px;
        }

        .property-reviews .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
            padding: 30px 34px;
            background: linear-gradient(135deg, #fffdf8 0%, #f0f3ec 100%);
            border: 1px solid #e3dfd4;
            border-radius: 24px;
            box-shadow: 0 12px 32px rgba(61, 55, 49, 0.06);
            animation: reviews-enter 500ms ease both;
        }

        .property-reviews .reviews-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #303a2d;
            font-family: Georgia, serif;
            font-size: clamp(25px, 4vw, 34px);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .property-reviews .reviews-heading .bi-star-fill {
            color: #c18a3b;
            font-family: inherit;
            font-size: 22px;
        }

        .property-reviews .reviews-subtitle {
            color: #717171;
            margin: 0;
            font-size: 16px;
        }

        .property-reviews .review-editor {
            margin-top: 24px;
            padding: 22px 26px;
            background: #fffdf8;
            border: 1px solid #e3dfd4;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(61, 55, 49, 0.045);
            animation: reviews-enter 600ms ease both;
            animation-delay: 70ms;
        }

        .property-reviews .review-editor > summary {
            cursor: pointer;
            font-weight: 600;
            color: #52634e;
            font-size: 17px;
        }

        .property-reviews .review-form {
            max-width: 650px;
            margin-top: 24px;
        }

        .property-reviews .review-form .form-control,
        .property-reviews .review-form .form-select {
            border-color: #dedbd3;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .property-reviews .review-form .form-control:focus,
        .property-reviews .review-form .form-select:focus {
            border-color: #78866f;
            box-shadow: 0 0 0 4px rgba(82, 99, 78, 0.12);
        }

        .property-reviews .reviews-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-top: 28px;
        }

        .property-reviews .reviews-grid > article {
            padding: 26px;
            background: #fffdf8;
            border: 1px solid #e5e1d7;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(61, 55, 49, 0.05);
            animation: reviews-enter 550ms ease both;
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        .property-reviews .reviews-grid > article:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(61, 55, 49, 0.1);
        }

        .property-reviews .reviews-grid > article:nth-child(2) { animation-delay: 70ms; }
        .property-reviews .reviews-grid > article:nth-child(3) { animation-delay: 140ms; }
        .property-reviews .reviews-grid > article:nth-child(4) { animation-delay: 210ms; }

        .property-reviews .review-person {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 18px;
        }

        .property-reviews .review-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(145deg, #edf1e8, #dfe7d9);
            color: #52634e;
            font-size: 20px;
            font-weight: 600;
        }

        .property-reviews .review-name {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .property-reviews .review-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .property-reviews .review-stars {
            color: #c18a3b;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .property-reviews .review-date {
            color: #717171;
            font-size: 14px;
        }

        .property-reviews .review-comment {
            color: #45483f;
            font-size: 15px;
            line-height: 1.85;
            white-space: pre-line;
            overflow-wrap: anywhere;
            margin-bottom: 0;
        }

        .property-reviews .review-more {
            margin-top: 10px;
        }

        .property-reviews .review-more > summary {
            cursor: pointer;
            text-decoration: underline;
            font-weight: 600;
        }

        .property-reviews .review-actions {
            display: flex;
            gap: 18px;
            margin-top: 16px;
        }

        .property-reviews .review-action {
            padding: 0;
            border: 0;
            background: transparent;
            color: #52634e;
            font-size: 14px;
            text-decoration: underline;
            cursor: pointer;
        }

        .property-reviews .review-action-delete {
            color: #a44932;
        }

        .property-reviews .reviews-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 54px 24px;
            background: #fffdf8;
            border: 1px dashed #d8d5ca;
            border-radius: 20px;
            color: #62685d;
            animation: reviews-enter 500ms ease both;
        }

        @keyframes reviews-enter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767px) {
            .property-reviews .reviews-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .property-reviews .reviews-header {
                padding: 24px 20px;
                border-radius: 18px;
            }

            .property-reviews .review-editor,
            .property-reviews .reviews-grid > article {
                padding: 20px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .property-reviews *,
            .property-reviews *::before,
            .property-reviews *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    <x-seeker-navbar />

    <main class="app-page container property-reviews">
        <header class="reviews-header">
            <div>
                <h1 class="reviews-heading">
                    @if ($reviews->count() > 0)
                        <i class="bi bi-star-fill" aria-hidden="true"></i>
                        {{ number_format($reviews->avg('rating'), 2) }}
                        · {{ $reviews->count() }} reviews
                    @else
                        Property Reviews
                    @endif
                </h1>

                <p class="reviews-subtitle">{{ $property->title }}</p>
            </div>

            <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary">
                Back to properties
            </a>
        </header>

@if (!$myReview)
        <details class="review-editor" @if ($errors->any()) open @endif>
            <summary>Write a review</summary>



            <form
                action="{{ route('seeker.reviews.store', ['property' => $property->id]) }}"
                method="POST"
                class="review-form"
            >
                @csrf

                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>

                    <select name="rating" id="rating" class="form-select" required>
                        <option value="">Choose a rating</option>
                        <option value="1" @selected(old('rating') == 1)>★ — 1 star</option>
                        <option value="2" @selected(old('rating') == 2)>★★ — 2 stars</option>
                        <option value="3" @selected(old('rating') == 3)>★★★ — 3 stars</option>
                        <option value="4" @selected(old('rating') == 4)>★★★★ — 4 stars</option>
                        <option value="5" @selected(old('rating') == 5)>★★★★★ — 5 stars</option>
                    </select>

                    @error('rating')
                        <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">
                        Comment (optional)
                    </label>

                    <textarea
                        name="comment"
                        id="comment"
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                        placeholder="Share your experience..."
                    >{{ old('comment') }}</textarea>

                    @error('comment')
                        <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Submit review
                </button>
            </form>
        </details>
             @else
                 <p class="text-muted mt-4">
                  You have already reviewed this property.
                  </p>
            @endif

        <section class="reviews-grid" aria-label="Guest reviews">
            @forelse ($reviews as $review)
                <article>
                    <div class="review-person">
                        <span class="review-avatar" aria-hidden="true">
                            @if ($review->tenant && $review->tenant->tenantProfile)
                                {{ mb_substr($review->tenant->tenantProfile->first_name, 0, 1) }}
                            @else
                                M
                            @endif
                        </span>

                        <h2 class="review-name">
                            @if ($review->tenant && $review->tenant->tenantProfile)
                                {{ $review->tenant->tenantProfile->first_name }}
                                {{ $review->tenant->tenantProfile->last_name }}
                            @else
                                Makan user
                            @endif
                        </h2>
                    </div>

                    <div class="review-meta">
                        <span class="review-stars">
                            <span class="visually-hidden">
                                {{ $review->rating }} out of 5 stars
                            </span>

                            @for ($star = 1; $star <= $review->rating; $star++)
                                <i class="bi bi-star-fill" aria-hidden="true"></i>
                            @endfor
                        </span>

                        <span class="review-date">
                            · {{ $review->created_at->format('F Y') }}
                        </span>
                    </div>

                    @if (mb_strlen($review->comment ?? '') > 220)
                        <p class="review-comment">{{ mb_substr($review->comment, 0, 220) }}…</p>

                        <details class="review-more">
                            <summary>Show more</summary>

                            <p class="review-comment mt-2">{{ mb_substr($review->comment, 220) }}</p>
                        </details>
                    @else
                        <p class="review-comment">{{ $review->comment }}</p>
                    @endif

                    @if ($review->tenant_id == auth()->id())
                        <div class="review-actions">
                            <a
                                href="{{ route('seeker.reviews.edit', ['review' => $review->id]) }}"
                                class="review-action"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('seeker.reviews.destroy', ['review' => $review->id]) }}"
                                method="POST"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="review-action review-action-delete"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <div class="reviews-empty">
                    <i class="bi bi-chat-square-text fs-2" aria-hidden="true"></i>
                    <h2 class="h5 mt-3">No reviews yet</h2>
                    <p class="text-muted">
                        Be the first to review this property.
                    </p>
                </div>
            @endforelse
        </section>
    </main>
@endsection
