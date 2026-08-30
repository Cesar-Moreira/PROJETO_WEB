<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$pdo = conectarBanco();

$usuarioAlvo = trim((string) filter_input(INPUT_GET, 'usuario', FILTER_UNSAFE_RAW));
if ($usuarioAlvo === '') {
    definirMensagem('erro', 'Usuário inválido.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
$stmt->execute([':usuario' => $usuarioAlvo]);
$usuarioAtual = $stmt->fetch();

if (!$usuarioAtual) {
    definirMensagem('erro', 'Usuário não encontrado.');
    header('Location: index.php');
    exit;
}

$erros = [];
$dados = [
    'nome_exibicao' => $usuarioAtual['nome_exibicao'],
    'tipo'          => $usuarioAtual['tipo'],
    'status'        => $usuarioAtual['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['nome_exibicao'] = trim((string) filter_input(INPUT_POST, 'nome_exibicao', FILTER_UNSAFE_RAW));
    $dados['tipo']          = filter_input(INPUT_POST, 'tipo', FILTER_UNSAFE_RAW);
    $dados['status']        = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
    $novaSenha              = (string) filter_input(INPUT_POST, 'nova_senha', FILTER_UNSAFE_RAW);

    if ($dados['nome_exibicao'] === '') {
        $erros[] = 'O nome de exibição é obrigatório.';
    }
    if (!in_array($dados['tipo'], ['Administrador', 'Comum'], true)) {
        $erros[] = 'Selecione um tipo de usuário válido.';
    }
    if (!in_array($dados['status'], ['Ativo', 'Inativo', 'Bloqueado'], true)) {
        $erros[] = 'Selecione um status válido.';
    }
    // Não deixa o admin se auto-rebaixar/desativar por engano e ficar sem acesso
    if ($usuarioAlvo === $_SESSION['usuario'] && ($dados['tipo'] !== 'Administrador' || $dados['status'] !== 'Ativo')) {
        $erros[] = 'Você não pode remover seu próprio acesso de administrador ou se desativar/bloquear enquanto estiver logado.';
    }
    if ($novaSenha !== '' && strlen($novaSenha) < 6) {
        $erros[] = 'Se for definir uma nova senha, ela precisa ter pelo menos 6 caracteres.';
    }

    if (empty($erros)) {
        try {
            // Se o status está sendo colocado como "Ativo" (ex.: desbloqueio manual),
            // zeramos também as tentativas erradas — senão a próxima tentativa errada
            // já bloquearia de novo, mesmo com o contador antigo.
            $zerarTentativas = ($dados['status'] === 'Ativo');

            $sql = 'UPDATE usuarios SET nome_exibicao = :nome, tipo = :tipo, status = :status';
            $parametros = [
                ':nome'    => $dados['nome_exibicao'],
                ':tipo'    => $dados['tipo'],
                ':status'  => $dados['status'],
                ':usuario' => $usuarioAlvo,
            ];

            if ($zerarTentativas) {
                $sql .= ', tentativas_erradas = 0';
            }
            if ($novaSenha !== '') {
                $sql .= ', senha = :senha, qtd_acessos = 0'; // reseta para forçar troca de senha no próximo login
                $parametros[':senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE usuario = :usuario';

            $pdo->prepare($sql)->execute($parametros);

            definirMensagem('sucesso', 'Usuário atualizado com sucesso!');
            registrarLog('Editou o usuário "' . $usuarioAlvo . '" (status: ' . $dados['status'] . ', tipo: ' . $dados['tipo'] . ')');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erros[] = 'Ocorreu um erro ao atualizar o usuário. Tente novamente.';
        }
    }
}

$tituloPagina = 'Editar Usuário - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-pen"></i> Editar Usuário</h1>
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
                    <label>Usuário (login)</label>
                    <input type="text" value="<?= htmlspecialchars($usuarioAlvo) ?>" disabled
                           style="background:#f5f5f5; color:#888;">
                    <small style="color:#888;">O login não pode ser alterado depois de criado.</small>
                </div>

                <div class="form-grupo">
                    <label for="nome_exibicao">Nome de exibição *</label>
                    <input type="text" id="nome_exibicao" name="nome_exibicao" value="<?= htmlspecialchars($dados['nome_exibicao']) ?>" required>
                    <span class="erro-campo">Preencha o nome de exibição.</span>
                </div>

                <div class="form-grupo">
                    <label for="tipo">Tipo de usuário *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Comum" <?= $dados['tipo'] === 'Comum' ? 'selected' : '' ?>>Comum (somente visualização)</option>
                        <option value="Administrador" <?= $dados['tipo'] === 'Administrador' ? 'selected' : '' ?>>Administrador (acesso total)</option>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Ativo" <?= $dados['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= $dados['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                        <option value="Bloqueado" <?= $dados['status'] === 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                    <small style="color:#888;">Trocar para "Ativo" desbloqueia a conta e zera as tentativas de senha erradas.</small>
                </div>

                <div class="form-grupo">
                    <label for="nova_senha">Redefinir senha (opcional)</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="6" placeholder="Deixe em branco para manter a senha atual">
                    <span class="erro-campo">Se preenchida, a senha precisa ter pelo menos 6 caracteres.</span>
                    <small style="color:#888;">Se preenchida, o usuário será obrigado a trocá-la de novo no próximo login.</small>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-primario"><i class="fa-solid fa-check"></i> Salvar alterações</button>
                    <a href="index.php" class="btn btn-secundario"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
