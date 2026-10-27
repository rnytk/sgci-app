<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 font-medium text-kato-gray hover:text-kato-blue hover:bg-gray-50 focus:outline-none focus:text-kato-blue focus:bg-gray-50 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</a>