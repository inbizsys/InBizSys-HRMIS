@props(['href', 'active', 'icon'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 bg-gray-100 text-gray-900 rounded-lg group'
            : 'flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900 group';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div class="mr-3 flex-shrink-0 {{ ($active ?? false) ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-500' }}">
            {{ $icon }}
        </div>
    @endif
    {{ $slot }}
</a>
