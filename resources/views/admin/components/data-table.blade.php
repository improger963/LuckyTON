<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300 overflow-hidden">
    @if(isset($header))
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $header }}</h2>
            @if(isset($actions))
            <div class="mt-4 md:mt-0">
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    {{ $thead }}
                </tr>
            </thead>
            <tbody>
                {{ $tbody }}
            </tbody>
        </table>
    </div>
    
    @if(isset($footer))
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        {{ $footer }}
    </div>
    @endif
</div>