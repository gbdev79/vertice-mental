<?php
// Inicialização de banco e busca de artigos recentes
require_once 'components/conexao.php';

try {
    $query = $conexao->query("SELECT * FROM artigos ORDER BY data_criacao DESC LIMIT 4");
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

	<title>Vértice Mental</title>

	<link rel="icon" href="./src/images/favicon-vertice-mental.ico" type="image/x-icon">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="./assets/css/style.css">
	<script src="./assets/js/scripts.js" defer type="module"></script>
</head>

<body>

	<meu-cabecalho></meu-cabecalho>

	<main>

		<!-- Capa do Blog -->
		<section id="capa">
			<h1>Bem-vindo(a) ao Vértice Mental</h1>

			<figure>
				<img src="./src/images/banner-vertice-mental.webp" alt="Banner do blog Vértice Mental com símbolo geométrico e visual tecnológico" width="720">
				<figcaption>
					Um espaço para conectar filosofia, cultura, espiritualidade, tecnologia e ideias. 
				</figcaption>
			</figure>

			<p>
				&#8220;Aqui, pensamentos se encontram, referências se cruzam e novos vértices mentais surgem.&#8221;
			</p>
		</section>

		<hr>

		<!-- Artigos Recentes -->
		<section id="artigos">
			<h2>Últimos Artigos</h2>

			<p>
				Confira as reflexões mais recentes publicadas no Vértice Mental.
			</p>

			<div class="container">
				<?php if (empty($artigos)): ?>
					<!-- Fallback caso não haja artigos cadastrados -->
					<p style="text-align: center; font-style: italic; color: #666; width: 100%; margin: 40px 0;">
						Nenhum artigo publicado ainda. Acesse a área administrativa para cadastrar o seu primeiro artigo!
					</p>
				<?php else: ?>
					<?php foreach ($artigos as $artigo): ?>
						<article>
							<figure>
								<?php 
								$caminhoImagem = (strpos($artigo['imagem'], 'http') === 0) 
									? $artigo['imagem'] 
									: "./src/images/uploads/" . $artigo['imagem'];
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
									<a href="./pages/artigo.php?slug=<?php echo htmlspecialchars($artigo['slug']); ?>">Ler artigo completo</a>
								</h3>
							</div>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div> 

			<section id="todos-artigos">
				<hr>
				<h3>
					<a href="./pages/artigos.php">Ver todos os artigos publicados</a>
				</h3>
				<hr>
			</section>

		</section>

	</main> 

		<section id="newsletter">
		<div><h2>Seja avisado de novos artigos</h2></div>

		<hr>

		<div id="formulario">
			<form action="#" method="post">
				<fieldset>
					<p>
						<input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
					</p>

					<p>
						<input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>
					</p>

					<p>
						<button type="submit">Cadastrar</button>
					</p>
				</fieldset>
			</form>
		</div>
	</section>

	<meu-rodape></meu-rodape>

</body>
</html>
