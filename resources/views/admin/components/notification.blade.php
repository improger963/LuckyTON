@if(session('success'))
    <div class="rounded-lg bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800 p-4 mb-6 transition-all duration-300 fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-check-circle text-green-500 dark:text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                    Success!
                </h3>
                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                    <p>{{ session('success') }}</p>
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex rounded-md bg-green-50 text-green-500 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 p-1.5">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800 p-4 mb-6 transition-all duration-300 fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-circle text-red-500 dark:text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                    Error!
                </h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <p>{{ session('error') }}</p>
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex rounded-md bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 p-1.5">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800 p-4 mb-6 transition-all duration-300 fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-circle text-red-500 dark:text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                    Validation Errors
                </h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <ul role="list" class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex rounded-md bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 p-1.5">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif