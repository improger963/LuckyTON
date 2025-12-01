<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-1 {{ $class ?? '' }}">
    <div class="flex items-center">
        <div class="p-3 rounded-lg {{ $iconBgColor ?? 'bg-blue-100' }} {{ $iconColor ?? 'text-blue-600' }} dark:bg-opacity-20">
            <i class="{{ $icon ?? 'bi bi-question-circle' }} text-xl"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $value ?? '0' }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $label ?? 'Label' }}</p>
        </div>
    </div>
    @if(isset($trend))
    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
        <div class="flex items-center text-sm">
            @if(($trendDirection ?? 'up') === 'up')
                <i class="bi bi-arrow-up text-green-500 mr-1"></i>
                <span class="text-green-500 font-medium">{{ $trend }}</span>
            @else
                <i class="bi bi-arrow-down text-red-500 mr-1"></i>
                <span class="text-red-500 font-medium">{{ $trend }}</span>
            @endif
            @if(isset($trendLabel))
                <span class="text-gray-500 dark:text-gray-400 ml-2">{{ $trendLabel }}</span>
            @endif
        </div>
    </div>
    @endif
</div>