<?php
// Importa a conexão com o banco (voltando uma pasta com ../)
require_once '../components/conexao.php';

$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';

try {
    // Busca categorias únicas para o filtro
    $stmtCat = $conexao->query("SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

    // Constrói a query de produtos baseada nos filtros
    $sql = "SELECT * FROM produtos WHERE 1=1";
    $params = [];

    if (!empty($busca)) {
        $sql .= " AND (nome LIKE :busca OR descricao LIKE :busca)";
        $params[':busca'] = "%$busca%";
    }

    if (!empty($categoria)) {
        $sql .= " AND categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    $sql .= " ORDER BY data_criacao DESC";
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar produtos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Loja Oficial do blog Vértice Mental. Camisetas, canecas e moletons exclusivos.">
	<meta name="author" content="Gabriel Gonçalves">

	<title>Loja | Vértice Mental</title>

	<link rel="icon" href="../src/images/favicon-vertice-mental.ico" type="image/x-icon">

	<!-- Conexão com o Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="../assets/css/style.css">
	<script src="../assets/js/scripts.js" defer type="module"></script>

    <style>
        .filtro-loja {
            background-color: #1e1f29;
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 800px;
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .filtro-loja input, .filtro-loja select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #444;
            background: #15161f;
            color: #fff;
            font-size: 14px;
        }
        .filtro-loja button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filtro-loja button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.4);
        }
        .produto {
            width: 300px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .produto:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .produto h3 {
            font-size: 18px;
            margin: 15px 0 10px;
            color: #1a1a1a;
            
            /* Truncar título em 2 linhas com reticências */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 44px; /* Mantém a altura fixa para alinhar os outros elementos */
        }
        
        .produto-imagem-container {
            width: 100%;
            height: 220px; /* Mais espaço para a foto */
            overflow: hidden; /* Necessário para o zoom não vazar do box */
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .produto-imagem-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.4s ease;
        }
        
        /* Zoom In ao passar o mouse por cima do card do produto */
        .produto:hover .produto-imagem-container img {
            transform: scale(1.1);
        }
        .produto hr {
            width: 50%;
            margin: 10px auto;
        }
        .categoria-tag {
            background-color: rgba(106, 13, 173, 0.1);
            color: #6a0dad;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .button-comprar-produto {
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 15px;
            width: 100%;
            transition: all 0.3s;
        }
        .button-comprar-produto:hover {
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.4);
            transform: scale(1.05);
        }
        .produto-footer {
            margin-top: auto; /* Empurra o botão e o conteúdo final para baixo */
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>

<body>

	<meu-cabecalho></meu-cabecalho>

	<main>

		<!-- Capa da loja -->
		<section id="capa-loja">
			<h1>Bem-vindo(a) à nossa Loja Oficial</h1>

			<div>
				<hr>
				<h2>Produtos Exclusivos</h2>
				<hr>
			</div>

			<figure style="text-align: center;">
				<!-- Ajustado caminho da imagem de banner -->
				<img src="../src/images/banner-vertice-mental.webp" alt="Banner da loja Vértice Mental" width="720" style="border-radius: 28px; max-width: 100%; height: auto;">
			</figure>
		</section>

        <!-- Filtro de Busca e Categorias -->
        <form class="filtro-loja" action="loja.php" method="GET">
            <input type="text" name="busca" placeholder="Buscar produto..." value="<?php echo htmlspecialchars($busca); ?>">
            <select name="categoria">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>

		<section id="section-produtos">

			<div id="produtos" class="container" style="display: flex; gap: 40px; justify-content: center; flex-wrap: wrap; margin-top: 50px;">
				
				<?php if (empty($produtos)): ?>
					<p style="text-align: center; font-style: italic; color: #888; width: 100%; margin: 50px 0;">
						<?php echo !empty($busca) || !empty($categoria) ? "Nenhum produto encontrado para o filtro selecionado." : "Nenhum produto disponível no momento."; ?>
					</p>
				<?php else: ?>
					<?php foreach ($produtos as $produto): ?>
						<div class="produto">
							
							<?php 
							$caminhoImagem = (strpos($produto['imagem'], 'http') === 0) 
								? $produto['imagem'] 
								: "../src/images/uploads/" . $produto['imagem'];
							
							$precoParcela = $produto['preco'] / 12;
							$parcelaFormatada = number_format($precoParcela, 2, ',', '');
							$partes = explode(',', $parcelaFormatada);
							$reais = $partes[0];
							$centavos = $partes[1];
							?>

							<div class="produto-imagem-container">
							    <a href="produto.php?id=<?php echo $produto['id']; ?>">
							        <img src="<?php echo htmlspecialchars($caminhoImagem); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
							    </a>
							</div>
							
							<span class="categoria-tag"><?php echo htmlspecialchars($produto['categoria']); ?></span>
							<h3 title="<?php echo htmlspecialchars($produto['nome']); ?>"><?php echo htmlspecialchars($produto['nome']); ?></h3>
							
							<hr>
							
							<div class="produto-footer">
                                <p>em até 12x de</p>
                                
                                <div style="display: flex; justify-content: center; align-items: flex-start; margin: 10px 0;">
                                    <p class="cifrao-produto" style="margin: 0 2px;">R$</p>
                                    <p class="preço-produto" style="margin: 0 2px; font-size: 32px; font-weight: bold; line-height: 1;"><?php echo $reais; ?></p>
                                    <p class="centavos-produto" style="margin: 0 2px; font-size: 14px; font-weight: bold; line-height: 1.2;">,<?php echo $centavos; ?></p>
                                </div>
                                
                                <p style="margin-bottom: 15px;">ou à vista por R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                
                                <a href="produto.php?id=<?php echo $produto['id']; ?>" style="text-decoration: none; width: 100%;">
                                    <button class="button-comprar-produto" style="cursor: pointer; margin-top: 0;">VER PRODUTO</button>
                                </a>
                            </div>

						</div>
					<?php endforeach; ?>
				<?php endif; ?>

			</div>

		</section>

	</main>

	<meu-rodape></meu-rodape>

</body>
</html>