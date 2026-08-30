<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$erros = [];
$dados = ['usuario' => '', 'nome_exibicao' => '', 'tipo' => 'Comum'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['usuario']       = trim((string) filter_input(INPUT_POST, 'usuario', FILTER_UNSAFE_RAW));
    $dados['nome_exibicao'] = trim((string) filter_input(INPUT_POST, 'nome_exibicao', FILTER_UNSAFE_RAW));
    $dados['tipo']          = filter_input(INPUT_POST, 'tipo', FILTER_UNSAFE_RAW);
    $senha                  = (string) filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW);

    if ($dados['usuario'] === '' || !preg_match('/^[a-zA-Z0-9_.]+$/', $dados['usuario'])) {
        $erros[] = 'O login deve conter apenas letras, números, ponto ou underline (sem espaços ou acentos).';
    }
    if ($dados['nome_exibicao'] === '') {
        $erros[] = 'O nome de exibição é obrigatório.';
    }
    if (!in_array($dados['tipo'], ['Administrador', 'Comum'], true)) {
        $erros[] = 'Selecione um tipo de usuário válido.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha inicial precisa ter pelo menos 6 caracteres.';
    }

    if (empty($erros)) {
        $pdo = conectarBanco();

        $stmtExiste = $pdo->prepare('SELECT usuario FROM usuarios WHERE usuario = :usuario');
        $stmtExiste->execute([':usuario' => $dados['usuario']]);

        if ($stmtExiste->fetch()) {
            $erros[] = 'Já existe um usuário cadastrado com esse login.';
        } else {
            try {
                $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO usuarios (usuario, nome_exibicao, senha, status, tipo, qtd_acessos, tentativas_erradas)
                     VALUES (:usuario, :nome, :senha, "Ativo", :tipo, 0, 0)'
                );
                $stmt->execute([
                    ':usuario' => $dados['usuario'],
                    ':nome'    => $dados['nome_exibicao'],
                    ':senha'   => $hashSenha,
                    ':tipo'    => $dados['tipo'],
                ]);

                definirMensagem('sucesso', 'Usuário "' . $dados['usuario'] . '" cadastrado com sucesso! Ele precisará trocar a senha no primeiro acesso.');
                registrarLog('Cadastrou o usuário "' . $dados['usuario'] . '" (tipo: ' . $dados['tipo'] . ')');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $erros[] = 'Ocorreu um erro ao salvar o usuário. Tente novamente.';
            }
        }
    }
}

$tituloPagina = 'Novo Usuário - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-plus"></i> Novo Usuário</h1>
        </div>

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
                    <label for="usuario">Usuário (login) *</label>
                    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($dados['usuario']) ?>" required>
                    <span class="erro-campo">Informe um login válido (letras, números, ponto ou underline).</span>
                    <small style="color:#888;">Esse campo não poderá ser alterado depois de criado.</small>
                </div>

                <div class="form-grupo">
                    <label for="nome_exibicao">Nome de exibição *</label>
                    <input type="text" id="nome_exibicao" name="nome_exibicao" value="<?= htmlspecialchars($dados['nome_exibicao']) ?>" required>
                    <span class="erro-campo">Preencha o nome de exibição.</span>
                </div>

                <div class="form-grupo">
                    <label for="senha">Senha inicial *</label>
                    <input type="password" id="senha" name="senha" minlength="6" required>
                    <span class="erro-campo">A senha precisa ter pelo menos 6 caracteres.</span>
                    <small style="color:#888;">O usuário será obrigado a trocar essa senha no primeiro login.</small>
                </div>

                <div class="form-grupo">
                    <label for="tipo">Tipo de usuário *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Comum" <?= $dados['tipo'] === 'Comum' ? 'selected' : '' ?>>Comum (somente visualização)</option>
                        <option value="Administrador" <?= $dados['tipo'] === 'Administrador' ? 'selected' : '' ?>>Administrador (acesso total)</option>
                    </select>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-primario"><i class="fa-solid fa-check"></i> Salvar</button>
                    <a href="index.php" class="btn btn-secundario"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
