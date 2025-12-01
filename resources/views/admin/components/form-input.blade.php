@props(['label' => null, 'name', 'type' => 'text', 'value' => '', 'placeholder' => '', 'required' => false, 'error' => false])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    @if($type === 'textarea')
        <textarea 
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm']) }}
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm']) }}
        >
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm']) }}
        >
    @endif
    
    @if($error && $errors->has($name))
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $errors->first($name) }}</p>
    @endif
</div>