@extends('layouts.app')
@section('title', __('Makan — Upload verification document'))

@section('content')
@include('partials.admin-topbar')
<main class="app-page container">
    <h2>{{ __('Upload Verification Document') }}</h2>

@if (session('success'))
    <p>{{ __(session('success')) }}</p>
@endif

@if ($errors->any())
    <p>{{ $errors->first() }}</p>
@endif

<form action="{{ route('properties.verification.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <label for="property_id">{{ __('Property') }}</label>
    <select id="property_id" name="property_id" required>
        <option value="">{{ __('Select a property') }}</option>
        @foreach ($properties as $property)
            <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>
                {{ $property->localized('title') }}
            </option>
        @endforeach
    </select>

    <label>{{ __('Ownership Document') }}</label>
    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>

    <button type="submit">{{ __('Upload Document') }}</button>
</form>
</main>
@endsection
