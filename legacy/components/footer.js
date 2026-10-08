class MeuRodape extends HTMLElement {

    connectedCallback() {
        // Usa o caminho do próprio módulo para descobrir a raiz do projeto (voltando 1 pasta: de components/ para a raiz)
        const baseUrl = new URL('../', import.meta.url).href;

        this.innerHTML =`
            <footer id="contato">
                <div class="container-footer">
                    <h2>Entre em Contato</h2>

                    <hr>

                    <nav aria-label="Links de contato">
                        <ul>
                            <li>
                                <a href="https://youtube.com" target="_blank">
                                    <img src="${baseUrl}src/images/youtube-logo.webp" alt="Logo do Youtube" width="30">
                                    Canal do Youtube 
                                </a>
                            </li>

                            <li>
                                <a href="https://instagram.com" target="_blank">
                                    <img src="${baseUrl}src/images/instagram-logo.webp" alt="Logo do Instagram" width="30">
                                    Siga no Instagram
                                </a>
                            </li>
                            <li>
                                <a href="#" id="copiar-email">
                                    <img src="${baseUrl}src/images/email.webp" alt="Logo do Email" width="30">
                                    contato@verticemental.com.br

                                    <span class="tooltip-copiar">Clique para copiar</span>

                                </a>
                            </li>
                        </ul>
                    </nav>

                    <p id="copyright">Todos os direitos reservados a Vértice Mental &#169; 2026</p>
                </div> 
            </footer>
        `;

        // Lógica para copiar o endereço de e-mail ao clicar
        const botaoCopiar = this.querySelector('#copiar-email');
        const tooltip = this.querySelector('.tooltip-copiar');
        const email = 'contato@verticemental.com.br';

        if (botaoCopiar) {
            botaoCopiar.addEventListener('click', (event) => {
                event.preventDefault();

                navigator.clipboard.writeText(email).then(() => {
                    if (tooltip) {
                        tooltip.textContent = 'Copiado!';
                        tooltip.classList.add('copiado');

                        setTimeout(() => {
                            tooltip.textContent = 'Clique para copiar';
                            tooltip.classList.remove('copiado');
                        }, 2000);
                    }
                }).catch(err => {
                    console.error('Erro ao copiar e-mail: ', err);
                });
            });
        }
    }
}

customElements.define('meu-rodape', MeuRodape);