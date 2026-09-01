<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turn.js/3/turn.min.js"></script>

    <div class="bg-[#111] min-h-screen py-6 md:py-10 overflow-hidden flex flex-col items-center justify-center relative">

        {{-- Topo com Informações --}}
        <div class="w-full max-w-6xl px-6 flex justify-between items-center mb-6">
            <div class="text-white">
                <h1 class="text-xl md:text-2xl font-black uppercase italic tracking-tighter">{{ $magazine->title }}</h1>
                <p class="text-brand-400 text-[9px] md:text-[10px] font-black uppercase tracking-widest">Edição {{ $magazine->issue_period }}</p>
            </div>
            <a href="{{ route('home') }}" class="text-white/40 hover:text-white text-[10px] font-black uppercase tracking-widest transition">
                ✕ <span class="hidden md:inline">Fechar Leitor</span>
            </a>
        </div>

        {{-- CORRIGIDO/ADICIONADO: Central de Carregamento Dinâmico (Sumiço Suave via CSS) --}}
        <div id="magazine-loader" class="absolute inset-0 z-50 bg-[#111] flex flex-col items-center justify-center p-6 transition-all duration-700">
            <div class="w-full max-w-md text-center space-y-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-brand-500 border-t-transparent mb-2"></div>
                <p id="loader-text" class="text-white text-xs font-black uppercase tracking-widest animate-pulse">Iniciando download da revista...</p>

                {{-- Trilha da Barra --}}
                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden p-[2px] border border-white/5">
                    {{-- Progresso Real --}}
                    <div id="loader-bar" class="w-0 h-full bg-gradient-to-r from-brand-500 to-amber-500 rounded-full transition-all duration-300 ease-out shadow-lg shadow-brand-500/50"></div>
                </div>

                <span id="loader-percentage" class="text-[10px] font-mono font-bold text-gray-500 tracking-wider">0%</span>
            </div>
        </div>

        {{-- Espaço do Flipbook --}}
        <div id="canvas-container" class="relative flex justify-center items-center opacity-0 transition-opacity duration-1000 w-full overflow-hidden">
            <div id="flipbook" class="shadow-2xl mx-auto"></div>
        </div>

        {{-- Controle de Navegação --}}
        <div class="mt-8 md:mt-12 flex gap-4 md:gap-8 items-center z-40">
            <button id="prev" class="bg-white/10 hover:bg-white/20 text-white p-3 md:p-4 rounded-full transition active:scale-90">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>

            <button id="next" class="bg-white/10 hover:bg-white/20 text-white p-3 md:p-4 rounded-full transition active:scale-90">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <p class="mt-4 text-white/20 text-[8px] font-bold uppercase tracking-[0.3em] md:hidden">Deslize para folhear</p>
    </div>

    <style>
        #flipbook { transition: transform 0.3s ease; }
        .page { background-color: white; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
        canvas { width: 100%; height: 100%; display: block; }

        @media (max-width: 768px) {
            #flipbook {
                width: 90vw !important;
                height: 65vh !important;
            }
            .page {
                width: 90vw !important;
                height: 65vh !important;
            }
        }
    </style>

    <script>
        const pdfUrl = "{{ asset('storage/' . $magazine->pdf_path) }}";
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null;
        const isMobile = window.innerWidth < 768;

        // Atualiza a interface gráfica da barra de progresso
        function updateLoader(percentage, text) {
            $('#loader-bar').css('w', percentage + '%'); // Ajustado para sintaxe padrão sem shortcode inline malformado
            $('#loader-bar').width(percentage + '%');
            $('#loader-percentage').text(percentage + '%');
            if (text) $('#loader-text').text(text);
        }

        async function initFlipbook() {
            try {
                // CORRIGIDO: Captura o progresso de download real do arquivo PDF via Stream de dados
                const loadingTask = pdfjsLib.getDocument(pdfUrl);

                loadingTask.onProgress = function (progress) {
                    if (progress.total > 0) {
                        const percent = Math.round((progress.loaded / progress.total) * 100);
                        // Limita até 90% para deixar os 10% finais dedicados à renderização do canvas
                        const adjustedPercent = Math.min(Math.round(percent * 0.9), 90);
                        updateLoader(adjustedPercent, "Baixando conteúdo da revista...");
                    }
                };

                pdfDoc = await loadingTask.promise;
                const flipbook = $('#flipbook');

                // Renderiza cada página do PDF como um Canvas
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    // Atualiza o texto informando a página atual que o processador do PC está gerando
                    const renderPercent = 90 + Math.round((i / pdfDoc.numPages) * 10);
                    updateLoader(renderPercent, `Processando página ${i} de ${pdfDoc.numPages}...`);

                    const page = await pdfDoc.getPage(i);
                    const viewport = page.getViewport({ scale: isMobile ? 1.5 : 2 });

                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({ canvasContext: context, viewport: viewport }).promise;

                    const pageDiv = $('<div class="page"></div>').append(canvas);
                    flipbook.append(pageDiv);
                }

                // Configurações dinâmicas do Turn.js
                const fbWidth = isMobile ? window.innerWidth * 0.9 : 1000;
                const fbHeight = isMobile ? window.innerHeight * 0.65 : 700;

                flipbook.turn({
                    width: fbWidth,
                    height: fbHeight,
                    autoCenter: true,
                    display: isMobile ? 'single' : 'double',
                    acceleration: true,
                    gradients: true,
                    elevation: 50,
                    duration: 1000
                });

                // Conclusão total: joga a barra em 100% e some com o painel de loading com efeito fade
                updateLoader(100, "Revista pronta!");

                setTimeout(() => {
                    $('#magazine-loader').addClass('opacity-0 pointer-events-none');
                    $('#canvas-container').removeClass('opacity-0');
                }, 500);

            } catch (error) {
                console.error("Erro ao carregar revista:", error);
                updateLoader(0, "Falha no carregamento.");
                alert("Erro ao carregar o arquivo da revista.");
            }
        }

        $(window).ready(() => {
            initFlipbook();

            // Navegação por teclado
            $(window).bind('keydown', (e) => {
                if (e.keyCode == 37) $('#flipbook').turn('previous');
                else if (e.keyCode == 39) $('#flipbook').turn('next');
            });

            $('#prev').click(() => $('#flipbook').turn('previous'));
            $('#next').click(() => $('#flipbook').turn('next'));
        });
    </script>
</x-app-layout>
