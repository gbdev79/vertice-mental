import Link from 'next/link';
import Image from 'next/image';

export default function Header() {
  return (
    <header className="w-full bg-white shadow-sm sticky top-0 z-50">
      <div className="max-w-[1200px] mx-auto px-4 py-4 flex justify-between items-center">
        <Link href="/" className="hover:scale-105 transition-transform duration-300">
          <Image 
            src="/images/logo-vertice-mental.webp" 
            alt="Logo Vértice Mental" 
            width={180} 
            height={50} 
            className="h-auto w-auto max-h-[50px]"
            priority
          />
        </Link>
        <nav className="space-x-6 text-[16px] font-sans font-medium text-gray-800 flex items-center">
          <Link href="/" className="hover:text-primary hover:underline underline-offset-4 decoration-2 transition-all">Início</Link>
          <Link href="/blog" className="hover:text-primary hover:underline underline-offset-4 decoration-2 transition-all">Artigos</Link>
          <Link href="/loja" className="hover:text-primary hover:underline underline-offset-4 decoration-2 transition-all">Loja</Link>
          
          <div className="flex items-center gap-3 ml-4 border-l border-gray-300 pl-4">
            <a href="#" className="hover:scale-110 transition-transform">
              <Image src="/images/instagram-logo.webp" alt="Instagram" width={24} height={24} />
            </a>
            <a href="#" className="hover:scale-110 transition-transform">
              <Image src="/images/youtube-logo.webp" alt="YouTube" width={24} height={24} />
            </a>
          </div>
        </nav>
      </div>
    </header>
  );
}
