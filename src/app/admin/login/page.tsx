import { createClient } from '@/utils/supabase/server';
import { redirect } from 'next/navigation';

export default async function LoginPage({ searchParams }: { searchParams: Promise<{ error?: string }> }) {
  const { error } = await searchParams;

  // Server Action para processar o login com segurança no backend
  async function login(formData: FormData) {
    'use server';
    const email = formData.get('email') as string;
    const password = formData.get('password') as string;
    const supabase = await createClient();

    const { error } = await supabase.auth.signInWithPassword({ email, password });

    if (error) {
      redirect('/admin/login?error=E-mail ou senha incorretos');
    }

    // Se der sucesso, redireciona para o painel
    redirect('/admin');
  }

  return (
    <div className="max-w-md mx-auto mt-20 bg-white p-8 rounded-[12px] shadow-sm border border-gray-200">
      <div className="text-center mb-8">
        <h2 className="text-3xl font-bold font-serif text-gray-900 mb-2">Acesso Restrito</h2>
        <p className="text-gray-500 text-sm">Insira suas credenciais do Supabase para gerenciar o blog.</p>
      </div>
      
      {error && (
        <div className="bg-red-50 text-red-600 p-3 rounded-md mb-6 text-sm text-center border border-red-100">
          {error}
        </div>
      )}

      <form action={login} className="flex flex-col gap-5">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
          <input 
            name="email" 
            type="email" 
            required 
            placeholder="seu@email.com"
            className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition-all"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input 
            name="password" 
            type="password" 
            required 
            placeholder="••••••••"
            className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition-all"
          />
        </div>
        <button 
          type="submit" 
          className="mt-2 w-full bg-primary text-white p-3 rounded-md font-medium hover:bg-primary-dark transition-colors shadow-sm"
        >
          Entrar no Painel
        </button>
      </form>
    </div>
  );
}
