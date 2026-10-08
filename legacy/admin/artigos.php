<?php
// Inicia a sessão e verifica se está logado
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../components/conexao.php';

$mensagem = '';
$sucesso = true;

// LÓGICA DE EXCLUSÃO DE ARTIGO
if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];

    try {
        // Primeiro, busca o nome da imagem para podermos apagá-la do servidor
        $stmtFoto = $conexao->prepare("SELECT imagem FROM artigos WHERE id = :id");
        $stmtFoto->execute([':id' => $idExcluir]);
        $artigoFoto = $stmtFoto->fetch();

        // Deleta o registro no banco de dados
        $stmt = $conexao->prepare("DELETE FROM artigos WHERE id = :id");
        $stmt->execute([':id' => $idExcluir]);

        // Se o artigo foi deletado com sucesso, apaga a foto da pasta de uploads
        if ($artigoFoto && !empty($artigoFoto['imagem'])) {
            $caminhoFoto = "../src/images/uploads/" . $artigoFoto['imagem'];
            if (file_exists($caminhoFoto)) {
                unlink($caminhoFoto); // Apaga o arquivo físico do computador
            }
        }

        header("Location: artigos.php?msg=deletado");
        exit;
    } catch (PDOException $e) {
        $mensagem = "Erro ao excluir artigo: " . $e->getMessage();
        $sucesso = false;
    }
}

// Verifica se há alguma mensagem de redirecionamento anterior
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deletado') {
        $mensagem = "Artigo excluído com sucesso!";
    } elseif ($_GET['msg'] === 'cadastrado') {
        $mensagem = "Novo artigo publicado com sucesso!";
    } elseif ($_GET['msg'] === 'editado') {
        $mensagem = "Artigo atualizado com sucesso!";
    }
}

// Busca todos os artigos cadastrados para listar na tabela
try {
    $query = $conexao->query("SELECT * FROM artigos ORDER BY data_criacao DESC");
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
    <title>Gerenciar Artigos - Vértice Mental</title>
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
        
        /* Menu Lateral (Idêntico ao do index.php) */
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
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 28px;
            margin: 0;
            color: #b19cd9;
        }
        
        .btn-novo {
            padding: 12px 20px;
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
            transition: all 0.3s;
        }
        .btn-novo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 13, 173, 0.5);
        }
        
        /* Caixa de Alerta/Aviso */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background-color: rgba(75, 181, 67, 0.15);
            border: 1px solid #4bb543;
            color: #6cff8a;
        }
        .alert-danger {
            background-color: rgba(255, 51, 51, 0.15);
            border: 1px solid #ff3333;
            color: #ff6666;
        }
        
        /* Tabela de Artigos */
        .table-container {
            background-color: #1e1f29;
            border-radius: 12px;
            border: 1px solid rgba(177, 156, 217, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        
        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        th {
            background-color: rgba(177, 156, 217, 0.05);
            color: #b19cd9;
            font-weight: 600;
            font-size: 14px;
        }
        
        td {
            font-size: 14px;
            color: #ddd;
            vertical-align: middle;
        }
        
        tr:hover td {
            background-color: rgba(255, 255, 255, 0.01);
        }
        
        .miniatura {
            width: 60px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            background-color: #333;
        }
        
        /* Botões de Ação */
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn-edit {
            color: #b19cd9;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-edit:hover {
            color: #fff;
        }
        .btn-delete {
            color: #ff6666;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-delete:hover {
            color: #ff3333;
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

    <!-- Área de Conteúdo -->
    <div class="content">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="header-content">
            <h1>Gerenciar Artigos</h1>
            <a href="artigo-formulario.php" class="btn-novo">+ Novo Artigo</a>
        </div>

        <div class="table-container">
            <?php if (empty($artigos)): ?>
                <p style="padding: 30px; text-align: center; color: #888; font-style: italic; margin: 0;">
                    Nenhum artigo cadastrado até o momento.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>Título</th>
                            <th>Publicado em</th>
                            <th style="text-align: center;">Visualizações</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($artigos as $artigo): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $caminhoImagem = (strpos($artigo['imagem'], 'http') === 0) 
                                        ? $artigo['imagem'] 
                                        : "../src/images/uploads/" . $artigo['imagem'];
                                    ?>
                                    <img src="<?php echo htmlspecialchars($caminhoImagem); ?>" class="miniatura" alt="Capa">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($artigo['titulo']); ?></strong>
                                    <br>
                                    <span style="font-size: 11px; color: #777;">slug: /<?php echo htmlspecialchars($artigo['slug']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($artigo['data_criacao'])); ?></td>
                                <td style="text-align: center; color: #b19cd9; font-weight: 600;">
                                    <?php echo $artigo['visualizacoes']; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="actions" style="justify-content: flex-end;">
                                        <a href="artigo-formulario.php?id=<?php echo $artigo['id']; ?>" class="btn-edit">Editar</a>
                                        | 
                                        <!-- O JavaScript no onclick impede a exclusão acidental -->
                                        <a href="artigos.php?excluir=<?php echo $artigo['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Tem certeza absoluta que deseja excluir o artigo \‘<?php echo addslashes($artigo['titulo']); ?>\’? Esta ação não pode ser desfeita.');">
                                            Excluir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>