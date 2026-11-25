@props(['buttonClass' => 'dropdown-item text-danger'])

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="{{ $buttonClass }}">
        {{ __('Log out') }}
    </button>
</form>
