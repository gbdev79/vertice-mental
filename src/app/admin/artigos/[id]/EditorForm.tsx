'use client';
import { useState } from 'react';
import { createClient } from '@/utils/supabase/client';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

// Importações do Tiptap
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import LinkExtension from '@tiptap/extension-link';

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

  // Configuração do Editor Tiptap
  const editor = useEditor({
    extensions: [
      StarterKit,
      LinkExtension.configure({
        openOnClick: false,
        HTMLAttributes: {
          class: 'text-primary underline cursor-pointer',
        },
      }),
    ],
    content: conteudo,
    editorProps: {
      attributes: {
        class: 'p-4 min-h-[300px] max-h-[600px] overflow-y-auto focus:outline-none prose max-w-none',
      },
    },
    onUpdate: ({ editor }) => {
      setConteudo(editor.getHTML());
    },
  });

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
      const { error } = await supabase.from('artigos').update(dados).eq('id', artigoInicial.id);
      requestError = error;
    } else {
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
        
        {/* Barra de Ferramentas Tiptap */}
        {editor && (
          <div className="bg-white rounded-md overflow-hidden border border-gray-300 flex flex-col mb-12">
            <div className="bg-gray-100 p-2 flex gap-2 border-b border-gray-300 flex-wrap">
              <button type="button" onClick={() => editor.chain().focus().toggleBold().run()} className={`px-3 py-1 rounded text-sm font-bold ${editor.isActive('bold') ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>B</button>
              <button type="button" onClick={() => editor.chain().focus().toggleItalic().run()} className={`px-3 py-1 rounded text-sm italic ${editor.isActive('italic') ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>I</button>
              <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} className={`px-3 py-1 rounded text-sm font-bold ${editor.isActive('heading', { level: 2 }) ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>H2</button>
              <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()} className={`px-3 py-1 rounded text-sm font-bold ${editor.isActive('heading', { level: 3 }) ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>H3</button>
              <button type="button" onClick={() => editor.chain().focus().toggleBulletList().run()} className={`px-3 py-1 rounded text-sm ${editor.isActive('bulletList') ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>Lista</button>
              <button type="button" onClick={() => {
                const url = window.prompt('URL do Link:');
                if (url) editor.chain().focus().setLink({ href: url }).run();
              }} className={`px-3 py-1 rounded text-sm ${editor.isActive('link') ? 'bg-gray-300 text-black' : 'hover:bg-gray-200 text-gray-700'}`}>Link</button>
              <button type="button" onClick={() => editor.chain().focus().unsetLink().run()} disabled={!editor.isActive('link')} className="px-3 py-1 rounded text-sm hover:bg-gray-200 text-gray-700 disabled:opacity-50">Tirar Link</button>
            </div>
            
            <EditorContent editor={editor} />
          </div>
        )}
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
