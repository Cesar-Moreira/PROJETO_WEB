<?php
require_once __DIR__ . '/../config/auth.php';
exigirLogin();

$tituloPagina = 'Continentes - CRUD Mundo';

// Ordenação por nome (clicando no cabeçalho da coluna)
$ordem = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'desc') ? 'DESC' : 'ASC';
$proximaOrdem = ($ordem === 'ASC') ? 'desc' : 'asc';

$pdo = conectarBanco();
$stmt = $pdo->query("SELECT * FROM continentes ORDER BY nome {$ordem}");
$continentes = $stmt->fetchAll();

$mensagem = obterMensagem();
$ehAdmin = ($_SESSION['tipo'] === 'Administrador');

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-globe"></i> Continentes</h1>
            <?php if ($_SESSION['tipo'] === 'Administrador'): ?>
                <a href="cadastrar.php" class="btn btn-primario">
                    <i class="fa-solid fa-plus"></i> Novo continente
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
                <input type="text" id="campoPesquisa" placeholder="Pesquisar continente pelo nome...">
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
                        <th>População</th>
                        <th>Área (km²)</th>
                        <th>Total de países</th>
                        <?php if ($ehAdmin): ?><th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($continentes)): ?>
                        <tr>
                            <td colspan="<?= $ehAdmin ? 5 : 4 ?>" style="text-align:center; padding:2rem; color:#888;">
                                Nenhum continente cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($continentes as $continente): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($continente['nome'])) ?>">
                                <td><?= htmlspecialchars($continente['nome']) ?></td>
                                <td><?= number_format((float) $continente['populacao'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $continente['area_km2'], 2, ',', '.') ?> km²</td>
                                <td><?= (int) $continente['total_paises'] ?></td>
                                <?php if ($ehAdmin): ?>
                                <td>
                                    <div class="acoes-tabela">
                                        <a href="editar.php?id=<?= (int) $continente['id'] ?>" class="acao-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="excluir.php" method="POST" class="form-exclusao"
                                              onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($continente['nome'])) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $continente['id'] ?>">
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
