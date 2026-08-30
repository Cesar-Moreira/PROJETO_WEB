<?php
require_once __DIR__ . '/php/config/auth.php';

// Se já está logado, não faz sentido ver a tela de login de novo
if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$dados = ['usuario' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Recarregue a página e tente novamente.';
    } else {
        $dados['usuario'] = trim((string) filter_input(INPUT_POST, 'usuario', FILTER_UNSAFE_RAW));
        $senhaDigitada    = (string) filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW);

        if ($dados['usuario'] === '' || $senhaDigitada === '') {
            $erro = 'Preencha usuário e senha.';
        } else {
            $pdo = conectarBanco();
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
            $stmt->execute([':usuario' => $dados['usuario']]);
            $usuarioEncontrado = $stmt->fetch();

            if (!$usuarioEncontrado) {
                // Mensagem genérica de propósito: não revela se o usuário existe ou não
                $erro = 'Usuário ou senha inválidos.';
            } elseif ($usuarioEncontrado['status'] === 'Bloqueado') {
                $erro = 'Esta conta está bloqueada por excesso de tentativas incorretas. Procure um administrador.';
            } elseif ($usuarioEncontrado['status'] === 'Inativo') {
                $erro = 'Esta conta está inativa. Procure um administrador.';
            } elseif (!password_verify($senhaDigitada, $usuarioEncontrado['senha'])) {
                // Senha errada: incrementa o contador e bloqueia se chegar a 3
                $novasTentativas = (int) $usuarioEncontrado['tentativas_erradas'] + 1;

                if ($novasTentativas >= 3) {
                    $pdo->prepare('UPDATE usuarios SET tentativas_erradas = :tentativas, status = "Bloqueado" WHERE usuario = :usuario')
                        ->execute([':tentativas' => $novasTentativas, ':usuario' => $dados['usuario']]);
                    $erro = 'Senha incorreta pela 3ª vez. Sua conta foi bloqueada. Procure um administrador.';
                } else {
                    $pdo->prepare('UPDATE usuarios SET tentativas_erradas = :tentativas WHERE usuario = :usuario')
                        ->execute([':tentativas' => $novasTentativas, ':usuario' => $dados['usuario']]);
                    $tentativasRestantes = 3 - $novasTentativas;
                    $erro = "Senha incorreta. Você tem mais {$tentativasRestantes} tentativa(s) antes do bloqueio.";
                }
            } else {
                // Login correto! Zera as tentativas erradas e conta mais um acesso.
                $novaQtdAcessos = (int) $usuarioEncontrado['qtd_acessos'] + 1;

                $pdo->prepare('UPDATE usuarios SET tentativas_erradas = 0, qtd_acessos = :qtd WHERE usuario = :usuario')
                    ->execute([':qtd' => $novaQtdAcessos, ':usuario' => $dados['usuario']]);

                session_regenerate_id(true); // evita fixação de sessão
                $_SESSION['usuario']       = $usuarioEncontrado['usuario'];
                $_SESSION['nome_exibicao'] = $usuarioEncontrado['nome_exibicao'];
                $_SESSION['tipo']          = $usuarioEncontrado['tipo'];

                // Se este acabou de ser o PRIMEIRO acesso (qtd chegou a 1), força a troca de senha
                $_SESSION['forcar_troca_senha'] = ($novaQtdAcessos === 1);

                header('Location: ' . ($_SESSION['forcar_troca_senha'] ? 'trocar_senha.php' : 'index.php'));
                exit;
            }
        }
    }
}

$tituloPagina = 'Login - CRUD Mundo';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        .pagina-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }
        .cartao-login {
            background: #fff;
            padding: 2.5rem;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 380px;
        }
        .cartao-login h1 {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.3rem;
            color: #1565C0;
            margin-top: 0;
        }
        .cartao-login p.subtitulo {
            color: #757575;
            font-size: 0.88rem;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="pagina-login">
        <div class="cartao-login">
            <h1><i class="fa-solid fa-earth-americas"></i> CRUD Mundo</h1>
            <p class="subtitulo">Entre com seu usuário e senha para continuar.</p>

            <?php if ($erro): ?>
                <div class="mensagem mensagem-erro">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">

                <div class="form-grupo">
                    <label for="usuario">Usuário</label>
                    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($dados['usuario']) ?>" required autofocus>
                </div>

                <div class="form-grupo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-primario" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-right-to-bracket"></i> Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
