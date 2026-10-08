<?php
// Inicia a sessão e verifica o login
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$nomeUsuario = $_SESSION['usuario_nome'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Vértice Mental</title>
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
        
        /* Menu Lateral */
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
        .btn-logout:hover {
            text-decoration: underline;
        }
        
        /* Área de Conteúdo Principal */
        .content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            min-height: 100vh;
            box-sizing: border-box;
        }
        
        h1 {
            font-size: 28px;
            margin-top: 0;
            color: #b19cd9;
        }
        
        .welcome-box {
            background-color: #1e1f29;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(177, 156, 217, 0.1);
            margin-bottom: 30px;
        }
        
        /* Grade de Cards de Atalho */
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #1e1f29;
            border-radius: 12px;
            border: 1px solid rgba(177, 156, 217, 0.1);
            padding: 25px;
            text-align: center;
            transition: transform 0.3s, border-color 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .card:hover {
            transform: translateY(-5px);
            border-color: #6a0dad;
        }
        .card h3 {
            color: #b19cd9;
            font-size: 18px;
            margin: 15px 0 10px;
        }
        .card p {
            color: #aaa;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Menu Lateral -->
    <div class="sidebar">
        <div>
            <div class="sidebar-brand">Vértice Mental</div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Painel Inicial</a></li>
                <li><a href="artigos.php">Gerenciar Artigos</a></li> 
                <li><a href="produtos.php">Gerenciar Produtos</a></li> 
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">Sair do Sistema</a>
        </div>
    </div>

    <!-- Área de Conteúdo -->
    <div class="content">
        <div class="welcome-box">
            <h1>Olá, <?php echo htmlspecialchars($nomeUsuario); ?>!</h1>
            <p>Seja bem-vindo ao painel do seu blog. Use o menu ao lado para gerenciar o conteúdo.</p>
        </div>

        <div class="grid-cards">
            <!-- Atalho Artigos -->
            <a href="artigos.php" class="card">
                <span style="font-size: 40px;">📝</span>
                <h3>Gerenciar Artigos</h3>
                <p>Inserir, editar ou remover posts do blog.</p>
            </a>

            <!-- Atalho Produtos -->
            <a href="produtos.php" class="card">
                <span style="font-size: 40px;">🛍️</span>
                <h3>Gerenciar Produtos</h3>
                <p>Inserir, editar ou remover itens na loja.</p>
            </a>
        </div>
    </div>

</body>
</html>