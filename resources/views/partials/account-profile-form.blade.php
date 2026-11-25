<section class="panel">
    @if (session('success'))
        <p class="alert alert-success" role="status">{{ __(session('success')) }}</p>
    @endif
    @if ($errors->any())
        <p class="alert alert-danger" role="alert">{{ $errors->first() }}</p>
    @endif
    <form method="post" action="{{ route($updateRoute) }}" class="row g-3">
        @csrf
        @method('PUT')
        @if ($user->role !== 'admin')
            @php
                $fields = ['first_name' => 'First name', 'last_name' => 'Last name', 'phone' => 'Phone'];
                $fields += $user->role === 'agent'
                    ? ['agency_name' => 'Agency name', 'license_number' => 'License number']
                    : ['emergency_contact' => 'Emergency contact', 'student_id' => 'Student ID'];
            @endphp
            @foreach ($fields as $field => $label)
                <div class="col-md-6">
                    <label class="form-label" for="{{ $field }}">{{ __($label) }}</label>
                    <input class="form-control" id="{{ $field }}" name="{{ $field }}"
                           value="{{ old($field, $profile?->{$field}) }}"
                           @required(in_array($field, ['first_name', 'last_name', 'phone'], true))>
                </div>
            @endforeach
        @endif
        <div class="col-md-6">
            <label class="form-label" for="email">{{ __('Email') }}</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="col-12"><h2 class="h6 mb-0">{{ __('Change password (optional)') }}</h2></div>
        @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
            <div class="col-md-4">
                <label class="form-label" for="{{ $field }}">{{ __($label) }}</label>
                <input type="password" class="form-control" id="{{ $field }}" name="{{ $field }}"
                       autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}">
            </div>
        @endforeach
        <div class="col-12"><button type="submit" class="btn btn-accent">{{ __('Save profile') }}</button></div>
    </form>
</section>
