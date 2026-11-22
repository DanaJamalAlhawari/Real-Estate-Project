<footer class="site-footer makan-footer">
    <div class="container">
        <div class="makan-footer__main">
            <div>
                <a class="brand" href="{{ route('home') }}">
                    <img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span>
                </a>
                <p>Find and manage rental homes in Jordan.</p>
            </div>
            <nav aria-label="Footer">
                <a href="{{ route('home') }}#how">How it works</a>
                @switch(auth()->user()?->role)
                    @case('tenant')
                        <a href="{{ route('seeker.search') }}">Discover</a>
                        <a href="{{ route('seeker.bookings') }}">My bookings</a>
                        @break
                    @case('agent')
                        <a href="{{ route('owner.properties') }}">My properties</a>
                        <a href="{{ route('owner.requests') }}">Booking requests</a>
                        @break
                    @case('admin')
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <a href="{{ route('admin.verifications') }}">Verifications</a>
                        @break
                    @default
                        <a href="{{ route('home') }}#owners">For owners</a>
                        <a href="{{ route('auth.login') }}">Sign in</a>
                @endswitch
            </nav>
        </div>
        <div class="footer-bottom">© {{ now()->year }} Makan</div>
    </div>
</footer>
