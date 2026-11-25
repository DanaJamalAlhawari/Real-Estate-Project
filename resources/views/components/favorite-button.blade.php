@props(['property', 'saved' => false])

<form
    action="{{ route(
        $saved ? 'seeker.favorites.destroy' : 'seeker.favorites.store',
        ['property' => $property->id]
    ) }}"
    method="POST"
>
    @csrf

    @if ($saved)
        @method('DELETE')
    @endif

    <button
        type="submit"
        class="btn btn-outline-primary"
        aria-label="{{ $saved ? __('Remove from favorites') : __('Add to favorites') }}"
    >
        <i class="{{ $saved ? 'bi bi-heart-fill' : 'bi bi-heart' }}" aria-hidden="true"></i>
    </button>
</form>