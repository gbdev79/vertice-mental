<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../components/conexao.php';

$mensagem = '';
$sucesso = true;

// Variáveis de controle do formulário
$id = 0;
$nome = '';
$descricao = '';
$preco = '';
$linkCompra = '';
$categoria = '';
$imagemAtual = '';
$modoEdicao = false;

// Busca os dados do produto em caso de edição
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $modoEdicao = true;

    try {
        $stmt = $conexao->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch();

        if ($produto) {
            $nome = $produto['nome'];
            $descricao = $produto['descricao'];
            $preco = number_format($produto['preco'], 2, ',', ''); 
            $linkCompra = $produto['link_compra'];
            $categoria = $produto['categoria'];
            $imagemAtual = $produto['imagem'];
        } else {
            header("Location: produtos.php");
            exit;
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao carregar produto: " . $e->getMessage();
        $sucesso = false;
    }
}

// Processa o envio do formulário (Salvar/Atualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificação de limite de tamanho de envio (post_max_size)
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $mensagem = "A imagem enviada é muito grande! Por favor, envie uma foto menor que 2MB.";
        $sucesso = false;
    } else {
        // Obtenção dos dados do formulário
        $id = (int)$_POST['id'];
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $precoInput = trim($_POST['preco']);
        $linkCompra = trim($_POST['link_compra']);
        $categoria = trim($_POST['categoria']);
        $imagemAtual = $_POST['imagem_atual'];
        $modoEdicao = $id > 0;

    if (!empty($nome) && !empty($descricao) && !empty($precoInput) && !empty($linkCompra) && !empty($categoria)) {
        
        // Formatação do preço para padrão internacional
        $precoFloat = (float)str_replace(['.', ','], ['', '.'], $precoInput);
        $nomeFinalImagem = $imagemAtual; 

        // Tratamento do upload de imagem do produto
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $arqNome = $_FILES['imagem']['name'];
            $arqTmp = $_FILES['imagem']['tmp_name'];
            $arqExtensao = strtolower(pathinfo($arqNome, PATHINFO_EXTENSION));
            
            $extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($arqExtensao, $extensoesValidas)) {
                $nomeFinalImagem = uniqid() . '.' . $arqExtensao;
                $pastaDestino = '../src/images/uploads/' . $nomeFinalImagem;

                if (move_uploaded_file($arqTmp, $pastaDestino)) {
                    // Exclui imagem anterior em caso de substituição
                    if ($modoEdicao && !empty($imagemAtual)) {
                        $fotoAntiga = '../src/images/uploads/' . $imagemAtual;
                        if (file_exists($fotoAntiga)) {
                            unlink($fotoAntiga);
                        }
                    }
                } else {
                    $mensagem = "Falha ao salvar a imagem no servidor.";
                    $sucesso = false;
                }
            } else {
                $mensagem = "Formato de imagem inválido. Aceitos: JPG, JPEG, PNG e WEBP.";
                $sucesso = false;
            }
        } elseif (!$modoEdicao && empty($nomeFinalImagem)) {
            $mensagem = "Por favor, envie uma foto do produto.";
            $sucesso = false;
        }

        // Persistência no banco de dados
        if ($sucesso) {
            try {
                if ($modoEdicao) {
                    $stmt = $conexao->prepare("UPDATE produtos SET nome = :nome, descricao = :descricao, preco = :preco, imagem = :imagem, link_compra = :link_compra, categoria = :categoria WHERE id = :id");
                    $stmt->execute([
                        ':nome' => $nome,
                        ':descricao' => $descricao,
                        ':preco' => $precoFloat,
                        ':imagem' => $nomeFinalImagem,
                        ':link_compra' => $linkCompra,
                        ':categoria' => $categoria,
                        ':id' => $id
                    ]);
                    header("Location: produtos.php?msg=editado");
                    exit;
                } else {
                    $stmt = $conexao->prepare("INSERT INTO produtos (nome, descricao, preco, imagem, link_compra, categoria) VALUES (:nome, :descricao, :preco, :imagem, :link_compra, :categoria)");
                    $stmt->execute([
                        ':nome' => $nome,
                        ':descricao' => $descricao,
                        ':preco' => $precoFloat,
                        ':imagem' => $nomeFinalImagem,
                        ':link_compra' => $linkCompra,
                        ':categoria' => $categoria
                    ]);
                    header("Location: produtos.php?msg=cadastrado");
                    exit;
                }
            } catch (PDOException $e) {
                $mensagem = "Erro ao salvar no banco de dados: " . $e->getMessage();
                $sucesso = false;
            }
        }

    } else {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $sucesso = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modoEdicao ? 'Editar' : 'Novo'; ?> Produto - Vértice Mental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #12131a;
            color: #fff;
            margin: 0;
            display: flex;
        }
        
        /* Menu Lateral (Igual ao do painel) */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #1e1f29;
            border-right: 1px solid rgba(177, 156, 217, 0.1);
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand {
            padding: 30px 20px;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: #b19cd9;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
        }
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: #fff;
            background-color: rgba(106, 13, 173, 0.2);
            border-left: 4px solid #6a0dad;
        }
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .btn-logout {
            color: #ff6666;
            text-decoration: none;
            font-size: 14px;
        }
        
        /* Área de Conteúdo */
        .content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            min-height: 100vh;
            box-sizing: border-box;
        }
        
        h1 {
            font-size: 28px;
            color: #b19cd9;
            margin-top: 0;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            background-color: rgba(255, 51, 51, 0.15);
            border: 1px solid #ff3333;
            color: #ff6666;
        }
        
        .form-container {
            background-color: #1e1f29;
            border-radius: 12px;
            border: 1px solid rgba(177, 156, 217, 0.1);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            color: #b19cd9;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input[type="text"], .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 8px;
            background: #15161f;
            color: #fff;
            box-sizing: border-box;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #b19cd9;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .preview-imagem {
            margin-top: 10px;
            max-width: 150px;
            max-height: 150px;
            border-radius: 6px;
            border: 1px solid #444;
            display: block;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-salvar {
            padding: 12px 25px;
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
            transition: all 0.3s;
        }
        
        .btn-salvar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 13, 173, 0.5);
        }
        
        .btn-cancelar {
            padding: 12px 25px;
            background-color: #333;
            color: #ccc;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
            text-align: center;
        }
        .btn-cancelar:hover {
            background-color: #444;
            color: #fff;
        }
    </style>
</head>
<body>

    <!-- Menu Lateral -->
    <div class="sidebar">
        <div>
            <div class="sidebar-brand">Vértice Mental</div>
            <ul class="sidebar-menu">
                <li><a href="index.php">Painel Inicial</a></li>
                <li><a href="artigos.php">Gerenciar Artigos</a></li>
                <li><a href="produtos.php" class="active">Gerenciar Produtos</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">Sair do Sistema</a>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="content">
        <h1><?php echo $modoEdicao ? 'Editar' : 'Novo'; ?> Produto</h1>

        <?php if (!empty($mensagem) && !$sucesso): ?>
            <div class="alert">
                ⚠ <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="produto-formulario.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="imagem_atual" value="<?php echo $imagemAtual; ?>">

                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required placeholder="Ex: Moletom Descartes Quântico">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição do Produto *</label>
                    <textarea id="descricao" name="descricao" required placeholder="Fale sobre o tecido, caimento ou detalhes do produto..."><?php echo htmlspecialchars($descricao); ?></textarea>
                </div>

                <div class="form-group" style="max-width: 250px;">
                    <label for="preco">Preço (R$) *</label>
                    <input type="text" id="preco" name="preco" value="<?php echo htmlspecialchars($preco); ?>" required placeholder="Ex: 199,90" pattern="[0-9.,]+">
                </div>

                <div class="form-group">
                    <label for="link_compra">Link de Compra *</label>
                    <input type="text" id="link_compra" name="link_compra" value="<?php echo htmlspecialchars($linkCompra); ?>" required placeholder="Link do Mercado Pago, PagSeguro, etc.">
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria *</label>
                    <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>" required placeholder="Categoria do produto">
                </div>

                <div class="form-group">
                    <label for="imagem">Foto do Produto *</label>
                    <input type="file" id="imagem" name="imagem" accept="image/png, image/jpeg, image/jpg, image/webp" <?php echo $modoEdicao ? '' : 'required'; ?>>
                    
                    <?php if ($modoEdicao && !empty($imagemAtual)): ?>
                        <span style="display:block; margin-top: 10px; font-size: 13px; color: #888;">Foto atual:</span>
                        <img src="../src/images/uploads/<?php echo $imagemAtual; ?>" class="preview-imagem" alt="Produto Atual">
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-salvar">
                        <?php echo $modoEdicao ? 'Salvar Alterações' : 'Cadastrar Produto'; ?>
                    </button>
                    <a href="produtos.php" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>