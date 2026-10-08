<?php
// Inicia a sessão para controle de acesso
session_start();

// Importa a conexão com o banco de dados (voltando uma pasta para achar o components)
require_once '../components/conexao.php';

// Se o usuário já estiver logado, redireciona direto para o painel principal
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: index.php");
    exit;
}

$erro = '';

// Processa o formulário de login quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = trim($_POST['usuario']);
    $senhaInput = $_POST['senha'];

    if (!empty($usuarioInput) && !empty($senhaInput)) {
        try {
            // Busca o usuário no banco de dados
            $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
            $stmt->execute([':usuario' => $usuarioInput]);
            $usuario = $stmt->fetch();

            // Verifica se o usuário existe e se a senha descriptografada bate
            if ($usuario && password_verify($senhaInput, $usuario['senha'])) {
                // Registra as variáveis de sessão
                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                // Redireciona para o painel administrativo
                header("Location: index.php");
                exit;
            } else {
                $erro = "Usuário ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle, #250938 0%, #0c0d12 100%);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: rgba(30, 31, 41, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(177, 156, 217, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }
        .login-card h1 {
            font-size: 26px;
            color: #b19cd9;
            margin: 0 0 10px;
        }
        .login-card p {
            color: #aaa;
            font-size: 14px;
            margin: 0 0 30px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #b19cd9;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 8px;
            background: #15161f;
            color: #fff;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #b19cd9;
            box-shadow: 0 0 10px rgba(177, 156, 217, 0.4);
        }
        .btn-entrar {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #8b5fbf 0%, #6a0dad 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
            margin-top: 10px;
        }
        .btn-entrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 13, 173, 0.5);
        }
        .erro-msg {
            background-color: rgba(255, 51, 51, 0.15);
            border: 1px solid #ff3333;
            color: #ff6666;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 25px;
            text-align: left;
        }
        .btn-voltar {
            display: inline-block;
            margin-top: 25px;
            color: #aaa;
            font-size: 13px;
            text-decoration: none;
        }
        .btn-voltar:hover {
            color: #b19cd9;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Vértice Mental</h1>
        <p>Área Administrativa</p>

        <?php if (!empty($erro)): ?>
            <div class="erro-msg">
                ⚠ <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" placeholder="Digite seu usuário" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-entrar">Acessar Painel</button>
        </form>
        <a href="../index.php" class="btn-voltar">← Voltar para o blog</a>
    </div>
</body>
</html>