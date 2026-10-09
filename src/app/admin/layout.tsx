import Link from 'next/link';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-100 flex flex-col font-sans">
      <header className="bg-black text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-50">
        <h1 className="text-xl font-bold font-serif tracking-wide">Vértice Mental Admin</h1>
        <nav className="space-x-6 text-sm font-medium">
          <Link href="/admin" className="hover:text-primary-light transition-colors">Painel</Link>
          <Link href="/" target="_blank" className="hover:text-primary-light transition-colors">Ver Site &rarr;</Link>
        </nav>
      </header>
      
      <main className="flex-1 p-4 md:p-8 flex justify-center">
        <div className="w-full max-w-[1000px]">
          {children}
        </div>
      </main>
    </div>
  );
}
