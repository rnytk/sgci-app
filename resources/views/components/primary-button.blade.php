<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#102d52] border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#193f6f] focus:bg-[#193f6f] active:bg-[#0b203b] focus:outline-none focus:ring-2 focus:ring-[#9cc51d] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
