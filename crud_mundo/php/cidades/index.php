<?php
require_once __DIR__ . '/../config/database.php';

$tituloPagina = 'Cidades - CRUD Mundo';

$ordem = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'desc') ? 'DESC' : 'ASC';
$proximaOrdem = ($ordem === 'ASC') ? 'desc' : 'asc';

$pdo = conectarBanco();

$sql = "SELECT c.*, p.nome AS pais_nome, g.nome AS governante_nome
        FROM cidades c
        INNER JOIN paises p ON p.id = c.pais_id
        LEFT JOIN governantes g ON g.id = c.governante_id
        ORDER BY c.nome {$ordem}";
$stmt = $pdo->query($sql);
$cidades = $stmt->fetchAll();

$mensagem = obterMensagem();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-city"></i> Cidades</h1>
            <a href="cadastrar.php" class="btn btn-primario">
                <i class="fa-solid fa-plus"></i> Nova cidade
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
                <input type="text" id="campoPesquisa" placeholder="Pesquisar cidade pelo nome...">
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
                        <th>País</th>
                        <th>População</th>
                        <th>Governante</th>
                        <th>Fundação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cidades)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:2rem; color:#888;">
                                Nenhuma cidade cadastrada ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cidades as $cidade): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($cidade['nome'])) ?>">
                                <td><?= htmlspecialchars($cidade['nome']) ?></td>
                                <td><?= htmlspecialchars($cidade['pais_nome']) ?></td>
                                <td><?= number_format((float) $cidade['populacao'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($cidade['governante_nome'] ?: '-') ?></td>
                                <td><?= $cidade['data_fundacao'] ? date('d/m/Y', strtotime($cidade['data_fundacao'])) : '-' ?></td>
                                <td>
                                    <div class="acoes-tabela">
                                        <a href="editar.php?id=<?= (int) $cidade['id'] ?>" class="acao-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="excluir.php" method="POST" class="form-exclusao"
                                              onsubmit="return confirmarExclusao('<?= htmlspecialchars(addslashes($cidade['nome'])) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $cidade['id'] ?>">
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
