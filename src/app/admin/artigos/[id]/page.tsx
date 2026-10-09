import { createClient } from '@/utils/supabase/server';
import { redirect } from 'next/navigation';
import { Suspense } from 'react';
import EditorForm from './EditorForm';

async function FetchAndRenderForm({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const supabase = await createClient();

  // Verifica Autenticação
  const { data: { user } } = await supabase.auth.getUser();
  if (!user) {
    redirect('/admin/login');
  }

  let artigoInicial = null;

  // Se o ID não for 'novo', busca os dados do artigo para editar
  if (id !== 'novo') {
    const { data: artigo } = await supabase
      .from('artigos')
      .select('*')
      .eq('id', id)
      .single();
      
    if (artigo) {
      artigoInicial = artigo;
    }
  }

  return (
    <div>
      <div className="mb-8">
        <h2 className="text-2xl font-bold font-serif text-gray-900">
          {artigoInicial ? 'Editar Artigo' : 'Criar Novo Artigo'}
        </h2>
        <p className="text-sm text-gray-500 mt-1">
          {artigoInicial ? 'Atualize os dados e o conteúdo abaixo.' : 'Escreva e publique um novo texto para seus leitores.'}
        </p>
      </div>

      <div className="bg-white p-6 md:p-8 rounded-[12px] shadow-sm border border-gray-200">
        <EditorForm artigoInicial={artigoInicial} />
      </div>
    </div>
  );
}

export default function EditArticlePage({ params }: { params: Promise<{ id: string }> }) {
  return (
    <div className="w-full">
      <Suspense fallback={<p className="text-center italic text-gray-500 mt-10">Carregando editor...</p>}>
        <FetchAndRenderForm params={params} />
      </Suspense>
    </div>
  );
}
