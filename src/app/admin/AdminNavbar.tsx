'use client';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { createClient } from '@/utils/supabase/client';
import { 
  FileText, 
  PlusCircle, 
  ExternalLink, 
  LogOut, 
  ShieldCheck 
} from 'lucide-react';

export default function AdminNavbar() {
  const pathname = usePathname();
  const router = useRouter();
  const supabase = createClient();

  const isLoginPage = pathname === '/admin/login';

  const handleLogout = async () => {
    await supabase.auth.signOut();
    router.push('/admin/login');
    router.refresh();
  };

  return (
    <header className="bg-[#121319] text-white border-b border-gray-800 sticky top-0 z-50 shadow-md">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        {/* Identidade do Painel Administrativo */}
        <div className="flex items-center gap-6">
          <Link href="/admin" className="flex items-center gap-2.5 group">
            <span className="font-serif font-bold text-lg md:text-xl text-white tracking-wide group-hover:text-primary-light transition-colors">
              Vértice Mental
            </span>
            <span className="bg-primary/20 text-primary-light border border-primary/40 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1">
              <ShieldCheck size={12} />
              Admin
            </span>
          </Link>

          {/* Navegação Administrativa (visível quando logado) */}
          {!isLoginPage && (
            <nav className="hidden md:flex items-center gap-1 ml-4 border-l border-gray-800 pl-4">
              <Link
                href="/admin"
                className={`flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium transition-colors ${
                  pathname === '/admin'
                    ? 'bg-primary text-white font-semibold'
                    : 'text-gray-300 hover:text-white hover:bg-gray-800/60'
                }`}
              >
                <FileText size={16} />
                Artigos
              </Link>
              <Link
                href="/admin/artigos/novo"
                className={`flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium transition-colors ${
                  pathname === '/admin/artigos/novo'
                    ? 'bg-primary text-white font-semibold'
                    : 'text-gray-300 hover:text-white hover:bg-gray-800/60'
                }`}
              >
                <PlusCircle size={16} />
                Novo Artigo
              </Link>
            </nav>
          )}
        </div>

        {/* Ações à Direita */}
        <div className="flex items-center gap-3">
          
          {/* Botão Acessar Site (abre em nova guia) */}
          <Link
            href="/"
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-1.5 text-xs md:text-sm font-medium bg-gray-800/80 hover:bg-gray-700 text-gray-200 hover:text-white px-3.5 py-1.5 rounded-lg border border-gray-700 transition-colors shadow-xs"
            title="Abrir o blog em uma nova guia"
          >
            <span>Acessar Site</span>
            <ExternalLink size={14} className="text-gray-400" />
          </Link>

          {/* Botão Sair / Logout */}
          {!isLoginPage && (
            <button
              type="button"
              onClick={handleLogout}
              className="flex items-center gap-1.5 text-xs md:text-sm font-medium text-gray-400 hover:text-red-400 hover:bg-red-950/20 px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer"
              title="Encerrar sessão administrativa"
            >
              <LogOut size={15} />
              <span className="hidden sm:inline">Sair</span>
            </button>
          )}
        </div>

      </div>

      {/* Sub-menu Mobile (quando em telas menores) */}
      {!isLoginPage && (
        <div className="md:hidden border-t border-gray-800/80 px-4 py-2 flex items-center gap-2 bg-[#0e0f14]">
          <Link
            href="/admin"
            className={`flex items-center gap-1.5 px-3 py-1 rounded text-xs font-medium ${
              pathname === '/admin' ? 'bg-primary text-white' : 'text-gray-300'
            }`}
          >
            <FileText size={14} />
            Artigos
          </Link>
          <Link
            href="/admin/artigos/novo"
            className={`flex items-center gap-1.5 px-3 py-1 rounded text-xs font-medium ${
              pathname === '/admin/artigos/novo' ? 'bg-primary text-white' : 'text-gray-300'
            }`}
          >
            <PlusCircle size={14} />
            Novo Artigo
          </Link>
        </div>
      )}
    </header>
  );
}
