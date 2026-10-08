'use client';
import { useState, useEffect } from 'react';
import Image from 'next/image';

export default function Footer() {
  const [copiado, setCopiado] = useState(false);
  const [ano, setAno] = useState('');

  useEffect(() => {
    setAno(new Date().getFullYear().toString());
  }, []);

  const copiarEmail = () => {
    navigator.clipboard.writeText('contato@verticemental.com.br');
    setCopiado(true);
    setTimeout(() => setCopiado(false), 2000);
  };

  return (
    <footer className="w-full bg-[#f4f4f4] pt-[40px] pb-[20px] font-sans mt-0">
      <div className="max-w-[1200px] mx-auto px-5 flex flex-col items-center justify-center gap-[20px]">
        <h2 className="text-[35px] font-serif font-bold text-heading m-0 p-0">Vértice Mental</h2>
        <hr className="m-0 mb-[15px] border-none h-[1px] w-[100px] bg-gradient-to-r from-transparent via-primary to-transparent" />
        
        <ul className="flex justify-center items-center gap-[15px] list-none p-0 m-0 text-foreground">
          <li className="flex items-center after:content-['|'] after:ml-[15px] after:text-[#ccc]">
            <a href="#" className="inline-flex items-center gap-[8px] hover:scale-110 hover:text-primary hover:underline transition-all duration-300">
              <Image src="/images/instagram-logo.webp" alt="Instagram" width={20} height={20} />
              Instagram
            </a>
          </li>
          <li className="flex items-center after:content-['|'] after:ml-[15px] after:text-[#ccc]">
            <a href="#" className="inline-flex items-center gap-[8px] hover:scale-110 hover:text-primary hover:underline transition-all duration-300">
              <Image src="/images/youtube-logo.webp" alt="YouTube" width={20} height={20} />
              YouTube
            </a>
          </li>
          <li className="flex items-center">
            <div 
              id="copiar-email" 
              className="relative cursor-pointer inline-flex items-center gap-[8px] hover:scale-110 hover:text-primary transition-all duration-300"
              onClick={copiarEmail}
            >
              <Image src="/images/icone-email.webp" alt="Email" width={20} height={20} />
              E-mail
              <span className={`absolute bottom-[130%] left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-[6px] text-[12px] font-sans whitespace-nowrap shadow-[0_4px_10px_rgba(0,0,0,0.15)] transition-all duration-300 pointer-events-none text-white ${copiado ? 'bg-primary opacity-100 translate-y-0 visible' : 'bg-[#333] opacity-0 translate-y-[10px] invisible'} after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-[5px] after:border-solid ${copiado ? 'after:border-primary after:border-b-transparent after:border-l-transparent after:border-r-transparent' : 'after:border-[#333] after:border-b-transparent after:border-l-transparent after:border-r-transparent'}`}>
                {copiado ? 'Copiado!' : 'Copiar e-mail'}
              </span>
            </div>
          </li>
        </ul>

        <div id="copyright" className="mt-[18px] mb-[5px] text-sm text-[#666]">
          &copy; {ano ? ano : '2026'} Vértice Mental. Por Gabriel Gonçalves.
        </div>
      </div>
    </footer>
  );
}
