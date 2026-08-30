<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$tituloPagina = 'Usuários - CRUD Mundo';

$ordem = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'desc') ? 'DESC' : 'ASC';
$proximaOrdem = ($ordem === 'ASC') ? 'desc' : 'asc';

$pdo = conectarBanco();
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY usuario {$ordem}");
$usuarios = $stmt->fetchAll();

$mensagem = obterMensagem();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';

// Cor do "selo" de status, só para ficar visualmente claro na tabela
function corStatus(string $status): string
{
    return match ($status) {
        'Ativo'     => 'background:#E8F5E9; color:#2E7D32;',
        'Bloqueado' => 'background:#FFEBEE; color:#C62828;',
        'Inativo'   => 'background:#F5F5F5; color:#757575;',
        default     => '',
    };
}
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-users-gear"></i> Usuários</h1>
            <a href="cadastrar.php" class="btn btn-primario">
                <i class="fa-solid fa-plus"></i> Novo usuário
            </a>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem mensagem-<?= $mensagem['tipo'] ?>">
                <i class="fa-solid <?= $mensagem['tipo'] === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($mensagem['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="barra-topo">
            <div class="campo-pesquisa">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="campoPesquisa" placeholder="Pesquisar usuário pelo nome de login...">
            </div>
        </div>

        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?ordem=<?= $proximaOrdem ?>">
                                Usuário (login) <i class="fa-solid fa-sort"></i>
                            </a>
                        </th>
                        <th>Nome de exibição</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Acessos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:2rem; color:#888;">
                                Nenhum usuário cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($usuario['usuario'])) ?>">
                                <td><strong><?= htmlspecialchars($usuario['usuario']) ?></strong></td>
                                <td><?= htmlspecialchars($usuario['nome_exibicao']) ?></td>
                                <td><?= htmlspecialchars($usuario['tipo']) ?></td>
                                <td>
                                    <span style="padding:0.25rem 0.7rem; border-radius:20px; font-size:0.78rem; font-weight:600; <?= corStatus($usuario['status']) ?>">
                                        <?= htmlspecialchars($usuario['status']) ?>
                                    </span>
                                </td>
                                <td><?= (int) $usuario['qtd_acessos'] ?></td>
                                <td>
                                    <div class="acoes-tabela">
                                        <a href="editar.php?usuario=<?= urlencode($usuario['usuario']) ?>" class="acao-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <?php if ($usuario['usuario'] !== $_SESSION['usuario']): ?>
                                        <form action="excluir.php" method="POST" class="form-exclusao"
                                              onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($usuario['usuario'])) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>">
                                            <button type="submit" class="acao-excluir" title="Excluir">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
