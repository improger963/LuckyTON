<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300 {{ $class ?? '' }}">
    @if(isset($header))
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                @if(isset($title))
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if(isset($subtitle))
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
            <div class="mt-4 md:mt-0">
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <div class="p-6 {{ isset($header) ? '' : 'pt-6' }} {{ isset($footer) ? '' : 'pb-6' }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        {{ $footer }}
    </div>
    @endif
</div>