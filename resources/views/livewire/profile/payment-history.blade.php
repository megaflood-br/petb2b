<section @if($openCharges->contains(fn ($charge) => $charge->isAwaitingPayment())) wire:poll.6s @endif>
    <header>
        <h2 class="text-lg font-black text-gray-900 uppercase italic tracking-tight">
            Pagamentos
        </h2>
        <p class="mt-1 text-sm text-gray-500 font-medium">
            PIX gerados para recarga e histórico dos pagamentos já efetuados.
        </p>
    </header>

    @if($supplier)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                Saldo de créditos
                <span class="ml-2 font-mono text-sm font-black text-gray-900 normal-case">R$ {{ number_format((float) $supplier->credit_balance, 2, ',', '.') }}</span>
            </p>
            @can('access-supplier')
                <a href="{{ route('supplier.ads') }}" class="text-[10px] font-black uppercase tracking-widest text-brand-500 hover:text-brand-600">
                    Gerar novo PIX →
                </a>
            @endcan
        </div>
    @endif

    <div class="mt-6 space-y-8">
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">PIX gerados</h3>

            @forelse($openCharges as $charge)
                <div class="mb-3 border border-gray-100 rounded-2xl p-4 bg-gray-50/60 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-black text-gray-900 font-mono">R$ {{ number_format((float) $charge->amount, 2, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400 font-medium">Gerado em {{ $charge->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase {{ $charge->isExpired() ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                            {{ $charge->statusLabel() }}
                        </span>
                    </div>

                    @if($charge->isAwaitingPayment() && $charge->pix_payload)
                        <div class="bg-white rounded-xl p-3 space-y-2">
                            @if($charge->pix_encoded_image)
                                <img src="data:image/png;base64,{{ $charge->pix_encoded_image }}" alt="QR Code PIX" class="w-32 h-32 mx-auto rounded-lg border border-gray-100">
                            @endif
                            <p class="text-[9px] font-black uppercase text-gray-400 tracking-wider text-center">Copia e cola</p>
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-2 break-all font-mono text-[9px] text-gray-700 select-all">{{ $charge->pix_payload }}</div>
                            @if($charge->pix_expiration)
                                <p class="text-[8px] text-gray-400 text-center">Válido até {{ $charge->pix_expiration->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 font-medium">Nenhum PIX em aberto. {{ $supplier ? 'Gere uma recarga no painel de anúncios.' : 'Recargas PIX ficam disponíveis no painel do fornecedor.' }}</p>
            @endforelse
        </div>

        <div>
            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">Pagamentos efetuados</h3>

            @forelse($paidCharges as $charge)
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3 border border-gray-100 rounded-2xl p-4">
                    <div>
                        <p class="text-sm font-black text-gray-900 font-mono">R$ {{ number_format((float) $charge->amount, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">Pago em {{ $charge->credited_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase bg-green-50 text-green-600 border border-green-100">
                        {{ $charge->statusLabel() }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 font-medium">Nenhum pagamento efetuado ainda.</p>
            @endforelse
        </div>
    </div>
</section>
