@props(['action', 'category' => null, 'fragment' => null, 'placeholder' => 'Title, description, address or city'])

<form method="get" action="{{ $action }}{{ $fragment }}" class="d-flex gap-2 flex-wrap mb-3" role="search">
    @if ($category)
        <input type="hidden" name="category" value="{{ $category }}">
    @endif
    <input type="search" class="form-control w-auto flex-grow-1" name="q" maxlength="255"
        value="{{ request('q') }}" aria-label="{{ __('Search properties') }}"
        placeholder="{{ __($placeholder) }}">
    <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
    @if (request()->filled('q'))
        <a class="btn btn-outline-primary" href="{{ $action }}{{ $category ? '?'.http_build_query(['category' => $category]) : '' }}{{ $fragment }}">{{ __('Clear') }}</a>
    @endif
    @error('q')<div class="text-danger small w-100" role="alert">{{ $message }}</div>@enderror
</form>
