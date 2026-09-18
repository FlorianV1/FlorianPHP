@props([
    'icon',
    'dark' => false,
])

@php
    // The panel's own name, so the three panels stay tellable apart while
    // sharing one company mark. Filament renders this inside a container it
    // has already sized, hence h-full on the image.
    $brandName = filament()->getBrandName();
@endphp

<span class="flex h-full items-center gap-2">
    {{-- Decorative: the brand name beside it is the accessible text. --}}
    <img
        src="{{ asset('images/brand/' . $icon) }}"
        alt=""
        aria-hidden="true"
        class="h-full w-auto shrink-0"
    >

    <span @class([
        'text-lg font-bold tracking-tight',
        'text-gray-950' => ! $dark,
        'text-white' => $dark,
    ])>{{ $brandName }}</span>
</span>
