<?php
require_once __DIR__ . '/php/config/auth.php';

// Não usamos exigirLogin() aqui de propósito (ela redirecionaria de volta
// pra cá em loop infinito quando forcar_troca_senha estiver ativo).
// Fazemos a checagem manual:
if (!estaLogado()) {
    header('Location: login.php');
    exit;
}

$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou. Recarregue a página e tente novamente.';
    } else {
        $senhaAtual        = (string) filter_input(INPUT_POST, 'senha_atual', FILTER_UNSAFE_RAW);
        $novaSenha         = (string) filter_input(INPUT_POST, 'nova_senha', FILTER_UNSAFE_RAW);
        $confirmarNovaSenha = (string) filter_input(INPUT_POST, 'confirmar_nova_senha', FILTER_UNSAFE_RAW);

        $pdo = conectarBanco();
        $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE usuario = :usuario');
        $stmt->execute([':usuario' => $_SESSION['usuario']]);
        $usuarioAtual = $stmt->fetch();

        if (!$usuarioAtual || !password_verify($senhaAtual, $usuarioAtual['senha'])) {
            $erros[] = 'A senha atual informada está incorreta.';
        }
        if (strlen($novaSenha) < 6) {
            $erros[] = 'A nova senha precisa ter pelo menos 6 caracteres.';
        }
        if ($novaSenha !== $confirmarNovaSenha) {
            $erros[] = 'A confirmação não é igual à nova senha.';
        }
        if ($novaSenha === $senhaAtual) {
            $erros[] = 'A nova senha precisa ser diferente da senha atual.';
        }

        if (empty($erros)) {
            $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE usuarios SET senha = :senha WHERE usuario = :usuario')
                ->execute([':senha' => $novoHash, ':usuario' => $_SESSION['usuario']]);

            unset($_SESSION['forcar_troca_senha']);
            definirMensagem('sucesso', 'Senha alterada com sucesso!');
            header('Location: index.php');
            exit;
        }
    }
}

$tituloPagina = 'Trocar Senha - CRUD Mundo';
require __DIR__ . '/php/includes/header.php';
require __DIR__ . '/php/includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal" style="max-width:700px;">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-key"></i> Trocar Senha</h1>
        </div>

        <?php if (!empty($_SESSION['forcar_troca_senha'])): ?>
            <div class="mensagem mensagem-erro">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Este é o seu primeiro acesso. Por segurança, você precisa trocar a senha antes de continuar.
            </div>
        <?php endif; ?>

        <?php if (!empty($erros)): ?>
            <div class="mensagem mensagem-erro">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <?php foreach ($erros as $erro): ?>
                        <div><?= htmlspecialchars($erro) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">

                <div class="form-grupo">
                    <label for="senha_atual">Senha atual *</label>
                    <input type="password" id="senha_atual" name="senha_atual" required>
                    <span class="erro-campo">Informe sua senha atual.</span>
                </div>

                <div class="form-grupo">
                    <label for="nova_senha">Nova senha *</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="6" required>
                    <span class="erro-campo">A nova senha precisa ter pelo menos 6 caracteres.</span>
                </div>

                <div class="form-grupo">
                    <label for="confirmar_nova_senha">Confirmar nova senha *</label>
                    <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" minlength="6" required>
                    <span class="erro-campo">Confirme a nova senha.</span>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-primario"><i class="fa-solid fa-check"></i> Salvar nova senha</button>
                    <?php if (empty($_SESSION['forcar_troca_senha'])): ?>
                        <a href="index.php" class="btn btn-secundario"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/php/includes/footer.php'; ?>
