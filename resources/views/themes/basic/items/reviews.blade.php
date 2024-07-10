@extends('themes.basic.items.layout')
@section('title', $item->name)
@section('breadcrumbs', Breadcrumbs::render('items.reviews', $item))
@section('og_image', $item->getPreviewImageLink())
@section('description', shorterText(strip_tags($item->description), 155))
@section('keywords', $item->tags)
@section('content')
    <div class="border-top pt-3">
        <div class="row row-cols-auto align-items-center justify-content-between g-3 mb-3">
            <div class="col">
                <div class="d-flex align-items-center">
                    @include('themes.basic.partials.rating-stars', [
                        'stars' => $item->avg_reviews,
                        'ratings_classes' => 'ratings-lg',
                    ])
                    <span class="d-flex ms-2">
                        {{ translate(':count out of 5 stars', ['count' => $item->avg_reviews]) }}
                    </span>
                </div>
            </div>
            @if (authUser() && authUser()->hasPurchasedItem($item->id))
                <div class="col">
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#reviewsForm"
                        aria-expanded="false" aria-controls="collapseExample">
                        {{ translate('Write a review') }}
                    </button>
                </div>
            @endif
        </div>
        @if (authUser() && authUser()->hasPurchasedItem($item->id))
            <div class="collapse mt-3 mb-4" id="reviewsForm">
                <div class="card-v border p-4">
                    <form action="{{ $item->getReviewsLink() }}" method="POST">
                        @csrf
                        <h5 class="mb-3">{{ translate('Write a review') }}</h5>
                        <div class="row row-cols-auto flex-nowrap g-2 ratings ratings-lg ratings-selective mb-3">
                            <div class="col rating">
                                <i class="fa fa-star fa-lg"></i>
                                <input type="radio" name="review_stars" value="1">
                            </div>
                            <div class="col rating">
                                <i class="fa fa-star fa-lg"></i>
                                <input type="radio" name="review_stars" value="2">
                            </div>
                            <div class="col rating">
                                <i class="fa fa-star fa-lg"></i>
                                <input type="radio" name="review_stars" value="3">
                            </div>
                            <div class="col rating">
                                <i class="fa fa-star fa-lg"></i>
                                <input type="radio" name="review_stars" value="4">
                            </div>
                            <div class="col rating">
                                <i class="fa fa-star fa-lg"></i>
                                <input type="radio" name="review_stars" value="5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Subject') }}</label>
                            <input type="text" class="form-control form-control-md mb-3" name="subject"
                                placeholder="{{ translate('Subject') }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">{{ translate('Review') }}</label>
                            <textarea class="form-control mb-3" name="review" rows="6" placeholder="{{ translate('Your Review') }}"></textarea>
                        </div>
                        <button class="btn btn-primary btn-md">{{ translate('Publish') }}</button>
                    </form>
                </div>
            </div>
        @endif
        <div class="reviews">
            @if ($reviews->count() > 0)
                <div class="row row-cols-1 g-4">
                    @foreach ($reviews as $review)
                        @include('themes.basic.partials.item-review', [
                            'item' => $item,
                            'review' => $review,
                        ])
                    @endforeach
                </div>
            @else
                <div class="card-v card-bg text-center">
                    <i class="far fa-star fa-lg"></i>
                    <p class="mb-0 mt-3">{{ translate('This item has no reviews') }}</p>
                </div>
            @endif
        </div>
        {{ $reviews->links() }}
    </div>
@endsection
