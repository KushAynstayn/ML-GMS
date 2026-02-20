<div id="statusModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-sm w-full text-center transform transition-all mx-4">
        
        <div id="modalLoading">
            <div class="relative mx-auto w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-red-600 border-t-transparent animate-spin"></div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Processing...</h3>
            <p class="text-sm text-gray-500">Generating loan record and amortization schedule. Please wait.</p>
        </div>
        
        <div id="modalSuccess" class="hidden">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Saved!</h3>
            <p class="text-gray-500 mb-6">The loan record has been successfully stored in the database.</p>
            
            <div class="flex flex-col gap-2">
                <button onclick="window.location.reload()" class="w-full bg-gray-900 text-white py-2.5 rounded-lg font-bold hover:bg-gray-800 transition-colors">
                    Back to Dashboard
                </button>
            </div>
        </div>
    </div>
</div>