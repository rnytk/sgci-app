<nav class="flex items-center justify-end gap-3">
    @auth
        <a
            href="{{ url('/dashboard') }}"
            class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]"
        >
            Dashboard
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]"
        >
            Log in
        </a>

        @if (Route::has('register'))
            <a
                href="{{ route('register') }}"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-[#102d52] transition hover:bg-[#f2f9e9]"
            >
                Register
            </a>
        @endif
    @endauth
</nav>
