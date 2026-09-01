<x-app-layout>
    @php
        $apply = trim($job->how_to_apply);
        if (filter_var($apply, FILTER_VALIDATE_EMAIL)) {
            $applyHref = 'mailto:' . $apply;
        } elseif (\Illuminate\Support\Str::startsWith($apply, ['http://', 'https://'])) {
            $applyHref = $apply;
        } else {
            $applyHref = null;
        }
    @endphp

    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-3xl mx-auto px-6">

            <a href="{{ route('jobs.index') }}" class="text-[10px] font-black uppercase tracking-widest text-brand-500 hover:underline">← Voltar às vagas</a>

            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 md:p-10 shadow-sm mt-4">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-[9px] font-black uppercase tracking-wider bg-brand-50 text-brand-600 px-3 py-1.5 rounded-full">{{ $job->type }}</span>
                    @if($job->salary)
                        <span class="text-[9px] font-black uppercase tracking-wider bg-green-50 text-green-600 px-3 py-1.5 rounded-full">{{ $job->salary }}</span>
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl font-black text-gray-900 uppercase italic tracking-tight">{{ $job->title }}</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">
                    @if($job->supplier)
                        <a href="{{ route('suppliers.show', $job->supplier->slug) }}" class="text-brand-500 hover:underline font-bold">{{ $job->supplier->name }}</a> ·
                    @endif
                    {{ $job->city ?: 'Local a combinar' }}{{ $job->state ? '/' . $job->state : '' }}
                </p>

                <div class="prose prose-sm max-w-none mt-8 text-gray-700 whitespace-pre-line leading-relaxed">{{ $job->description }}</div>

                <div class="mt-10 pt-6 border-t border-gray-100">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-2">Como se candidatar</p>
                    @if($applyHref)
                        <a href="{{ $applyHref }}" target="_blank" rel="noopener" class="inline-block bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition shadow-md shadow-brand-500/10">
                            Candidatar-se
                        </a>
                    @else
                        <p class="text-sm font-bold text-gray-900 bg-gray-50 border border-gray-100 rounded-xl p-4">{{ $apply }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
