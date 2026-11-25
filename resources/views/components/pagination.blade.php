@props(['items'])

@if ($items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && ($items->hasPages() || ! $items->onFirstPage()))
    <div class="makan-pagination mt-3" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
        {{ $items->onEachSide(1)->links('partials.pagination') }}
    </div>
@endif
