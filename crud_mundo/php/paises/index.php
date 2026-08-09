<?php
require_once __DIR__ . '/../config/database.php';

$tituloPagina = 'Países - CRUD Mundo';

$ordem = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'desc') ? 'DESC' : 'ASC';
$proximaOrdem = ($ordem === 'ASC') ? 'desc' : 'asc';

$pdo = conectarBanco();

// JOIN com continentes e governantes só para mostrar o NOME em vez do ID na tabela
$sql = "SELECT p.*, c.nome AS continente_nome, g.nome AS governante_nome
        FROM paises p
        INNER JOIN continentes c ON c.id = p.continente_id
        LEFT JOIN governantes g ON g.id = p.governante_id
        ORDER BY p.nome {$ordem}";
$stmt = $pdo->query($sql);
$paises = $stmt->fetchAll();

$mensagem = obterMensagem();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-flag"></i> Países</h1>
            <a href="cadastrar.php" class="btn btn-primario">
                <i class="fa-solid fa-plus"></i> Novo país
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
                <input type="text" id="campoPesquisa" placeholder="Pesquisar país pelo nome...">
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
                        <th>Continente</th>
                        <th>População</th>
                        <th>Idioma</th>
                        <th>Governante</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paises)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:2rem; color:#888;">
                                Nenhum país cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paises as $pais): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($pais['nome'])) ?>">
                                <td><?= htmlspecialchars($pais['nome']) ?></td>
                                <td><?= htmlspecialchars($pais['continente_nome']) ?></td>
                                <td><?= number_format((float) $pais['populacao'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($pais['idioma'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($pais['governante_nome'] ?: '-') ?></td>
                                <td>
                                    <div class="acoes-tabela">
                                        <a href="editar.php?id=<?= (int) $pais['id'] ?>" class="acao-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="excluir.php" method="POST" class="form-exclusao"
                                              onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($pais['nome'])) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $pais['id'] ?>">
                                            <button type="submit" class="acao-excluir" title="Excluir">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
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
