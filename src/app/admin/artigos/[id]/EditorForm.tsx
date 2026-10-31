'use client';
import { useState, useRef, useMemo } from 'react';
import { createClient } from '@/utils/supabase/client';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

// Tiptap core & extensions
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import LinkExtension from '@tiptap/extension-link';
import ImageExtension from '@tiptap/extension-image';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import { TextStyle, FontSize } from '@tiptap/extension-text-style';
import Color from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';

// Lucide Icons
import {
  Bold,
  Italic,
  Underline as UnderlineIcon,
  Strikethrough,
  Heading1,
  Heading2,
  Heading3,
  Type,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  List,
  ListOrdered,
  Quote,
  Minus,
  Link2,
  Unlink,
  Image as ImageIcon,
  Palette,
  Highlighter,
  Undo,
  Redo,
  RemoveFormatting,
  Upload,
  Eye,
  Edit3,
  Clock,
  FileText,
  X
} from 'lucide-react';

const CORES_TEXTO = [
  { nome: 'Padrão', cor: '#2b2b2b' },
  { nome: 'Roxo Principal', cor: '#6a0dad' },
  { nome: 'Roxo Claro', cor: '#8b5fbf' },
  { nome: 'Roxo Escuro', cor: '#3c0464' },
  { nome: 'Azul', cor: '#2563eb' },
  { nome: 'Verde', cor: '#16a34a' },
  { nome: 'Vermelho', cor: '#dc2626' },
  { nome: 'Laranja', cor: '#ea580c' },
  { nome: 'Cinza', cor: '#6b7280' },
];

const CORES_MARCATEXTO = [
  { nome: 'Nenhum', cor: '' },
  { nome: 'Amarelo', cor: '#fef08a' },
  { nome: 'Verde', cor: '#bbf7d0' },
  { nome: 'Azul', cor: '#bfdbfe' },
  { nome: 'Lilás', cor: '#e9d5ff' },
  { nome: 'Rosa', cor: '#fbcfe8' },
  { nome: 'Laranja', cor: '#fed7aa' },
];

const TAMANHOS_FONTE = [
  { label: 'Padrão (16px)', value: '' },
  { label: 'Pequeno (14px)', value: '14px' },
  { label: 'Médio (18px)', value: '18px' },
  { label: 'Grande (22px)', value: '22px' },
  { label: 'Destaque (26px)', value: '26px' },
  { label: 'Título (32px)', value: '32px' },
];

export default function EditorForm({ artigoInicial }: { artigoInicial?: any }) {
  const [titulo, setTitulo] = useState(artigoInicial?.titulo || '');
  const [subtitulo, setSubtitulo] = useState(artigoInicial?.subtitulo || '');
  const [slug, setSlug] = useState(artigoInicial?.slug || '');
  const [imagem, setImagem] = useState(artigoInicial?.imagem || '');
  const [conteudo, setConteudo] = useState(artigoInicial?.conteudo || '');
  const [salvando, setSalvando] = useState(false);
  const [erro, setErro] = useState('');

  // Estados de controle da interface
  const [modoVisualizacao, setModoVisualizacao] = useState<'editor' | 'previa'>('editor');
  const [modalImagemAberto, setModalImagemAberto] = useState(false);
  const [urlImagemInput, setUrlImagemInput] = useState('');
  const [altImagemInput, setAltImagemInput] = useState('');
  const [menuCorAberto, setMenuCorAberto] = useState(false);
  const [menuMarcaTextoAberto, setMenuMarcaTextoAberto] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);
  const capaInputRef = useRef<HTMLInputElement>(null);

  const router = useRouter();
  const supabase = createClient();

  // Configuração do Editor Tiptap CMS
  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: { levels: [1, 2, 3] },
      }),
      TextStyle,
      FontSize,
      Color,
      Highlight.configure({ multicolor: true }),
      Underline,
      TextAlign.configure({
        types: ['heading', 'paragraph'],
      }),
      ImageExtension.configure({
        allowBase64: true,
        HTMLAttributes: {
          class: 'rounded-xl max-w-full my-4 shadow-md mx-auto block',
        },
      }),
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
        class: 'tiptap p-6 min-h-[450px] max-h-[800px] overflow-y-auto focus:outline-none article-body text-[18px]',
      },
    },
    onUpdate: ({ editor }) => {
      setConteudo(editor.getHTML());
    },
  });

  // Estatísticas de leitura
  const estatisticas = useMemo(() => {
    if (!conteudo) return { palavras: 0, caracteres: 0, tempoLeitura: 0 };
    // Remove tags HTML para contagem limpa
    const textoPuro = conteudo.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    const caracteres = textoPuro.length;
    const palavras = textoPuro ? textoPuro.split(' ').length : 0;
    const tempoLeitura = Math.max(1, Math.ceil(palavras / 200)); // média de 200 palavras/min
    return { palavras, caracteres, tempoLeitura };
  }, [conteudo]);

  const gerarSlug = (texto: string) => {
    return texto
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '');
  };

  const handleTituloChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setTitulo(e.target.value);
    if (!artigoInicial) {
      setSlug(gerarSlug(e.target.value));
    }
  };

  // Upload ou escolha de imagem para o corpo do artigo
  const handleUploadImagemArquivo = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      const base64 = reader.result as string;
      editor?.chain().focus().setImage({ src: base64, alt: altImagemInput || file.name }).run();
      setModalImagemAberto(false);
      setUrlImagemInput('');
      setAltImagemInput('');
      if (fileInputRef.current) fileInputRef.current.value = '';
    };
    reader.readAsDataURL(file);
  };

  const handleInserirImagemURL = () => {
    if (!urlImagemInput) return;
    editor?.chain().focus().setImage({ src: urlImagemInput, alt: altImagemInput || 'Imagem do artigo' }).run();
    setModalImagemAberto(false);
    setUrlImagemInput('');
    setAltImagemInput('');
  };

  // Upload de arquivo para a Imagem de Capa
  const handleUploadCapa = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      setImagem(reader.result as string);
    };
    reader.readAsDataURL(file);
  };

  // Manipulação de Links
  const handleAdicionarLink = () => {
    const linkAnterior = editor?.getAttributes('link').href;
    const url = window.prompt('URL do Link (ex: https://...):', linkAnterior || 'https://');
    if (url === null) return;
    if (url.trim() === '') {
      editor?.chain().focus().unsetLink().run();
      return;
    }
    editor?.chain().focus().setLink({ href: url.trim() }).run();
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
    <form onSubmit={salvar} className="flex flex-col gap-6 max-w-5xl mx-auto pb-16">
      
      {erro && (
        <div className="bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg text-sm shadow-sm flex items-start gap-3">
          <span className="font-bold text-lg">⚠️</span>
          <div>
            <strong>Erro ao salvar:</strong> {erro}
            <p className="text-xs text-red-500 mt-1">
              Verifique se as permissões de INSERT/UPDATE (RLS) no Supabase estão ativas para administradores.
            </p>
          </div>
        </div>
      )}

      {/* Cabeçalho do Editor e Metadados */}
      <div className="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5">
        <div className="flex flex-col md:flex-row gap-6">
          <div className="flex-1 space-y-4">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                Título do Artigo
              </label>
              <input 
                type="text" 
                required 
                value={titulo} 
                onChange={handleTituloChange}
                placeholder="Ex: As 7 Leis Herméticas na Vida Cotidiana"
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent focus:outline-none text-lg font-serif"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                Link Permanente (Slug)
              </label>
              <div className="flex items-center">
                <span className="bg-gray-100 text-gray-500 px-3 py-3 border border-r-0 border-gray-300 rounded-l-lg text-sm select-none">
                  /artigo/
                </span>
                <input 
                  type="text" 
                  required 
                  value={slug} 
                  onChange={(e) => setSlug(e.target.value)}
                  placeholder="as-7-leis-hermeticas"
                  className="w-full px-3 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-transparent focus:outline-none bg-gray-50 text-sm font-mono"
                />
              </div>
            </div>
          </div>
          
          <div className="flex-1 space-y-4">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                Subtítulo / Linha Fina
              </label>
              <input 
                type="text" 
                value={subtitulo} 
                onChange={(e) => setSubtitulo(e.target.value)}
                placeholder="Uma breve introdução instigante para o leitor..."
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent focus:outline-none text-sm italic"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                Imagem de Capa (URL ou Upload)
              </label>
              <div className="flex gap-2">
                <input 
                  type="text" 
                  value={imagem} 
                  onChange={(e) => setImagem(e.target.value)}
                  placeholder="https://... ou escolha um arquivo"
                  className="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent focus:outline-none text-sm"
                />
                <input 
                  type="file" 
                  ref={capaInputRef} 
                  onChange={handleUploadCapa} 
                  accept="image/*" 
                  className="hidden" 
                />
                <button
                  type="button"
                  onClick={() => capaInputRef.current?.click()}
                  className="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg border border-gray-300 text-xs font-medium flex items-center gap-1.5 transition-colors"
                  title="Carregar imagem do seu computador"
                >
                  <Upload size={14} />
                  <span>Arquivo</span>
                </button>
              </div>
              {imagem && (
                <p className="text-xs text-green-600 mt-1 flex items-center gap-1">
                  ✓ Capa carregada ({imagem.startsWith('data:') ? 'Imagem do computador' : imagem.slice(0, 45) + '...'})
                </p>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Editor Principal CMS */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
        
        {/* Barra Superior de Abas (Modo Edição vs Prévia) */}
        <div className="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200">
          <div className="flex items-center gap-1">
            <button
              type="button"
              onClick={() => setModoVisualizacao('editor')}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors ${
                modoVisualizacao === 'editor'
                  ? 'bg-white text-primary shadow-xs border border-gray-200'
                  : 'text-gray-600 hover:text-gray-900'
              }`}
            >
              <Edit3 size={14} />
              Editor CMS
            </button>
            <button
              type="button"
              onClick={() => setModoVisualizacao('previa')}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors ${
                modoVisualizacao === 'previa'
                  ? 'bg-white text-primary shadow-xs border border-gray-200'
                  : 'text-gray-600 hover:text-gray-900'
              }`}
            >
              <Eye size={14} />
              Pré-visualização
            </button>
          </div>

          {/* Estatísticas rápidas de leitura */}
          <div className="hidden sm:flex items-center gap-4 text-xs text-gray-500">
            <span className="flex items-center gap-1">
              <FileText size={13} />
              {estatisticas.palavras} palavras
            </span>
            <span className="flex items-center gap-1">
              <Clock size={13} />
              ~{estatisticas.tempoLeitura} min de leitura
            </span>
          </div>
        </div>

        {/* BARRA DE FERRAMENTAS DO CMS (Sticky Toolbar) */}
        {modoVisualizacao === 'editor' && editor && (
          <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-xs border-b border-gray-200 p-2 flex flex-wrap items-center gap-1 shadow-xs">
            
            {/* Histórico */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={() => editor.chain().focus().undo().run()}
                disabled={!editor.can().undo()}
                className="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 text-gray-700"
                title="Desfazer (Ctrl+Z)"
              >
                <Undo size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().redo().run()}
                disabled={!editor.can().redo()}
                className="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 text-gray-700"
                title="Refazer (Ctrl+Y)"
              >
                <Redo size={16} />
              </button>
            </div>

            {/* Cabeçalhos e Níveis de Texto */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={() => editor.chain().focus().setParagraph().run()}
                className={`px-2 py-1 rounded text-xs font-medium ${
                  editor.isActive('paragraph') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Texto Normal"
              >
                <Type size={15} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleHeading({ level: 1 }).run()}
                className={`p-1.5 rounded text-xs font-bold ${
                  editor.isActive('heading', { level: 1 }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Título Principal (H1)"
              >
                <Heading1 size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
                className={`p-1.5 rounded text-xs font-bold ${
                  editor.isActive('heading', { level: 2 }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Subtítulo Seção (H2)"
              >
                <Heading2 size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}
                className={`p-1.5 rounded text-xs font-bold ${
                  editor.isActive('heading', { level: 3 }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Intertítulo (H3)"
              >
                <Heading3 size={16} />
              </button>
            </div>

            {/* Tamanho da Fonte */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1">
              <select
                onChange={(e) => {
                  const val = e.target.value;
                  if (!val) {
                    (editor.chain().focus() as any).unsetFontSize().run();
                  } else {
                    (editor.chain().focus() as any).setFontSize(val).run();
                  }
                }}
                className="text-xs bg-gray-50 border border-gray-300 rounded px-2 py-1 text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary"
                title="Tamanho da Letra"
                defaultValue=""
              >
                {TAMANHOS_FONTE.map((item) => (
                  <option key={item.label} value={item.value}>
                    {item.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Formatação Básica (Bold, Italic, Underline, Strike) */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleBold().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('bold') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Negrito (Ctrl+B)"
              >
                <Bold size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleItalic().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('italic') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Itálico (Ctrl+I)"
              >
                <Italic size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleUnderline().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('underline') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Sublinhado (Ctrl+U)"
              >
                <UnderlineIcon size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleStrike().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('strike') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Tachado"
              >
                <Strikethrough size={16} />
              </button>
            </div>

            {/* Cores e Marca-Texto */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-1 relative">
              
              {/* Menu de Cor do Texto */}
              <div className="relative">
                <button
                  type="button"
                  onClick={() => {
                    setMenuCorAberto(!menuCorAberto);
                    setMenuMarcaTextoAberto(false);
                  }}
                  className="flex items-center gap-1 p-1.5 rounded hover:bg-gray-100 text-gray-700"
                  title="Cor da Fonte"
                >
                  <Palette size={16} />
                  <span 
                    className="w-3 h-3 rounded-full border border-gray-300"
                    style={{ backgroundColor: editor.getAttributes('textStyle').color || '#2b2b2b' }}
                  />
                </button>

                {menuCorAberto && (
                  <div className="absolute left-0 mt-1 p-2 bg-white rounded-lg shadow-lg border border-gray-200 z-30 w-44">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                      Cor da Letra
                    </p>
                    <div className="grid grid-cols-4 gap-1.5 mb-2">
                      {CORES_TEXTO.map((c) => (
                        <button
                          key={c.cor}
                          type="button"
                          onClick={() => {
                            editor.chain().focus().setColor(c.cor).run();
                            setMenuCorAberto(false);
                          }}
                          className="w-7 h-7 rounded-md border border-gray-200 hover:scale-110 transition-transform shadow-xs"
                          style={{ backgroundColor: c.cor }}
                          title={c.nome}
                        />
                      ))}
                    </div>
                    <div className="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                      <span>Personalizada:</span>
                      <input 
                        type="color" 
                        onChange={(e) => editor.chain().focus().setColor(e.target.value).run()}
                        className="w-6 h-6 p-0 border-0 rounded cursor-pointer"
                      />
                    </div>
                  </div>
                )}
              </div>

              {/* Menu de Marca-Texto (Highlight) */}
              <div className="relative">
                <button
                  type="button"
                  onClick={() => {
                    setMenuMarcaTextoAberto(!menuMarcaTextoAberto);
                    setMenuCorAberto(false);
                  }}
                  className={`flex items-center gap-1 p-1.5 rounded text-gray-700 ${
                    editor.isActive('highlight') ? 'bg-yellow-100 text-yellow-800' : 'hover:bg-gray-100'
                  }`}
                  title="Marca-texto (Fundo Colorido)"
                >
                  <Highlighter size={16} />
                </button>

                {menuMarcaTextoAberto && (
                  <div className="absolute left-0 mt-1 p-2 bg-white rounded-lg shadow-lg border border-gray-200 z-30 w-44">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                      Marca-Texto
                    </p>
                    <div className="grid grid-cols-4 gap-1.5">
                      {CORES_MARCATEXTO.map((c) => (
                        <button
                          key={c.nome}
                          type="button"
                          onClick={() => {
                            if (!c.cor) {
                              editor.chain().focus().unsetHighlight().run();
                            } else {
                              editor.chain().focus().setHighlight({ color: c.cor }).run();
                            }
                            setMenuMarcaTextoAberto(false);
                          }}
                          className="w-7 h-7 rounded-md border border-gray-200 hover:scale-110 transition-transform flex items-center justify-center text-[10px] font-bold"
                          style={{ backgroundColor: c.cor || '#ffffff' }}
                          title={c.nome}
                        >
                          {!c.cor ? '✕' : ''}
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Alinhamento de Texto */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={() => editor.chain().focus().setTextAlign('left').run()}
                className={`p-1.5 rounded ${
                  editor.isActive({ textAlign: 'left' }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Alinhar à Esquerda"
              >
                <AlignLeft size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().setTextAlign('center').run()}
                className={`p-1.5 rounded ${
                  editor.isActive({ textAlign: 'center' }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Centralizar"
              >
                <AlignCenter size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().setTextAlign('right').run()}
                className={`p-1.5 rounded ${
                  editor.isActive({ textAlign: 'right' }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Alinhar à Direita"
              >
                <AlignRight size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().setTextAlign('justify').run()}
                className={`p-1.5 rounded ${
                  editor.isActive({ textAlign: 'justify' }) ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Justificar"
              >
                <AlignJustify size={16} />
              </button>
            </div>

            {/* Listas e Citação */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleBulletList().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('bulletList') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Lista com Marcadores"
              >
                <List size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleOrderedList().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('orderedList') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Lista Numerada"
              >
                <ListOrdered size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().toggleBlockquote().run()}
                className={`p-1.5 rounded ${
                  editor.isActive('blockquote') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Citação em Bloco"
              >
                <Quote size={16} />
              </button>
              <button
                type="button"
                onClick={() => editor.chain().focus().setHorizontalRule().run()}
                className="p-1.5 rounded hover:bg-gray-100 text-gray-700"
                title="Linha Divisória"
              >
                <Minus size={16} />
              </button>
            </div>

            {/* Inserção de Links e Mídia */}
            <div className="flex items-center border-r border-gray-200 pr-1.5 mr-1 gap-0.5">
              <button
                type="button"
                onClick={handleAdicionarLink}
                className={`p-1.5 rounded ${
                  editor.isActive('link') ? 'bg-primary/10 text-primary' : 'hover:bg-gray-100 text-gray-700'
                }`}
                title="Inserir / Editar Link"
              >
                <Link2 size={16} />
              </button>
              {editor.isActive('link') && (
                <button
                  type="button"
                  onClick={() => editor.chain().focus().unsetLink().run()}
                  className="p-1.5 rounded hover:bg-gray-100 text-red-500"
                  title="Remover Link"
                >
                  <Unlink size={16} />
                </button>
              )}
              <button
                type="button"
                onClick={() => setModalImagemAberto(true)}
                className="p-1.5 rounded hover:bg-primary/10 hover:text-primary text-gray-700 flex items-center gap-1 font-semibold text-xs"
                title="Inserir Imagem no Artigo"
              >
                <ImageIcon size={16} />
                <span className="hidden md:inline">Imagem</span>
              </button>
            </div>

            {/* Limpar Formatação */}
            <div>
              <button
                type="button"
                onClick={() => editor.chain().focus().unsetAllMarks().clearNodes().run()}
                className="p-1.5 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-700"
                title="Limpar toda a formatação selecionada"
              >
                <RemoveFormatting size={16} />
              </button>
            </div>

          </div>
        )}

        {/* ÁREA DE CONTEÚDO: MODO EDITOR OU MODO PRÉVIA */}
        {modoVisualizacao === 'editor' ? (
          <div className="min-h-[450px]">
            <EditorContent editor={editor} />
          </div>
        ) : (
          <div className="p-8 min-h-[450px] bg-white">
            <div className="max-w-2xl mx-auto border-b border-gray-100 pb-8 mb-8 text-center">
              <span className="text-xs uppercase font-sans text-primary font-bold tracking-widest">
                Pré-visualização do Leitor
              </span>
              <h1 className="text-3xl font-serif font-bold text-heading mt-2 mb-3">
                {titulo || 'Sem título definido'}
              </h1>
              {subtitulo && (
                <p className="text-lg text-gray-600 italic font-lora">
                  {subtitulo}
                </p>
              )}
              {imagem && (
                <img 
                  src={imagem} 
                  alt="Capa" 
                  className="w-full h-64 object-cover rounded-xl mt-6 shadow-sm"
                />
              )}
            </div>
            
            <div 
              className="article-body max-w-2xl mx-auto"
              dangerouslySetInnerHTML={{ 
                __html: conteudo || '<p class="text-gray-400 italic text-center">Nenhum conteúdo digitado ainda...</p>' 
              }}
            />
          </div>
        )}

        {/* Rodapé do Editor */}
        <div className="bg-gray-50 border-t border-gray-200 px-4 py-2.5 flex items-center justify-between text-xs text-gray-500">
          <span>
            {artigoInicial ? 'Modo de Edição' : 'Novo Artigo'} • Dica: Você pode colar imagens ou textos formatados diretamente
          </span>
          <span>
            {estatisticas.caracteres} caracteres
          </span>
        </div>

      </div>

      {/* Modal de Inserção de Imagem */}
      {modalImagemAberto && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
          <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between border-b pb-3">
              <h3 className="font-semibold text-gray-900 flex items-center gap-2">
                <ImageIcon size={18} className="text-primary" />
                Inserir Imagem no Texto
              </h3>
              <button 
                type="button" 
                onClick={() => setModalImagemAberto(false)}
                className="text-gray-400 hover:text-gray-600 p-1"
              >
                <X size={18} />
              </button>
            </div>

            {/* Opção 1: Upload do Computador */}
            <div className="bg-purple-50/60 p-4 rounded-lg border border-purple-100 text-center space-y-2">
              <Upload className="mx-auto text-primary" size={24} />
              <p className="text-xs text-gray-600 font-medium">
                Carregar arquivo de imagem do seu computador
              </p>
              <input 
                type="file" 
                ref={fileInputRef} 
                onChange={handleUploadImagemArquivo} 
                accept="image/*" 
                className="hidden" 
              />
              <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                className="px-4 py-2 bg-primary text-white rounded-md text-xs font-semibold hover:bg-primary-dark transition-colors shadow-xs"
              >
                Escolher Arquivo (PNG, JPG, WebP)
              </button>
            </div>

            <div className="relative flex py-1 items-center">
              <div className="flex-grow border-t border-gray-200"></div>
              <span className="flex-shrink mx-3 text-gray-400 text-xs uppercase">ou por Link da Web</span>
              <div className="flex-grow border-t border-gray-200"></div>
            </div>

            {/* Opção 2: URL da Web */}
            <div className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  URL da Imagem na Web
                </label>
                <input 
                  type="text" 
                  value={urlImagemInput} 
                  onChange={(e) => setUrlImagemInput(e.target.value)}
                  placeholder="https://exemplo.com/minha-imagem.webp"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  Legenda / Texto Alternativo (Alt)
                </label>
                <input 
                  type="text" 
                  value={altImagemInput} 
                  onChange={(e) => setAltImagemInput(e.target.value)}
                  placeholder="Descrição da imagem para acessibilidade"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                />
              </div>
            </div>

            <div className="flex justify-end gap-2 pt-2 border-t">
              <button
                type="button"
                onClick={() => setModalImagemAberto(false)}
                className="px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-md"
              >
                Cancelar
              </button>
              <button
                type="button"
                onClick={handleInserirImagemURL}
                disabled={!urlImagemInput}
                className="px-4 py-2 text-xs font-medium bg-primary text-white rounded-md hover:bg-primary-dark disabled:opacity-40"
              >
                Inserir por URL
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Botões de Ação do Artigo */}
      <div className="flex items-center justify-between pt-2">
        <Link 
          href="/admin" 
          className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm"
        >
          &larr; Voltar ao Painel
        </Link>
        <div className="flex gap-3">
          <button 
            type="submit" 
            disabled={salvando}
            className="px-8 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all font-semibold text-sm shadow-md hover:shadow-lg disabled:opacity-50 flex items-center gap-2 cursor-pointer"
          >
            {salvando ? 'Salvando Alterações...' : (artigoInicial ? 'Salvar e Atualizar Artigo' : 'Publicar Novo Artigo')}
          </button>
        </div>
      </div>

    </form>
  );
}
