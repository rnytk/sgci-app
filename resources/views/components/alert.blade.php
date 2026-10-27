@props(['type' => 'success', 'message'])

@if($message)
    @php
        $styles = [
            'success' => ['bg-green-50 border-green-200 text-green-700', 'text-green-500'],
            'error' => ['bg-red-50 border-red-200 text-red-700', 'text-red-500'],
        ];
        [$box, $icon] = $styles[$type];
    @endphp
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="alert" aria-live="polite"
         class="mb-6 {{ $box }} px-6 py-4 rounded-2xl flex items-center shadow-sm">
        {{-- ícono según $type --}}
        <span class="font-bold">{{ $message }}</span>
    </div>
@endif