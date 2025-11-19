@php
    $colors = $get('custom_colors') ?? [
        'app_bg'         => '#0E0E10',
        'surface'        => '#111418',
        'accent'         => '#4A9FFF',
        'accent_hover'   => '#2D7CE8',
        'text_primary'   => '#E7EAF0',
        'text_secondary' => '#A8ACB3',
        'text_muted'     => '#6F737A',
    ];
@endphp

<div
    class="rounded-xl border border-gray-800/70 overflow-hidden mt-4"
    style="background: {{ $colors['app_bg'] }}; color: {{ $colors['text_primary'] }};"
>
    <div class="px-4 py-3 flex items-center justify-between"
         style="background: {{ $colors['surface'] }};">
        <div class="text-xs uppercase tracking-wide"
             style="color: {{ $colors['text_secondary'] }};">
            Theme preview
        </div>
        <div class="flex gap-2">
            <span class="inline-flex items-center px-3 py-1 text-xs rounded-full"
                  style="background: {{ $colors['accent'] }}; color: #ffffff;">
                Accent
            </span>
            <span class="inline-flex items-center px-3 py-1 text-xs rounded-full"
                  style="border: 1px solid {{ $colors['text_muted'] }}; color: {{ $colors['text_secondary'] }};">
                Surface
            </span>
        </div>
    </div>
    <div class="px-4 py-5 space-y-2 text-xs">
        <div style="color: {{ $colors['text_primary'] }};">
            This is primary text.
        </div>
        <div style="color: {{ $colors['text_secondary'] }};">
            This is secondary text for descriptions.
        </div>
        <div style="color: {{ $colors['text_muted'] }};">
            This is muted text for meta / labels.
        </div>
        <button
            type="button"
            class="mt-3 inline-flex items-center px-4 py-2 text-xs font-medium rounded-lg"
            style="background: {{ $colors['accent'] }}; color: #ffffff;"
        >
            Primary button
        </button>
    </div>
</div>
