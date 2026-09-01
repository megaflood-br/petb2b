<x-app-layout>
    <div class="bg-white">

        {{-- Hero Section Comercial --}}
        <div class="bg-gray-950 py-28 px-6 text-center overflow-hidden relative border-b border-gray-900">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(244,63,94,0.08),transparent_50%)]"></div>
            <div class="relative z-10 max-w-4xl mx-auto space-y-6">
                <span class="bg-brand-500/10 text-brand-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-brand-500/20">
                    Anúncios por Performance • CPC & CPM
                </span>
                <h1 class="text-4xl md:text-6xl font-black text-white uppercase tracking-tight leading-none italic">
                    Sua Marca no Coração do <span class="text-brand-500">Mercado Pet</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-400 font-medium max-w-2xl mx-auto">
                    Conecte sua indústria ou distribuidora com milhares de lojistas, veterinários e empreendedores B2B pagando apenas pelos resultados reais.
                </p>
                <div class="pt-4">
                    <a href="#tabela-taxas" class="bg-brand-500 text-white px-10 py-5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-xl shadow-brand-500/20 inline-block">
                        Ver Modelos & Taxas
                    </a>
                </div>
            </div>
        </div>

        {{-- Métricas de Impacto --}}
        <div class="py-16 bg-gray-50/50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <p class="text-4xl font-black text-gray-950 font-mono">50k+</p>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-2">Visitas Mensais</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-brand-500 font-mono">12k+</p>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-2">Lojistas Cadastrados</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-gray-950 font-mono">85%</p>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-2">Decisores de Compra (B2B)</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-brand-500 font-mono">100%</p>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-2">Auditado via Painel</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Como funciona o Sistema de Créditos --}}
        <div class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-16">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">Como funciona nossa plataforma?</h2>
                    <p class="text-gray-500 font-medium text-sm mt-2">Transparência total. Esqueça contratos fixos mensais onde você paga sem saber o retorno.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-gray-100/80 space-y-4">
                        <div class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 font-black text-lg">1</div>
                        <h3 class="font-black text-gray-900 text-lg uppercase tracking-tight">Compre Créditos</h3>
                        <p class="text-gray-500 text-xs font-bold leading-relaxed normal-case">Adicione saldo comercial diretamente pelo seu painel. Seus créditos não expiram e você activa ou pausa campanhas quando quiser.</p>
                    </div>

                    <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-gray-100/80 space-y-4">
                        <div class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 font-black text-lg">2</div>
                        <h3 class="font-black text-gray-900 text-lg uppercase tracking-tight">Configure as Zonas</h3>
                        <p class="text-gray-500 text-xs font-bold leading-relaxed normal-case">Escolha os locais mais estratégicos para o seu público: topo da home, barra lateral do guia ou dentro do canal de notícias técnicas.</p>
                    </div>

                    <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-gray-100/80 space-y-4">
                        <div class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 font-black text-lg">3</div>
                        <h3 class="font-black text-gray-900 text-lg uppercase tracking-tight">Pague por Resultado</h3>
                        <p class="text-gray-500 text-xs font-bold leading-relaxed normal-case">O sistema deduz centavos do seu saldo apenas quando o banner é exibido (View) ou clicado por um cliente interessado (Clique).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Formatos Disponíveis (Zonas de Anúncios) --}}
        <div id="tabela-taxas" class="py-24 bg-gray-50 border-t border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-16">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">Formatos & Taxas de Consumo</h2>
                    <p class="text-gray-500 font-medium text-sm mt-2">Selecione o melhor posicionamento para a sua identidade visual.</p>
                </div>

                {{-- CORRIGIDO: Grid alterada de lg:grid-cols-3 para lg:grid-cols-2 para acomodar 4 formatos de forma simétrica --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8 items-stretch font-bold">

                    {{-- Formato 1: Topo Home --}}
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="p-8 space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-gray-400 text-[8px] uppercase tracking-wider block">Posicionamento Premium</span>
                                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Topo Destaque Home</h3>
                                </div>
                                <span class="bg-gray-900 text-white font-mono text-[9px] px-2.5 py-1 rounded-full uppercase tracking-wider">Leaderboard</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl text-center font-mono text-gray-400 border border-dashed border-gray-200">
                                Medida Recomendada: <span class="text-gray-900 font-black">1200 x 160 px</span>
                            </div>
                            <p class="text-gray-500 text-xs font-medium leading-relaxed normal-case">Exibido de forma rotativa no topo da Home Page principal. Perfeito para lançamentos industriais e fixação de marca nacional.</p>
                        </div>
                        <div class="p-8 bg-gray-50 border-t divide-y divide-gray-200/60 font-mono text-xs">
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por Clique</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_CLICK', 0.50), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por View (Exibição)</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0050), 4, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Formato 2: Sidebar Guia --}}
                    <div class="bg-white rounded-[2.5rem] border-2 border-brand-500 shadow-md overflow-hidden flex flex-col justify-between relative">
                        <div class="p-8 space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-brand-500 text-[8px] uppercase tracking-wider block">Foco em Conversão B2B</span>
                                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Sidebar Guia Comercial</h3>
                                </div>
                                <span class="bg-brand-500 text-white font-mono text-[9px] px-2.5 py-1 rounded-full uppercase tracking-wider">Square</span>
                            </div>
                            <div class="bg-brand-50/40 p-4 rounded-2xl text-center font-mono text-brand-600 border border-dashed border-brand-100">
                                Medida Recomendada: <span class="text-brand-700 font-black">300 x 250 px</span>
                            </div>
                            <p class="text-gray-500 text-xs font-medium leading-relaxed normal-case">Exibido de forma fixa (Sticky) na barra lateral do Guia de Fornecedores. Segue o lojista enquanto ele busca parceiros.</p>
                        </div>
                        <div class="p-8 bg-brand-50/20 border-t border-brand-100 divide-y divide-brand-100/40 font-mono text-xs">
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por Clique</span>
                                <span class="text-brand-700 font-black">R$ {{ number_format((float) env('ADS_COST_PER_CLICK', 0.50), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por View (Exibição)</span>
                                <span class="text-brand-700 font-black">R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0050), 4, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Formato 3: Meio Blog --}}
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="p-8 space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-gray-400 text-[8px] uppercase tracking-wider block">Autoridade Conteúdo</span>
                                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Entre Notícias Blog</h3>
                                </div>
                                <span class="bg-gray-900 text-white font-mono text-[9px] px-2.5 py-1 rounded-full uppercase tracking-wider">Banner</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl text-center font-mono text-gray-400 border border-dashed border-gray-200">
                                Medida Recomendada: <span class="text-gray-900 font-black">1200 x 200 px</span>
                            </div>
                            <p class="text-gray-500 text-xs font-medium leading-relaxed normal-case">Injetado estrategicamente no meio das matérias de tendências do setor pet brasileiro. Impacta leitores altamente qualificados.</p>
                        </div>
                        <div class="p-8 bg-gray-50 border-t divide-y divide-gray-200/60 font-mono text-xs">
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por Clique</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_CLICK', 0.50), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por View (Exibição)</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0050), 4, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- NOVO Formato 4: Banner Fixo Mobile Rodapé --}}
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="p-8 space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-gray-400 text-[8px] uppercase tracking-wider block">Hiper-Engajamento Smartphone</span>
                                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Banner Fixo Mobile</h3>
                                </div>
                                <span class="bg-amber-500 text-white font-mono text-[9px] px-2.5 py-1 rounded-full uppercase tracking-wider">Anchor Sticky</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl text-center font-mono text-gray-400 border border-dashed border-gray-200">
                                Medida Recomendada: <span class="text-gray-900 font-black">320 x 50 px</span>
                            </div>
                            <p class="text-gray-500 text-xs font-medium leading-relaxed normal-case">Fixado permanentemente no rodapé da tela em dispositivos móveis. Acompanha o usuário em todas as páginas do portal com taxa de clique altíssima.</p>
                        </div>
                        <div class="p-8 bg-gray-50 border-t divide-y divide-gray-200/60 font-mono text-xs">
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por Clique</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_CLICK', 0.50), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-gray-400 font-sans font-bold uppercase text-[9px]">Custo por View (Exibição)</span>
                                <span class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0050), 4, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Chamada para Cadastro / Login Rápido --}}
        <div class="py-24 bg-white">
            <div class="max-w-4xl mx-auto px-6 bg-gray-950 rounded-[3rem] p-12 text-center shadow-2xl relative overflow-hidden border border-gray-100 layout-border">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom,rgba(244,63,94,0.05),transparent_70%)]"></div>
                <div class="relative z-10 space-y-6">
                    <h2 class="text-3xl font-black text-white uppercase italic tracking-tight">Comece a anunciar hoje mesmo</h2>
                    <p class="text-gray-400 text-sm max-w-xl mx-auto font-medium">Crie sua conta corporativa, adicione saldo via PIX de forma simulada no ambiente local e coloque seus primeiros banners para rodar instantaneamente.</p>

                    <div class="pt-4 flex flex-col sm:flex-row gap-4 justify-center text-xs font-black uppercase tracking-widest">
                        @auth
                            <a href="{{ route('supplier.ads') }}" class="bg-brand-500 text-white px-8 py-4 rounded-xl hover:bg-brand-600 transition shadow-lg shadow-brand-500/10">
                                Acessar Meu Painel de Ads
                            </a>
                        @else
                            <a href="{{ route('register.select') }}" class="bg-brand-500 text-white px-8 py-4 rounded-xl hover:bg-brand-600 transition shadow-lg shadow-brand-500/10">
                                Criar Conta de Fornecedor
                            </a>
                            <a href="{{ route('login') }}" class="bg-gray-900 border border-gray-800 text-white px-8 py-4 rounded-xl hover:bg-gray-800 transition">
                                Fazer Login no Portal
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
