<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../components/conexao.php';

$mensagem = '';
$sucesso = true;

// Variáveis para guardar os dados do formulário (vazias por padrão)
$id = 0;
$titulo = '';
$subtitulo = '';
$conteudo = '';
$imagemAtual = '';
$modoEdicao = false;

// Geração de URL amigável (slug)
function gerarSlug($texto) {
    $texto = preg_replace('~[^\pL\d]+~u', '-', $texto); // Substitui caracteres inválidos
    $texto = trim($texto, '-');
    
    // Tabela de substituição manual de acentos
    $mapa = [
        'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
        'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o',
        'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ç'=>'c', 'ñ'=>'n', 'ý'=>'y',
        'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E',
        'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O',
        'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ç'=>'C', 'Ñ'=>'N', 'Ý'=>'Y'
    ];
    $texto = strtr($texto, $mapa);
    $texto = strtolower($texto);
    $texto = preg_replace('~[^-\w]+~', '', $texto); // Limpa caracteres indesejados
    $texto = preg_replace('~-+~', '-', $texto); // Evita múltiplos hifens seguidos
    return $texto;
}

// Busca os dados do artigo em caso de edição
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $modoEdicao = true;

    try {
        $stmt = $conexao->prepare("SELECT * FROM artigos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $artigo = $stmt->fetch();

        if ($artigo) {
            $titulo = $artigo['titulo'];
            $subtitulo = $artigo['subtitulo'];
            $conteudo = $artigo['conteudo'];
            $imagemAtual = $artigo['imagem'];
        } else {
            header("Location: artigos.php");
            exit;
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao carregar artigo: " . $e->getMessage();
        $sucesso = false;
    }
}

// Processamento do formulário (Inserção/Atualização)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo']);
    $conteudo = trim($_POST['conteudo']);
    $imagemAtual = $_POST['imagem_atual'];
    $modoEdicao = $id > 0;

    if (!empty($titulo) && !empty($subtitulo) && !empty($conteudo)) {
        
        $slug = gerarSlug($titulo);
        $nomeFinalImagem = $imagemAtual; // Mantém a imagem atual por padrão

        // Tratamento de upload de imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $arqNome = $_FILES['imagem']['name'];
            $arqTmp = $_FILES['imagem']['tmp_name'];
            $arqExtensao = strtolower(pathinfo($arqNome, PATHINFO_EXTENSION));
            
            $extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($arqExtensao, $extensoesValidas)) {
                // Gera um nome único para a imagem (ex: 64b8c2d1e5a9f.webp)
                $nomeFinalImagem = uniqid() . '.' . $arqExtensao;
                $pastaDestino = '../src/images/uploads/' . $nomeFinalImagem;

                if (move_uploaded_file($arqTmp, $pastaDestino)) {
                    // Remove imagem anterior caso exista
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
            // Validação de imagem obrigatória para novos cadastros
            $mensagem = "Por favor, envie uma imagem de capa para o artigo.";
            $sucesso = false;
        }

        // Persistência no banco de dados
        if ($sucesso) {
            try {
                if ($modoEdicao) {
                    // Atualiza o artigo existente
                    $stmt = $conexao->prepare("UPDATE artigos SET titulo = :titulo, subtitulo = :subtitulo, conteudo = :conteudo, imagem = :imagem, slug = :slug WHERE id = :id");
                    $stmt->execute([
                        ':titulo' => $titulo,
                        ':subtitulo' => $subtitulo,
                        ':conteudo' => $conteudo,
                        ':imagem' => $nomeFinalImagem,
                        ':slug' => $slug,
                        ':id' => $id
                    ]);
                    header("Location: artigos.php?msg=editado");
                    exit;
                } else {
                    // Insere um novo artigo
                    $stmt = $conexao->prepare("INSERT INTO artigos (titulo, subtitulo, conteudo, imagem, slug) VALUES (:titulo, :subtitulo, :conteudo, :imagem, :slug)");
                    $stmt->execute([
                        ':titulo' => $titulo,
                        ':subtitulo' => $subtitulo,
                        ':conteudo' => $conteudo,
                        ':imagem' => $nomeFinalImagem,
                        ':slug' => $slug
                    ]);
                    header("Location: artigos.php?msg=cadastrado");
                    exit;
                }
            } catch (PDOException $e) {
                // Tratamento de violação de unicidade (slug duplicado)
                if ($e->getCode() == 23000) {
                    $mensagem = "Já existe um artigo publicado com esse título! Escolha outro.";
                } else {
                    $mensagem = "Erro ao salvar no banco de dados: " . $e->getMessage();
                }
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
    <title><?php echo $modoEdicao ? 'Editar' : 'Novo'; ?> Artigo - Vértice Mental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#conteudo',
            plugins: 'lists link image',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            skin: 'oxide-dark',
            content_css: 'dark',
            height: 400
        });
    </script>

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
        
        /* Caixa de Alerta/Aviso */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            background-color: rgba(255, 51, 51, 0.15);
            border: 1px solid #ff3333;
            color: #ff6666;
        }
        
        /* Formulário */
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
            height: 300px;
            resize: vertical;
        }
        
        /* Upload de Imagem */
        .preview-imagem {
            margin-top: 10px;
            max-width: 200px;
            max-height: 150px;
            border-radius: 6px;
            border: 1px solid #444;
            display: block;
        }
        
        /* Botões */
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
                <li><a href="artigos.php" class="active">Gerenciar Artigos</a></li>
                <li><a href="#">Gerenciar Produtos</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">Sair do Sistema</a>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="content">
        <h1><?php echo $modoEdicao ? 'Editar' : 'Novo'; ?> Artigo</h1>

        <?php if (!empty($mensagem) && !$sucesso): ?>
            <div class="alert">
                ⚠ <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <!-- Formulário multipart/form-data para suporte a upload de arquivos -->
            <form action="artigo-formulario.php" method="POST" enctype="multipart/form-data">
                
                <!-- Campos ocultos de controle -->
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="imagem_atual" value="<?php echo $imagemAtual; ?>">

                <div class="form-group">
                    <label for="titulo">Título do Artigo *</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required placeholder="Digite o título principal do post">
                </div>

                <div class="form-group">
                    <label for="subtitulo">Resumo/Subtítulo *</label>
                    <input type="text" id="subtitulo" name="subtitulo" value="<?php echo htmlspecialchars($subtitulo); ?>" required placeholder="Uma linha curta que descreve o artigo">
                </div>

                <div class="form-group">
                    <label for="conteudo">Conteúdo Principal *</label>
                    <textarea id="conteudo" name="conteudo" placeholder="Escreva o texto completo do seu post aqui."><?php echo htmlspecialchars($conteudo); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="imagem">Foto de Capa *</label>
                    <input type="file" id="imagem" name="imagem" accept="image/png, image/jpeg, image/jpg, image/webp" <?php echo $modoEdicao ? '' : 'required'; ?>>
                    
                    <?php if ($modoEdicao && !empty($imagemAtual)): ?>
                        <span style="display:block; margin-top: 10px; font-size: 13px; color: #888;">Capa atual:</span>
                        <img src="../src/images/uploads/<?php echo $imagemAtual; ?>" class="preview-imagem" alt="Capa Atual">
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-salvar">
                        <?php echo $modoEdicao ? 'Salvar Alterações' : 'Publicar Artigo'; ?>
                    </button>
                    <a href="artigos.php" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>