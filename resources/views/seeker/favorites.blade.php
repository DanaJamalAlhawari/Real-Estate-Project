<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Favorites</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap.min.css') }}">
</head>
<body class="bg-light">

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">My Favorites</h1>
                <p class="text-muted mb-0">Your saved properties in one place.</p>
            </div>

            <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary">
                Browse properties
            </a>
        </div>

        <div class="row g-4">
            @forelse ($favorites as $property)
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h2 class="h5">{{ $property->title }}</h2>

                            <p class="text-muted">
                                {{ $property->area ?: 'Area not specified' }}
                            </p>

                            <p class="fw-bold text-primary">
                                {{ $property->price !== null
                                    ? number_format($property->price, 2) . ' JOD'
                                    : 'Contact for price' }}
                            </p>

                            <form
                                action="{{ route('seeker.favorites.destroy', ['property' => $property->id]) }}"
                                method="POST"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-outline-danger w-100">
                                    Remove from favorites
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center p-5">
                        <h2 class="h4">No favorites yet</h2>

                        <p class="text-muted">
                            Save properties you like to find them here later.
                        </p>

                        <a href="{{ route('seeker.search') }}" class="btn btn-primary align-self-center">
                            Explore properties
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>