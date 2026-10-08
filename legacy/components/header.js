class MeuCabecalho extends HTMLElement {

    connectedCallback() {
        // Usa o caminho do próprio módulo para descobrir a raiz do projeto (voltando 1 pasta: de components/ para a raiz)
        const baseUrl = new URL('../', import.meta.url).href;

        this.innerHTML = `
            <header id="home">
                <div class="container-header">
                    <div>
                        <a href="${baseUrl}index.php">
                            <img src="${baseUrl}src/images/logo-vertice-mental.webp" alt="Logo do Vértice Mental" width="120">
                        </a>
                    </div>

                    <nav aria-label="Menu principal">
                        <ul id="menu-principal">
                            <li><a href="${baseUrl}index.php">Início</a></li>
                            <li><a href="${baseUrl}pages/artigos.php">Artigos</a></li>
                            <li><a href="${baseUrl}pages/loja.php">Loja</a></li>
                            <li><a href="${baseUrl}pages/sobre.html">Sobre</a></li>
                            <li><a href="${baseUrl}index.php#contato">Contato</a></li>
                        </ul>
                    </nav>
                    <nav aria-label="Menu Redes Sociais">
                        <ul id="menu-social">	
                            <li>
                                <a href="https://instagram.com/overticemental/" target="_blank">
                                    <img src="${baseUrl}src/images/instagram-logo.webp" alt="Instagram" width="20">
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>	
            </header>
        `;
    }
}

customElements.define('meu-cabecalho', MeuCabecalho);