<?php
require_once '../components/conexao.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    header("Location: loja.php");
    exit;
}

try {
    $stmt = $conexao->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        header("Location: loja.php");
        exit;
    }

    // Busca produtos recomendados (da mesma categoria, exceto ele mesmo, limit 4)
    $stmtRecomendados = $conexao->prepare("SELECT * FROM produtos WHERE categoria = :categoria AND id != :id ORDER BY RAND() LIMIT 4");
    $stmtRecomendados->execute([':categoria' => $produto['categoria'], ':id' => $produto['id']]);
    $recomendados = $stmtRecomendados->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar o produto: " . $e->getMessage());
}

$caminhoImagem = (strpos($produto['imagem'], 'http') === 0) 
    ? $produto['imagem'] 
    : "../src/images/uploads/" . $produto['imagem'];

$precoParcela = $produto['preco'] / 12;
$parcelaFormatada = number_format($precoParcela, 2, ',', '');
$partes = explode(',', $parcelaFormatada);
$reais = $partes[0];
$centavos = $partes[1];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo htmlspecialchars(substr($produto['descricao'], 0, 150)); ?>...">
	<title><?php echo htmlspecialchars($produto['nome']); ?> | Vértice Mental</title>

	<link rel="icon" href="../src/images/favicon-vertice-mental.ico" type="image/x-icon">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="../assets/css/style.css">
	<script src="../assets/js/scripts.js" defer type="module"></script>

    <style>
        .container-pagina-produto {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* GRID PRINCIPAL */
        .produto-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 50px;
        }

        @media (max-width: 768px) {
            .produto-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }

        /* ESQUERDA: IMAGEM */
        .produto-galeria {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .produto-imagem-grande {
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            object-fit: contain;
            border: 1px solid #eaeaea;
            padding: 20px;
            transition: transform 0.3s;
        }
        .produto-imagem-grande:hover {
            transform: scale(1.05); /* Efeito zoom */
        }

        /* DIREITA: INFO */
        .produto-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .produto-info .categoria-tag {
            background-color: rgba(106, 13, 173, 0.1);
            color: #6a0dad;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .produto-info h1 {
            font-size: 28px;
            color: #1a1a1a;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .estrelas {
            color: #6a0dad; /* Roxo do site em vez de amarelo amazon */
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .estrelas span.avaliacoes-cont {
            color: #8b5fbf;
            font-size: 14px;
            margin-left: 10px;
            cursor: pointer;
        }
        .estrelas span.avaliacoes-cont:hover {
            text-decoration: underline;
        }
        
        hr.divisor-info {
            width: 100%;
            border: 0;
            height: 1px;
            background: #eaeaea;
            margin: 20px 0;
        }

        .preco-bloco {
            margin-bottom: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .preco-bloco .em-ate {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
        }
        .preco-destaque {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: #1a1a1a; /* Escuro premium */
        }
        .preco-destaque .cifrao {
            font-size: 20px;
            margin-top: 8px;
            margin-right: 5px;
        }
        .preco-destaque .reais {
            font-size: 56px; /* Aumentado bastante */
            font-weight: 800; /* Mais grosso */
            line-height: 1;
        }
        .preco-destaque .centavos {
            font-size: 20px;
            margin-top: 8px;
        }
        .preco-vista {
            font-size: 16px;
            color: #6a0dad; /* Roxo para destaque secundário */
            font-weight: 600;
            margin-top: 10px;
        }

        /* FRETE - Mais compacto e elegante */
        .frete-container {
            background: #fff;
            padding: 0;
            margin-bottom: 25px;
            border: none;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .frete-container h4 {
            font-size: 13px;
            margin-bottom: 8px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .frete-calc {
            display: flex;
            gap: 0;
            max-width: 300px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            margin: 0 auto;
        }
        .frete-calc input {
            flex: 1;
            padding: 8px 12px;
            border: none;
            font-size: 14px;
            outline: none;
        }
        .frete-calc button {
            background: #f5f5f5;
            border: none;
            border-left: 1px solid #ddd;
            color: #333;
            padding: 8px 15px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s;
        }
        .frete-calc button:hover {
            background: #e9e9e9;
        }
        #frete-resultado {
            margin-top: 10px;
            font-size: 13px;
            color: #6a0dad; /* Roxo no resultado */
            font-weight: 600;
            display: none;
        }

        /* PAGAMENTO E COMPRA */
        .formas-pagamento {
            margin-bottom: 25px;
            font-size: 14px;
            color: #555;
        }
        .btn-comprar-oficial {
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%); /* Gradiente premium da loja */
            color: white;
            border: none;
            padding: 16px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
        }
        .btn-comprar-oficial:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 13, 173, 0.5);
        }

        /* SEÇÃO DE DESCRIÇÃO E RECOMENDADOS */
        .secao-secundaria {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 50px;
        }
        .secao-secundaria h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #1a1a1a;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 10px;
        }
        .descricao-texto {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            text-align: justify;
        }

        /* RECOMENDADOS (Mantendo a identidade da loja) */
        .grid-recomendados {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .card-recomendado {
            width: 250px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 1px solid transparent;
        }
        .card-recomendado:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(106, 13, 173, 0.2);
        }
        .card-recomendado img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        .card-recomendado h4 {
            font-size: 16px;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 38px;
            color: #1a1a1a;
            font-weight: 600;
        }
        .card-recomendado .preco-rec {
            font-size: 18px;
            font-weight: bold;
            color: #6a0dad;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .card-recomendado {
                width: calc(50% - 10px);
            }
        }

        /* AVALIAÇÕES MOCKADAS */
        .lista-avaliacoes {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .avaliacao-item {
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 20px;
        }
        .avaliacao-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .avaliacao-header .avatar {
            width: 34px;
            height: 34px;
            background: #e3e3e3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #555;
        }
        .avaliacao-header .nome {
            font-weight: 600;
            font-size: 15px;
        }
        .avaliacao-nota {
            color: #6a0dad; /* Estrelas roxas */
            font-size: 14px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .avaliacao-nota span {
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }
        .avaliacao-texto {
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }

    </style>
</head>

<body>

	<meu-cabecalho></meu-cabecalho>

	<main class="container-pagina-produto">

        <!-- Grid Produto (Cima) -->
        <section class="produto-grid">
            
            <div class="produto-galeria">
                <img src="<?php echo htmlspecialchars($caminhoImagem); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="produto-imagem-grande">
            </div>

            <div class="produto-info">
                <span class="categoria-tag"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                
                <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
                
                <div class="estrelas">
                    ★ ★ ★ ★ ★ 
                    <span class="avaliacoes-cont">14 avaliações de clientes</span>
                </div>

                <hr class="divisor-info">

                <div class="preco-bloco">
                    <div class="em-ate">em até 12x de</div>
                    <div class="preco-destaque">
                        <span class="cifrao">R$</span>
                        <span class="reais"><?php echo $reais; ?></span>
                        <span class="centavos">,<?php echo $centavos; ?></span>
                    </div>
                    <div class="preco-vista">
                        ou à vista por R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                    </div>
                </div>

                <div class="frete-container">
                    <h4>Calcular Frete e Prazo</h4>
                    <div class="frete-calc">
                        <input type="text" id="cep-input" placeholder="Digite seu CEP" maxlength="9">
                        <button type="button" onclick="calcularFrete()">Calcular</button>
                    </div>
                    <div id="frete-resultado">
                        ✓ Frete Grátis | Entrega estimada entre 5 e 7 dias úteis.
                    </div>
                </div>

                <div class="formas-pagamento">
                    🔒 Pagamento seguro via Cartão de Crédito ou Pix.
                </div>

                <a href="<?php echo htmlspecialchars($produto['link_compra']); ?>" target="_blank" class="btn-comprar-oficial">
                    COMPRAR AGORA
                </a>

            </div>

        </section>

        <!-- Descrição (Baixo) -->
        <section class="secao-secundaria">
            <h2>Descrição do Produto</h2>
            <div class="descricao-texto">
                <!-- Usando nl2br para quebrar linhas digitadas no textarea do painel -->
                <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
            </div>
        </section>

        <!-- Produtos Recomendados -->
        <?php if (!empty($recomendados)): ?>
        <section class="secao-secundaria">
            <h2>Produtos Recomendados</h2>
            <div class="grid-recomendados">
                <?php foreach ($recomendados as $rec): 
                    $caminhoImgRec = (strpos($rec['imagem'], 'http') === 0) 
                        ? $rec['imagem'] 
                        : "../src/images/uploads/" . $rec['imagem'];
                ?>
                <a href="produto.php?id=<?php echo $rec['id']; ?>" class="card-recomendado">
                    <img src="<?php echo htmlspecialchars($caminhoImgRec); ?>" alt="<?php echo htmlspecialchars($rec['nome']); ?>">
                    <h4 title="<?php echo htmlspecialchars($rec['nome']); ?>"><?php echo htmlspecialchars($rec['nome']); ?></h4>
                    <div class="preco-rec">R$ <?php echo number_format($rec['preco'], 2, ',', '.'); ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Avaliações Simuladas -->
        <section class="secao-secundaria">
            <h2>Avaliações de Clientes</h2>
            <div class="lista-avaliacoes">
                
                <div class="avaliacao-item">
                    <div class="avaliacao-header">
                        <div class="avatar">M</div>
                        <div class="nome">Marcos Silva</div>
                    </div>
                    <div class="avaliacao-nota">
                        ★ ★ ★ ★ ★ <span>Excelente qualidade!</span>
                    </div>
                    <div class="avaliacao-texto">
                        Produto superou minhas expectativas. Material de primeira e a entrega foi super rápida. Recomendo com certeza!
                    </div>
                </div>

                <div class="avaliacao-item">
                    <div class="avaliacao-header">
                        <div class="avatar">J</div>
                        <div class="nome">Juliana M.</div>
                    </div>
                    <div class="avaliacao-nota">
                        ★ ★ ★ ★ ☆ <span>Muito bom, chegou certinho</span>
                    </div>
                    <div class="avaliacao-texto">
                        Gostei bastante do produto, a única ressalva é que a caixa veio um pouco amassada, mas o item por dentro estava impecável. Compraria novamente.
                    </div>
                </div>

                <div class="avaliacao-item">
                    <div class="avaliacao-header">
                        <div class="avatar">R</div>
                        <div class="nome">Rafael Costa</div>
                    </div>
                    <div class="avaliacao-nota">
                        ★ ★ ★ ★ ★ <span>Exatamente o que eu procurava</span>
                    </div>
                    <div class="avaliacao-texto">
                        Tamanho perfeito e o design é exatamente igual ao da foto. Sensacional a atenção aos detalhes.
                    </div>
                </div>

            </div>
        </section>

	</main>

	<meu-rodape></meu-rodape>

    <script>
        // Lógica simulada de frete
        function calcularFrete() {
            const cep = document.getElementById('cep-input').value.replace(/\D/g, '');
            const resultado = document.getElementById('frete-resultado');
            
            if (cep.length === 8) {
                resultado.style.display = 'block';
                resultado.style.color = '#6a0dad';
                resultado.innerHTML = '✓ <strong>Frete Grátis</strong> | Entrega estimada: ' + (Math.floor(Math.random() * 5) + 3) + ' a ' + (Math.floor(Math.random() * 5) + 8) + ' dias úteis.';
            } else {
                resultado.style.display = 'block';
                resultado.style.color = '#d9534f';
                resultado.innerHTML = '⚠ Por favor, insira um CEP válido.';
                setTimeout(() => {
                    resultado.style.display = 'none';
                    resultado.style.color = '#6a0dad';
                }, 3000);
            }
        }

        // Mascara CEP
        document.getElementById('cep-input').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    </script>

</body>
</html>
