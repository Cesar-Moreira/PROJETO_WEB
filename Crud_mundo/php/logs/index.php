<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$tituloPagina = 'Logs - CRUD Mundo';

$pdo = conectarBanco();
// Mostra os 200 registros mais recentes primeiro - suficiente para acompanhar
// a atividade do sistema sem sobrecarregar a página com um histórico gigante.
$stmt = $pdo->query(
    'SELECT * FROM logs ORDER BY data_acesso DESC, hora_acesso DESC, id DESC LIMIT 200'
);
$logs = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-list-check"></i> Logs do sistema</h1>
        </div>

        <div class="barra-topo">
            <div class="campo-pesquisa">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="campoPesquisa" placeholder="Pesquisar por usuário ou ação...">
            </div>
        </div>

        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:2rem; color:#888;">
                                Nenhuma ação registrada ainda. Cadastre, edite ou exclua algo em qualquer módulo para começar a ver logs aqui.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr data-nome="<?= htmlspecialchars(mb_strtolower($log['usuario'] . ' ' . $log['acao'])) ?>">
                                <td><?= date('d/m/Y', strtotime($log['data_acesso'])) ?></td>
                                <td><?= date('H:i:s', strtotime($log['hora_acesso'])) ?></td>
                                <td><strong><?= htmlspecialchars($log['usuario']) ?></strong></td>
                                <td><?= htmlspecialchars($log['acao']) ?></td>
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
