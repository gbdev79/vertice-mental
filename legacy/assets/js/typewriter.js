// ==========================================
// EFEITO MÁQUINA DE ESCREVER (TYPEWRITER)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const elementoTexto = document.querySelector('#capa p');
    if (!elementoTexto) return;

    // 1. Guardamos o texto completo que veio do HTML e removemos espaços extras
    const textoCompleto = elementoTexto.textContent.trim();
    
    // 2. Limpamos o elemento para ele começar vazio
    elementoTexto.textContent = '';
    
    let indice = 0;
    const velocidadeDigitação = 50; // Tempo em milissegundos entre cada letra

    // 3. Função responsável por digitar letra por letra recursivamente
    function digitar() {
        if (indice < textoCompleto.length) {
            elementoTexto.textContent += textoCompleto.charAt(indice);
            indice++;
            setTimeout(digitar, velocidadeDigitação);
        } else {
            // Quando termina de digitar, adiciona a classe que remove o cursor
            elementoTexto.classList.remove('digitando');
            elementoTexto.classList.add('digitado-completo');
        }
    }

    // 4. intersectionObserver: Só inicia a digitação quando o elemento estiver visível na tela
    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach(entrada => {
            if (entrada.isIntersecting) {
                // Adiciona a classe que faz o cursor piscar
                elementoTexto.classList.add('digitando');
                
                // Dá um pequeno delay de 500ms antes de começar a digitar de fato
                setTimeout(digitar, 500);
                
                // Para de observar o elemento para a digitação não reiniciar se o usuário rolar a tela de novo
                observador.unobserve(elementoTexto);
            }
        });
    }, {
        threshold: 0.5 // Só dispara quando pelo menos 50% do elemento estiver visível na tela
    });

    // Inicia a observação no elemento do texto
    observador.observe(elementoTexto);
});