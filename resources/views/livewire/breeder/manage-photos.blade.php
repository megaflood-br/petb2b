<div class="space-y-6 text-xs font-bold">
    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px]">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="uploadPhotos" class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
        <div>
            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Adicionar Fotos à Galeria</label>
            <input type="file" wire:model="photos" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
            @error('photos.*') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-brand-500 text-white px-6 py-2.5 rounded-xl uppercase text-[10px] tracking-widest hover:bg-brand-600 transition">
            Enviar Imagens
        </button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
        @foreach($existingPhotos as $photo)
            <div wire:key="photo-{{ $photo->id }}" class="aspect-square bg-gray-100 rounded-xl overflow-hidden relative group border border-gray-100">
                <img src="{{ asset('storage/' . $photo->image_path) }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                    <button wire:click="deletePhoto({{ $photo->id }})" class="bg-red-600 text-white px-3 py-1.5 rounded-lg uppercase text-[9px] tracking-widest font-black hover:bg-red-700 transition">
                        Excluir
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
