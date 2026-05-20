<x-filament-widgets::widget>
    <x-filament::section heading="Top Pages">
        @if(empty($rows))
            <p class="text-sm text-gray-400 dark:text-gray-500">No data yet.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($rows as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                {{ $row['page'] }}
                            </p>
                            <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full bg-primary-500"
                                     style="width: {{ $row['pct'] }}%"></div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($row['views']) }}
                            </span>
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">
                                {{ $row['pct'] }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
