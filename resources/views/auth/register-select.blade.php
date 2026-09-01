<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-20 flex flex-col justify-center items-center px-6">

        {{-- Cabeçalho da Escolha --}}
        <div class="text-center max-w-xl mb-16">
            <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.3em] mb-2">Seja bem-vindo à rede</p>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 uppercase italic tracking-tight">
                Escolha o seu perfil no <span class="text-brand-500">Portal</span>
            </h1>
            <p class="mt-3 text-sm text-gray-500 font-medium">
                Selecione como a sua marca ou profissional vai interagir com a maior plataforma do mercado pet regional.
            </p>
        </div>

        {{-- Grid de Opções --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full">

            {{-- Opção 1: Lojista / Leitor Profissional --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    {{-- ÍCONE SVG DE MALETA/LOJA INTEGRADO --}}
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 mb-6 group-hover:bg-brand-500 group-hover:text-white transition-colors duration-300 p-3">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-2">Lojista / Médico Veterinário</h3>
                    <p class="text-[9px] font-black text-brand-500 uppercase tracking-widest mb-4">Perfil Leitor Profissional</p>

                    <ul class="space-y-2.5 text-xs text-gray-500 font-medium border-t border-gray-50 pt-4">
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Salvar e favoritar matérias técnicas
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Negociar nos classificados do setor
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Acesso a benefícios e compras coletivas
                        </li>
                    </ul>
                </div>
                <div class="mt-8">
                    <a href="{{ route('register', ['role' => 'reader']) }}" class="w-full block text-center bg-gray-900 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-brand-500 transition shadow-sm">
                        Criar Perfil Lojista
                    </a>
                </div>
            </div>

            {{-- Opção 2: Fornecedor / Indústria --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    {{-- ÍCONE SVG DE PRÉDIO/INDÚSTRIA INTEGRADO --}}
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 mb-6 group-hover:bg-brand-500 group-hover:text-white transition-colors duration-300 p-3">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-2">Indústria / Fornecedor B2B</h3>
                    <p class="text-[9px] font-black text-brand-500 uppercase tracking-widest mb-4">Perfil Corporativo</p>

                    <ul class="space-y-2.5 text-xs text-gray-500 font-medium border-t border-gray-50 pt-4">
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Cadastrar sua empresa no Guia oficial
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Publicar lançamentos de produtos B2B
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Destacar anúncios e posições de busca
                        </li>
                    </ul>
                </div>
                <div class="mt-8">
                    <a href="{{ route('register', ['role' => 'supplier']) }}" class="w-full block text-center bg-brand-500 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100">
                        Criar Perfil Fornecedor
                    </a>
                </div>
            </div>

            {{-- Opção 3: Canil / Criador --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    {{-- ÍCONE SVG DE ESCUDO/SEGURANÇA (REPRESENTANDO PROCEDÊNCIA DO CANIL) --}}
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 mb-6 group-hover:bg-brand-500 group-hover:text-white transition-colors duration-300 p-3">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-2">Canil Registrado / Criador</h3>
                    <p class="text-[9px] font-black text-brand-500 uppercase tracking-widest mb-4">Perfil Especializado</p>

                    <ul class="space-y-2.5 text-xs text-gray-500 font-medium border-t border-gray-50 pt-4">
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Página exclusiva do canil com afixo
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Galeria de fotos de matrizes e instalações
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-black">✓</span> Selo de Criador Verificado pela Revista
                        </li>
                    </ul>
                </div>
                <div class="mt-8">
                    <a href="{{ route('register', ['role' => 'breeder']) }}" class="w-full block text-center bg-gray-900 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-brand-500 transition shadow-sm">
                        Criar Perfil Canil
                    </a>
                </div>
            </div>

        </div>

        {{-- Link de voltar --}}
        <p class="mt-12 text-xs font-bold text-gray-400 uppercase tracking-widest">
            Já possui uma conta? <a href="{{ route('login') }}" class="text-brand-500 hover:underline">Fazer Login</a>
        </p>

    </div>
</x-app-layout>
