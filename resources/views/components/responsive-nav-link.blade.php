@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block pl-3 pr-4 py-2 border-l-4 border-primary-400 text-base font-medium text-primary-700 bg-primary-50 focus:outline-none focus:text-primary-800 focus:bg-primary-100 focus:border-primary-700 transition duration-150 ease-in-out dark:bg-gray-900'
            : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out dark:bg-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
