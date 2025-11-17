@if(session('success'))
<div class="admin-alert-success mb-6">
    <div class="flex items-center">
        <i class="bi bi-check-circle mr-2"></i>
        <span>{{ session('success') }}</span>
    </div>
    <button type="button" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="admin-alert-error mb-6">
    <div class="flex items-center">
        <i class="bi bi-exclamation-circle mr-2"></i>
        <span>{{ session('error') }}</span>
    </div>
    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
@endif

@if($errors->any())
<div class="admin-alert-error mb-6">
    <div>
        <div class="flex items-center">
            <i class="bi bi-exclamation-circle mr-2"></i>
            <span>There were some errors with your request:</span>
        </div>
        <ul class="list-disc list-inside mt-2 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
@endif