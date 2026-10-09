import { createClient } from '@/utils/supabase/server';
import { redirect } from 'next/navigation';
import Link from 'next/link';

export default async function AdminDashboard() {
  const supabase = await createClient();
  
  // 1. Verificação de Segurança: Garante que apenas usuários logados acessem essa página
  const { data: { user } } = await supabase.auth.getUser();
  
  if (!user) {
    redirect('/admin/login');
  }

  // 2. Busca todos os artigos criados
  const { data: artigos } = await supabase
    .from('artigos')
    .select('id, titulo, slug, data_criacao')
    .order('data_criacao', { ascending: false });

  return (
    <div className="bg-white p-6 md:p-8 rounded-[12px] shadow-sm border border-gray-200">
      
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h2 className="text-2xl font-bold font-serif text-gray-900">Gerenciar Artigos</h2>
          <p className="text-sm text-gray-500 mt-1">Bem-vindo, {user.email}</p>
        </div>
        
        <Link 
          href="/admin/artigos/novo" 
          className="bg-primary text-white px-5 py-2.5 rounded-md hover:bg-primary-dark transition-colors text-sm font-medium shadow-sm whitespace-nowrap"
        >
          + Novo Artigo
        </Link>
      </div>

      <div className="overflow-x-auto rounded-lg border border-gray-200">
        <table className="w-full text-left border-collapse">
          <thead className="bg-gray-50">
            <tr>
              <th className="p-4 text-sm font-semibold text-gray-600 border-b border-gray-200">Título</th>
              <th className="p-4 text-sm font-semibold text-gray-600 border-b border-gray-200">Data de Criação</th>
              <th className="p-4 text-sm font-semibold text-gray-600 border-b border-gray-200 text-right">Ações</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {artigos?.map((artigo) => {
              const dataCriacao = new Date(artigo.data_criacao).toLocaleDateString('pt-BR');
              return (
                <tr key={artigo.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 text-sm text-gray-900 font-medium">
                    {artigo.titulo}
                    <span className="block text-xs text-gray-400 font-normal mt-0.5">/{artigo.slug}</span>
                  </td>
                  <td className="p-4 text-sm text-gray-500">
                    {dataCriacao}
                  </td>
                  <td className="p-4 text-right space-x-4">
                    <Link href={`/admin/artigos/${artigo.id}`} className="text-primary hover:underline text-sm font-medium">
                      Editar
                    </Link>
                    <button className="text-red-600 hover:underline text-sm font-medium">
                      Excluir
                    </button>
                  </td>
                </tr>
              );
            })}
            
            {(!artigos || artigos.length === 0) && (
              <tr>
                <td colSpan={3} className="p-8 text-center text-gray-500 text-sm">
                  Nenhum artigo encontrado. Crie o seu primeiro texto agora mesmo!
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      
    </div>
  );
}
