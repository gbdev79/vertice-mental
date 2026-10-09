'use client';
import { useState } from 'react';
import { createClient } from '@/utils/supabase/client';
import { useRouter } from 'next/navigation';
import dynamic from 'next/dynamic';
import Link from 'next/link';
import 'react-quill/dist/quill.snow.css';

// Carrega o editor visual dinamicamente (pois ele só funciona no lado do cliente/navegador)
const ReactQuill = dynamic(() => import('react-quill'), { ssr: false, loading: () => <p className="text-gray-400 italic">Carregando editor...</p> });

export default function EditorForm({ artigoInicial }: { artigoInicial?: any }) {
  const [titulo, setTitulo] = useState(artigoInicial?.titulo || '');
  const [subtitulo, setSubtitulo] = useState(artigoInicial?.subtitulo || '');
  const [slug, setSlug] = useState(artigoInicial?.slug || '');
  const [imagem, setImagem] = useState(artigoInicial?.imagem || '');
  const [conteudo, setConteudo] = useState(artigoInicial?.conteudo || '');
  const [salvando, setSalvando] = useState(false);
  const [erro, setErro] = useState('');

  const router = useRouter();
  const supabase = createClient();

  const gerarSlug = (texto: string) => {
    return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
  };

  const handleTituloChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setTitulo(e.target.value);
    if (!artigoInicial) {
      setSlug(gerarSlug(e.target.value)); // Autopreencher slug apenas ao criar
    }
  };

  const salvar = async (e: React.FormEvent) => {
    e.preventDefault();
    setSalvando(true);
    setErro('');
    
    const dados = { titulo, subtitulo, slug, imagem, conteudo };
    let requestError;

    if (artigoInicial?.id) {
      // Atualizar existente
      const { error } = await supabase.from('artigos').update(dados).eq('id', artigoInicial.id);
      requestError = error;
    } else {
      // Criar novo
      const { error } = await supabase.from('artigos').insert([dados]);
      requestError = error;
    }

    setSalvando(false);

    if (requestError) {
      setErro(requestError.message);
    } else {
      router.push('/admin');
      router.refresh();
    }
  };

  return (
    <form onSubmit={salvar} className="flex flex-col gap-6">
      
      {erro && (
        <div className="bg-red-50 border border-red-200 text-red-600 p-4 rounded-md text-sm">
          <strong>Erro ao salvar:</strong> {erro}. (Dica: Você ativou as políticas RLS de UPDATE/INSERT no Supabase?)
        </div>
      )}

      <div className="flex flex-col md:flex-row gap-6">
        <div className="flex-1 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Título do Artigo</label>
            <input 
              type="text" required value={titulo} onChange={handleTituloChange}
              placeholder="Ex: As Leis Herméticas"
              className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:outline-none"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Link URL (Slug)</label>
            <input 
              type="text" required value={slug} onChange={(e) => setSlug(e.target.value)}
              placeholder="ex: as-leis-hermeticas"
              className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:outline-none bg-gray-50"
            />
          </div>
        </div>
        
        <div className="flex-1 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Subtítulo / Resumo</label>
            <input 
              type="text" value={subtitulo} onChange={(e) => setSubtitulo(e.target.value)}
              placeholder="Uma breve descrição..."
              className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:outline-none"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">URL da Imagem de Capa</label>
            <input 
              type="text" value={imagem} onChange={(e) => setImagem(e.target.value)}
              placeholder="/images/capa.webp"
              className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:outline-none"
            />
          </div>
        </div>
      </div>

      <div className="mt-4">
        <label className="block text-sm font-medium text-gray-700 mb-2">Conteúdo do Artigo (Editor Visual)</label>
        <div className="bg-white rounded-md overflow-hidden border border-gray-300">
          <ReactQuill 
            theme="snow" 
            value={conteudo} 
            onChange={setConteudo} 
            className="h-[400px] mb-12"
            modules={{
              toolbar: [
                [{ 'header': [2, 3, false] }],
                ['bold', 'italic', 'underline', 'blockquote'],
                [{'list': 'ordered'}, {'list': 'bullet'}],
                ['link', 'image', 'video'],
                ['clean']
              ],
            }}
          />
        </div>
      </div>

      <div className="flex gap-4 justify-end mt-4">
        <Link href="/admin" className="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors font-medium">
          Cancelar
        </Link>
        <button 
          type="submit" 
          disabled={salvando}
          className="px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors font-medium shadow-sm disabled:opacity-50"
        >
          {salvando ? 'Salvando...' : (artigoInicial ? 'Atualizar Artigo' : 'Publicar Artigo')}
        </button>
      </div>

    </form>
  );
}
