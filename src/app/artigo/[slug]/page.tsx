import Image from 'next/image';
import { notFound } from 'next/navigation';
import { createClient } from '@/utils/supabase/server';
import Link from 'next/link';

// Em Next.js 15, os parâmetros da URL são Assíncronos (Promises)
export default async function ArtigoPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const supabase = await createClient();

  // Busca o artigo específico pelo slug
  const { data: artigo } = await supabase
    .from('artigos')
    .select('*')
    .eq('slug', slug)
    .single();

  // Se não encontrar o artigo (slug inválido), mostra página 404
  if (!artigo) {
    notFound();
  }

  // Formata a data para o padrão brasileiro
  const dataCriacao = new Date(artigo.data_criacao).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });

  return (
    <article className="w-full max-w-[800px] mx-auto px-5 py-[50px] font-lora">
      
      <div className="mb-[30px] text-center">
        <h1 className="text-[40px] md:text-[50px] font-serif font-bold text-heading mb-[15px] leading-tight">
          {artigo.titulo}
        </h1>
        {artigo.subtitulo && (
          <p className="text-[20px] md:text-[24px] text-gray-600 italic mb-[20px]">
            {artigo.subtitulo}
          </p>
        )}
        <p className="text-sm text-gray-400 font-sans uppercase tracking-widest">
          Publicado em {dataCriacao}
        </p>
      </div>

      <figure className="mb-[50px]">
        <Image 
          src={artigo.imagem || '/images/leis-hermeticas.webp'} 
          alt={artigo.titulo} 
          width={800} 
          height={450} 
          className="w-full h-auto max-h-[500px] rounded-[16px] object-cover shadow-sm"
          priority
        />
      </figure>

      {/* Exibe o conteúdo, permitindo quebra de linhas reais */}
      <div className="text-[18px] md:text-[20px] text-foreground leading-[1.8] mb-[50px] whitespace-pre-wrap">
        {artigo.conteudo || "Nenhum conteúdo adicionado a este artigo ainda. Acesse o painel do Supabase para editar o texto!"}
      </div>

      <hr className="my-[50px] mx-auto h-[1px] border-none w-1/4 bg-gradient-to-r from-transparent via-primary to-transparent" />

      <div className="text-center mt-[50px]">
        <Link href="/" className="inline-block px-[30px] py-[15px] border border-gray-300 rounded-[50px] hover:bg-gray-50 hover:border-primary hover:text-primary transition-all font-sans font-medium text-sm">
          &larr; Voltar para a página inicial
        </Link>
      </div>
    </article>
  );
}
