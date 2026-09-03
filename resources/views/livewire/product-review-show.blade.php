<div class="bg-white min-h-screen py-20 px-6">
    <div class="max-w-4xl mx-auto">
        <span class="text-[10px] font-black text-brand-500 uppercase tracking-[0.3em] mb-4 block">
            {{ $review->category }}
        </span>
        <h1 class="text-4xl md:text-6xl font-black text-gray-900 uppercase italic mb-8 leading-tight">
            {{ $review->title }}
        </h1>

        <div class="aspect-video w-full rounded-[3rem] overflow-hidden mb-12 shadow-2xl border border-gray-100 bg-gray-50/50 flex items-center justify-center p-8">
            @if($review->image)
                <img src="{{ asset('storage/'.$review->image) }}" class="max-h-full object-contain" alt="{{ $review->title }}">
            @else
                <div class="text-gray-300 font-black italic text-sm tracking-widest">SEM FOTO</div>
            @endif
        </div>

        <div class="prose prose-brand max-w-none mb-12">
            @if($isHtmlContent)
                <div class="text-gray-600 leading-loose text-lg font-medium normal-case">
                    {!! $review->content !!}
                </div>
            @else
                <p class="text-gray-600 leading-loose text-lg font-medium normal-case whitespace-pre-line">
                    {{ $review->content }}
                </p>
            @endif
        </div>
    </div>
</div>
