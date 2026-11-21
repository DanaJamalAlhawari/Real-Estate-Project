<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Upload Verification Document</h2>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

@if ($errors->any())
    <p>{{ $errors->first() }}</p>
@endif

<form action="{{ route('properties.verification.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <label for="property_id">Property</label>
    <select id="property_id" name="property_id" required>
        <option value="">Select a property</option>
        @foreach ($properties as $property)
            <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>
                {{ $property->title }}
            </option>
        @endforeach
    </select>

    <label>Ownership Document</label>
    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>

    <button type="submit">Upload Document</button>
</form>
</body>
</html>
