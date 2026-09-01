<footer class="bg-gray-900 py-16 text-gray-400">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12">
        <div class="md:col-span-2">
            <h2 class="text-white font-black text-2xl mb-4 uppercase">Revista Negócios Pet</h2>
            <p class="max-w-sm font-medium leading-relaxed">A plataforma líder em conexões B2B e informação técnica para o ecossistema pet brasileiro.</p>
        </div>
        <div>
            <h3 class="text-white font-bold mb-4">Módulos</h3>
            <ul class="space-y-3 text-sm font-medium">
                <li><a href="{{ route('suppliers.index') }}" class="hover:text-white transition">Guia de Fornecedores</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">Blog & Análises</a></li>
                <li><a href="{{ route('classifieds.index') }}" class="hover:text-white transition">Classificados</a></li>
            </ul>
        </div>
        <div>
            <h3 class="text-white font-bold mb-4">Links Úteis</h3>
            <ul class="space-y-3 text-sm font-medium">
                <li><a href="{{ route('advertise') }}" class="text-brand-500 font-black hover:text-white transition uppercase text-[10px] tracking-widest">Anuncie Conosco</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white transition">Sobre o Projeto</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contato</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 mt-16 pt-8 border-t border-gray-800 text-center text-[10px] font-bold uppercase tracking-widest">
        © 2026 Revista Negócios Pet. Todos os direitos reservados. Powered by <a class="text-brand-500" href="https://megaflood.com.br">Megaflood</a>
    </div>
</footer>
