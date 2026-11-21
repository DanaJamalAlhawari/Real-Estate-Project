<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;






class ReviewController extends Controller
{

public function index(Property $property)
{


//    another way :     $reviews = Review::where('property_id', $property->id)->get();
    $reviews = $property->reviews()->get();

    $myReview = $property->reviews()
        ->where('tenant_id', Auth::id())
        ->first();

    return view('seeker.reviews', [
        'property' => $property,
        'reviews' => $reviews,
        'myReview' => $myReview,
    ]);
}


public function store(Request $request, Property $property)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:2000',
    ]);

     if ($property->reviews()
        ->where('tenant_id', $request->user()->id)
        ->exists()) {
        return back()->with(
            'reviewError',
            'You have already reviewed this property.'
        );
    }

    $review = new Review();

    $review->property_id = $property->id;
    $review->tenant_id = $request->user()->id;
    $review->rating = $request->input('rating');
    $review->comment = $request->input('comment');


    $review->save();

    

    return back();
}


public function edit(Request $request, Review $review)
{
    if ($review->tenant_id != $request->user()->id) {
        abort(403);
    }

    return view('seeker.edit-review', [
        'review' => $review,
    ]);
}





public function update(Request $request, Review $review)
{
    if ($review->tenant_id != $request->user()->id) {
        abort(403);
    }

    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:2000',
    ]);

    $review->rating = $request->input('rating');
    $review->comment = $request->input('comment');

    $review->save();

    return redirect()->route('seeker.reviews.index', [
        'property' => $review->property_id,
    ]);
}
public function destroy(Request $request, Review $review)
{
    if ($review->tenant_id != $request->user()->id) {
        abort(403);
    }

    $propertyId = $review->property_id;

    $review->delete();

    return redirect()->route('seeker.reviews.index', [
        'property' => $propertyId,
    ]);
}











}