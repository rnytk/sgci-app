@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 bg-white text-slate-800 focus:border-[#9cc51d] focus:ring-[#9cc51d] rounded-xl shadow-sm']) }}>
