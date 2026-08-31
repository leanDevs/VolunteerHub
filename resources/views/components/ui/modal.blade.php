@props(['id', 'title'])

<div id="{{ $id }}" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[999] flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 transform scale-100 transition-all duration-300 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4 sticky top-0 bg-white z-10">
            <h3 class="font-black text-slate-800 text-sm sm:text-base flex items-center gap-2">
                {{ $title }}
            </h3>
            <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
