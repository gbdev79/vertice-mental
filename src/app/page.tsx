import Link from 'next/link';
import Image from 'next/image';
import { createClient } from '@/utils/supabase/server';

export const dynamic = 'force-dynamic';

export default async function Home() {
  const supabase = await createClient();
  
  // Busca os 4 últimos artigos direto do Supabase
  const { data: artigos } = await supabase
    .from('artigos')
    .select('*')
    .order('data_criacao', { ascending: false })
    .limit(4);

  return (
    <div className="flex flex-col items-center w-full font-lora">
      {/* Capa do Blog */}
      <section className="w-full max-w-[1200px] mx-auto px-4 py-[50px] text-center" id="capa">
        <h1 className="text-[50px] font-serif font-bold text-heading mb-[50px]">
          Bem-vindo(a) ao Vértice Mental
        </h1>
        
        <figure className="max-w-[1200px] mx-auto mb-8">
          <Image 
            src="/images/banner-vertice-mental.webp" 
            alt="Banner do blog Vértice Mental com símbolo geométrico e visual tecnológico" 
            width={1200} 
            height={600} 
            className="w-full h-auto rounded-[28px] object-cover"
            priority
          />
          <figcaption className="mt-[10px] mb-[30px] text-[1.3rem] text-[#666] italic text-center">
            Um espaço para conectar filosofia, cultura, espiritualidade, tecnologia e ideias. 
          </figcaption>
        </figure>
        
        <p className="relative w-fit mx-auto mt-[70px] mb-[70px] text-[1.5rem] text-foreground italic pr-[5px] border-r-4 border-primary">
          &#8220;Aqui, pensamentos se encontram, referências se cruzam e novos vértices mentais surgem.&#8221;
        </p>
      </section>

      {/* Divisor gradiente */}
      <hr className="my-[50px] mx-auto h-[1px] border-none w-1/4 bg-gradient-to-r from-transparent via-primary to-transparent" />

      {/* Seção de Artigos */}
      <section className="w-full max-w-[1200px] mx-auto px-4 py-8 flex flex-col items-center" id="artigos">
        <h2 className="text-[40px] font-serif font-bold text-heading mb-[10px]">Últimos Artigos</h2>
        <p className="text-foreground text-lg italic mb-[15px]">
          Confira as reflexões mais recentes publicadas no Vértice Mental.
        </p>

        <div className="mt-[50px] flex justify-center items-stretch gap-[40px] flex-wrap w-full">
          {/* Mapeamento dos artigos do Supabase */}
          {artigos && artigos.length > 0 ? (
            artigos.map((item) => (
              <article key={item.id} className="w-[500px] h-[480px] bg-white rounded-[12px] shadow-[0_4px_15px_rgba(0,0,0,0.2)] px-[25px] flex flex-col text-center overflow-hidden transition-transform duration-300">
                <Image 
                  src={item.imagem || '/images/leis-hermeticas.webp'} 
                  alt={item.titulo} 
                  width={450} 
                  height={220} 
                  className="w-full h-[220px] object-cover rounded-[8px] mt-[20px] mx-auto"
                />
                <div className="flex-1 mt-[20px]">
                  <h3 className="text-[18px] font-serif font-bold text-heading m-0">
                    {item.titulo}
                  </h3>
                  <p className="text-gray-600 mt-[10px] line-clamp-2">
                    {item.subtitulo}
                  </p>
                </div>
                <hr className="my-[10px] mx-auto w-1/2 border-gray-200" />
                <Link href={`/artigo/${item.slug}`} className="mb-[25px] text-[20px] font-serif font-bold hover:scale-110 hover:text-primary hover:underline underline-offset-[2px] transition-all cursor-pointer">
                  Ler artigo completo
                </Link>
              </article>
            ))
          ) : (
            <p className="text-center italic text-gray-500 w-full mt-[30px]">
              Nenhum artigo publicado ainda. Acesse o Supabase para cadastrar seu primeiro artigo!
            </p>
          )}
        </div>

        <div className="mt-[50px] mb-[50px] flex gap-[20px] justify-center items-center w-full transition-transform duration-300 hover:scale-110 group cursor-pointer">
          <hr className="flex-[0.3] m-0 w-auto bg-gray-300 h-[1px] border-none" />
          <Link href="/blog" className="whitespace-nowrap mb-0 font-serif font-bold text-[22px] group-hover:text-primary group-hover:underline underline-offset-[2px] transition-all">
            Ver todos os artigos publicados
          </Link>
          <hr className="flex-[0.3] m-0 w-auto bg-gray-300 h-[1px] border-none" />
        </div>
      </section>

      {/* Formulário de Newsletter idêntico ao original */}
      <section 
        className="w-full flex flex-col items-center justify-center py-[30px] gap-[10px] m-0 h-[300px] font-sans"
        style={{ background: 'radial-gradient(circle, var(--color-primary-dark), #1a1a1a)' }}
      >
        <h2 className="text-[40px] font-serif font-bold text-white mb-0">Seja avisado de novos artigos</h2>
        <hr className="mx-auto mt-0 mb-[40px] h-[1px] border-none w-[300px] bg-gradient-to-r from-transparent via-primary to-transparent" />

        <form className="flex flex-row gap-[20px] flex-wrap justify-center w-full max-w-[1200px]" action="#">
          <fieldset className="flex flex-row gap-[20px] flex-wrap justify-center border-none p-0">
            <p className="m-0">
              <input 
                type="text" 
                placeholder="Digite seu nome" 
                className="bg-input-bg text-white rounded-[5px] p-[10px] border border-[#666] w-[300px] transition-all duration-300 hover:bg-input-hover focus:outline-none focus:bg-[#1a1a1a] focus:border-input-focus focus:shadow-[0_0_10px_var(--color-input-focus)]"
                required 
              />
            </p>
            <p className="m-0">
              <input 
                type="email" 
                placeholder="seuemail@exemplo.com" 
                className="bg-input-bg text-white rounded-[5px] p-[10px] border border-[#666] w-[300px] transition-all duration-300 hover:bg-input-hover focus:outline-none focus:bg-[#1a1a1a] focus:border-input-focus focus:shadow-[0_0_10px_var(--color-input-focus)]"
                required 
              />
            </p>
            <p className="m-0 flex justify-center">
              <button 
                type="submit" 
                className="self-center px-[15px] py-[10px] border-none rounded-[5px] transition-all duration-300 text-white font-bold bg-gradient-to-br from-primary-light to-primary hover:scale-110 hover:shadow-[0_0_5px_var(--color-primary),0_0_15px_var(--color-primary),0_0_25px_var(--color-primary)] outline-none cursor-pointer"
              >
                Cadastrar
              </button>
            </p>
          </fieldset>
        </form>
      </section>
    </div>
  );
}
