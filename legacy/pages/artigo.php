<?php
// Importa o arquivo de conexão (voltando uma pasta para achar o components)
require_once '../components/conexao.php';

// Verifica se o parâmetro 'slug' foi passado na URL (ex: artigo.php?slug=as-7-leis)
if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = $_GET['slug'];

    try {
        // Busca o artigo correspondente ao slug no banco
        $stmt = $conexao->prepare("SELECT * FROM artigos WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $artigo = $stmt->fetch();

        // Se o artigo existir, incrementa o contador de visualizações
        if ($artigo) {
            $stmtUpdate = $conexao->prepare("UPDATE artigos SET visualizacoes = visualizacoes + 1 WHERE id = :id");
            $stmtUpdate->execute([':id' => $artigo['id']]);
        } else {
            // Se o artigo não existir, redireciona para a home
            header("Location: ../index.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Erro ao carregar o artigo: " . $e->getMessage());
    }
} else {
    // Se não passar o slug na URL, redireciona para a home
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($artigo['subtitulo']); ?>">
    <title><?php echo htmlspecialchars($artigo['titulo']); ?> | Vértice Mental</title>

    <link rel="icon" href="../src/images/favicon-vertice-mental.ico" type="image/x-icon">

    <!-- Conexão com o Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <!-- Como estamos na pasta /pages/, subimos uma pasta para achar o CSS e o JS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/scripts.js" defer type="module"></script>
    
    <style>
        /* Estilos específicos para a página do artigo ficar bonita e focada em leitura */
        .artigo-container {
            max-width: 800px; /* Largura mais estreita e confortável para leitura */
            margin: 60px auto;
            padding: 0 20px;
        }
        
        .artigo-meta {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
            font-family: 'Inter', sans-serif;
        }
        
        .artigo-meta span {
            margin-right: 15px;
        }
        
        .artigo-titulo {
            font-size: 42px;
            text-align: left;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .artigo-subtitulo {
            font-size: 20px;
            color: #555;
            font-style: italic;
            margin-bottom: 40px;
            line-height: 1.4;
        }
        
        .artigo-capa {
            width: 100%;
            height: auto;
            max-height: 450px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Onde o texto principal será renderizado */
        .artigo-texto {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #2b2b2b;
            text-align: justify;
        }
        
        /* Espaçamento dos parágrafos internos que o usuário cadastrou */
        .artigo-texto p {
            margin-bottom: 25px;
        }
        
        /* Estilo para links ou interações dentro do texto */
        .artigo-texto a {
            color: #6a0dad;
            text-decoration: underline;
        }
        
        .btn-voltar-artigos {
            display: inline-block;
            margin-top: 40px;
            font-family: 'Inter', sans-serif;
            color: #6a0dad;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-voltar-artigos:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <meu-cabecalho></meu-cabecalho>

    <main class="artigo-container">
        
        <div class="artigo-meta">
            <!-- Formata a data para exibir no formato brasileiro -->
            <span>📅 Publicado em: <?php echo date('d/m/Y', strtotime($artigo['data_criacao'])); ?></span>
            <span>👁 <?php echo $artigo['visualizacoes'] + 1; ?> visualizações</span>
        </div>

        <h1 class="artigo-titulo"><?php echo htmlspecialchars($artigo['titulo']); ?></h1>
        <p class="artigo-subtitulo"><?php echo htmlspecialchars($artigo['subtitulo']); ?></p>

        <?php 
        $caminhoImagem = (strpos($artigo['imagem'], 'http') === 0) 
            ? $artigo['imagem'] 
            : "../src/images/uploads/" . $artigo['imagem'];
        ?>
        <img class="artigo-capa" src="<?php echo htmlspecialchars($caminhoImagem); ?>" alt="Capa de: <?php echo htmlspecialchars($artigo['titulo']); ?>">

        <!-- 
           Exibe o conteúdo completo do post. 
           Usamos o echo comum (sem htmlspecialchars) porque queremos que o navegador 
           interprete tags HTML como <p>, <strong> e <h2> que você cadastrou no formulário.
        -->
        <div class="artigo-texto">
            <?php echo $artigo['conteudo']; ?>
        </div>

        <a href="artigos.php" class="btn-voltar-artigos">← Ver todos os artigos</a>

    </main>

    <meu-rodape></meu-rodape>

</body>
</html>