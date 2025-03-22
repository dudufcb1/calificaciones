<div>
@if($showWarning)
<div class="my-4">
    <div class="rounded-md shadow-md p-4 {{ $warningType === 'warning' ? 'bg-yellow-50 border border-yellow-200' : ($warningType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200') }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($warningType === 'warning')
                    <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @elseif($warningType === 'error')
                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <div class="ml-3 flex-1 md:flex md:justify-between">
                <p class="{{ $warningType === 'warning' ? 'text-yellow-700' : ($warningType === 'error' ? 'text-red-700' : 'text-blue-700') }} text-sm">
                    {{ $warningMessage }}
                </p>
                @if($actionLink && $actionText)
                <div class="mt-3 flex md:mt-0 md:ml-6">
                    <a href="{{ $actionLink }}" class="{{ $warningType === 'warning' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : ($warningType === 'error' ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-blue-100 text-blue-800 hover:bg-blue-200') }} px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        {{ $actionText }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
</div>
