<?php
require_once __DIR__ . '/../config/auth.php';
exigirLogin();

$tituloPagina = 'Governantes - CRUD Mundo';

$ordem = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'desc') ? 'DESC' : 'ASC';
$proximaOrdem = ($ordem === 'ASC') ? 'desc' : 'asc';

$pdo = conectarBanco();
$stmt = $pdo->query("SELECT * FROM governantes ORDER BY nome {$ordem}");
$governantes = $stmt->fetchAll();

$mensagem = obterMensagem();
$ehAdmin = ($_SESSION['tipo'] === 'Administrador');

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';

// Formata data no padrão brasileiro, tratando valores nulos
function formatarData(?string $data): string
{
    if (!$data) {
        return '-';
    }
    return date('d/m/Y', strtotime($data));
}
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-user-tie"></i> Governantes</h1>
            <?php if ($ehAdmin): ?>
                <a href="cadastrar.php" class="btn btn-primario">
                    <i class="fa-solid fa-plus"></i> Novo governante
                </a>
            <?php endif; ?>
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
                <input type="text" id="campoPesquisa" placeholder="Pesquisar governante pelo nome...">
            </div>
        </div>

        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?ordem=<?= $proximaOrdem ?>">
                                Nome <i class="fa-solid fa-sort"></i>
                            </a>
                        </th>
                        <th>Partido</th>
                        <th>Idade</th>
                        <th>Início do mandato</th>
                        <th>Fim do mandato</th>
                        <?php if ($ehAdmin): ?><th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($governantes)): ?>
                        <tr>
                            <td colspan="<?= $ehAdmin ? 6 : 5 ?>" style="text-align:center; padding:2rem; color:#888;">
                                Nenhum governante cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($governantes as $governante): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($governante['nome'])) ?>">
                                <td><?= htmlspecialchars($governante['nome']) ?></td>
                                <td><?= htmlspecialchars($governante['partido_politico'] ?: '-') ?></td>
                                <td><?= $governante['idade'] !== null ? (int) $governante['idade'] . ' anos' : '-' ?></td>
                                <td><?= formatarData($governante['inicio_mandato']) ?></td>
                                <td><?= formatarData($governante['fim_mandato']) ?></td>
                                <?php if ($ehAdmin): ?>
                                <td>
                                    <div class="acoes-tabela">
                                        <a href="editar.php?id=<?= (int) $governante['id'] ?>" class="acao-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="excluir.php" method="POST" class="form-exclusao"
                                              onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($governante['nome'])) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $governante['id'] ?>">
                                            <button type="submit" class="acao-excluir" title="Excluir">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
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
