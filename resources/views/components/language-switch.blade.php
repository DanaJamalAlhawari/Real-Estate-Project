@php($switchToArabic = ! app()->isLocale('ar'))
<a class="makan-language btn btn-outline-primary btn-sm"
   href="{{ request()->fullUrlWithQuery(['lang' => $switchToArabic ? 'ar' : 'en']) }}"
   hreflang="{{ $switchToArabic ? 'ar' : 'en' }}"
   aria-label="{{ __($switchToArabic ? 'Change language to Arabic' : 'Change language to English') }}">
    <i class="bi bi-translate" aria-hidden="true"></i>
    <span lang="{{ $switchToArabic ? 'ar' : 'en' }}">{{ $switchToArabic ? 'العربية' : 'English' }}</span>
</a>
