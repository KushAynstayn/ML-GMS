<div id="errorModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full transform transition-all border-t-4 border-red-600 mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-100 p-2 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Validation Error</h3>
        </div>
        
        <p class="text-sm text-gray-600 mb-3">The following fields are required before you can verify this record:</p>
        
        <ul id="errorList" class="text-xs text-red-600 list-disc list-inside mb-6 space-y-1 font-semibold max-h-40 overflow-y-auto bg-red-50 p-4 rounded-lg border border-red-100">
            </ul>
        
        <button onclick="closeErrorModal()" class="w-full bg-red-600 text-white py-2.5 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-200">
            Close and Fix
        </button>
    </div>
</div>