<?php
require_once '../components/conexao.php';

$busca = $_GET['busca'] ?? '';

try {
    if (!empty($busca)) {
        // Filtra os artigos pelo título ou subtítulo
        $query = $conexao->prepare("SELECT * FROM artigos WHERE titulo LIKE :busca OR subtitulo LIKE :busca ORDER BY data_criacao DESC");
        $query->bindValue(':busca', '%' . $busca . '%');
        $query->execute();
    } else {
        // Busca todos os artigos
        $query = $conexao->query("SELECT * FROM artigos ORDER BY data_criacao DESC");
    }
    $artigos = $query->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar artigos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Vértice Mental é um blog sobre filosofia, cultura, espiritualidade, tecnologia e ideias que se conectam.">
	<meta name="author" content="Gabriel Gonçalves">

	<title>Todos os Artigos | Vértice Mental</title>

	<link rel="icon" href="../src/images/favicon-vertice-mental.ico" type="image/x-icon">

	<!-- Conexão com o Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="../assets/css/style.css">
	<script src="../assets/js/scripts.js" type="module"></script>
    
    <style>
        /* Estilos específicos para a barra de busca e cabeçalho da página de artigos */
        .busca-container {
            text-align: center;
            margin: 30px auto;
        }
        .busca-container input[type="text"] {
            width: 300px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .busca-container button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #6a0dad;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        .busca-container button:hover {
            background-color: #8b5fbf;
        }
        .titulo-pagina {
            margin-top: 40px;
            margin-bottom: 10px;
        }
        /* Ajuste do espaço entre busca e artigos */
        #artigos .container {
            margin-top: 20px;
        }
        /* Ajuste do espaço entre os artigos e o footer */
        #artigos {
            margin-bottom: 80px !important;
        }
    </style>
</head>

<body>

	<meu-cabecalho></meu-cabecalho>

	<main>
		<h1 class="titulo-pagina">Todos os Artigos</h1>

        <!-- Barra de Busca -->
        <div class="busca-container">
            <form action="artigos.php" method="GET">
                <input type="text" name="busca" placeholder="Buscar por palavra-chave..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <section id="artigos">
            <div class="container">
                <?php if (empty($artigos)): ?>
                    <p style="text-align: center; width: 100%; margin-top: 30px;">
                        <?php echo !empty($busca) ? "Nenhum artigo encontrado para '<strong>" . htmlspecialchars($busca) . "</strong>'." : "Ainda não há artigos publicados."; ?>
                    </p>
                <?php else: ?>
                    <?php foreach ($artigos as $artigo): ?>
                        <article>
                            <figure>
                                <?php 
                                $caminhoImagem = (strpos($artigo['imagem'], 'http') === 0) 
                                    ? $artigo['imagem'] 
                                    : "../src/images/uploads/" . $artigo['imagem'];
                                ?>
                                <img src="<?php echo htmlspecialchars($caminhoImagem); ?>" alt="Imagem de capa do artigo: <?php echo htmlspecialchars($artigo['titulo']); ?>" width="390">
                            </figure>	
                        
                            <section class="titulo-main-article">
                                <h3><?php echo htmlspecialchars($artigo['titulo']); ?></h3>
                            </section>

                            <p>
                                <?php echo htmlspecialchars($artigo['subtitulo']); ?>
                            </p>
                            
                            <hr>

                            <div class="botao-main-article">
                                <h3>
                                    <a href="artigo.php?slug=<?php echo htmlspecialchars($artigo['slug']); ?>">Ler artigo completo</a>
                                </h3>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div> 
        </section>
	</main>

	<meu-rodape></meu-rodape>

</body>
</html>
