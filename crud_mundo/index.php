<?php
require_once __DIR__ . '/php/config/database.php';

$tituloPagina = 'Dashboard - CRUD Mundo';
$pdo = conectarBanco();

// --- Contagens simples de cada tabela ---
$totalContinentes = (int) $pdo->query('SELECT COUNT(*) FROM continentes')->fetchColumn();
$totalPaises      = (int) $pdo->query('SELECT COUNT(*) FROM paises')->fetchColumn();
$totalCidades     = (int) $pdo->query('SELECT COUNT(*) FROM cidades')->fetchColumn();
$totalGovernantes = (int) $pdo->query('SELECT COUNT(*) FROM governantes')->fetchColumn();

// --- Cidade mais populosa (com o nome do país, via JOIN) ---
$cidadeMaisPopulosa = $pdo->query(
    'SELECT c.nome, c.populacao, p.nome AS pais_nome
       FROM cidades c
       INNER JOIN paises p ON p.id = c.pais_id
       ORDER BY c.populacao DESC
       LIMIT 1'
)->fetch();

// --- Maior país em área (km²) ---
$maiorPais = $pdo->query(
    'SELECT nome, area_km2 FROM paises ORDER BY area_km2 DESC LIMIT 1'
)->fetch();

// --- Últimos registros cadastrados, misturando as 4 tabelas ---
// Usamos UNION ALL para juntar as 4 tabelas em uma única lista,
// cada uma marcando de qual módulo veio, e ordenamos tudo por data.
$ultimosRegistros = $pdo->query(
    "SELECT nome, 'Continente' AS tipo, criado_em FROM continentes
     UNION ALL
     SELECT nome, 'País' AS tipo, criado_em FROM paises
     UNION ALL
     SELECT nome, 'Cidade' AS tipo, criado_em FROM cidades
     UNION ALL
     SELECT nome, 'Governante' AS tipo, criado_em FROM governantes
     ORDER BY criado_em DESC
     LIMIT 5"
)->fetchAll();

// --- DESAFIO EXTRA 1: cidade mais populosa DE CADA país ---
// Subconsulta "m" encontra a maior população por pais_id;
// o JOIN principal usa esse valor para trazer o nome da cidade correspondente.
$cidadeMaisPopulosaPorPais = $pdo->query(
    "SELECT p.nome AS pais_nome, c.nome AS cidade_nome, c.populacao
       FROM paises p
       INNER JOIN cidades c ON c.pais_id = p.id
       INNER JOIN (
           SELECT pais_id, MAX(populacao) AS maior_populacao
             FROM cidades
            GROUP BY pais_id
       ) m ON m.pais_id = c.pais_id AND m.maior_populacao = c.populacao
       ORDER BY p.nome ASC"
)->fetchAll();

// --- DESAFIO EXTRA 2: total de cidades cadastradas POR continente ---
// LEFT JOIN garante que continentes sem nenhuma cidade também apareçam (com total 0).
$cidadesPorContinente = $pdo->query(
    "SELECT co.nome AS continente_nome, COUNT(c.id) AS total_cidades
       FROM continentes co
       LEFT JOIN paises p ON p.continente_id = co.id
       LEFT JOIN cidades c ON c.pais_id = p.id
      GROUP BY co.id, co.nome
      ORDER BY co.nome ASC"
)->fetchAll();

require __DIR__ . '/php/includes/header.php';
require __DIR__ . '/php/includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-gauge"></i> Dashboard</h1>
        </div>

        <div class="cards-grid">
            <div class="card">
                <div class="card-icone"><i class="fa-solid fa-globe"></i></div>
                <div>
                    <p class="card-numero"><?= $totalContinentes ?></p>
                    <p class="card-legenda">Continentes</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icone"><i class="fa-solid fa-flag"></i></div>
                <div>
                    <p class="card-numero"><?= $totalPaises ?></p>
                    <p class="card-legenda">Países</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icone"><i class="fa-solid fa-city"></i></div>
                <div>
                    <p class="card-numero"><?= $totalCidades ?></p>
                    <p class="card-legenda">Cidades</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icone"><i class="fa-solid fa-user-tie"></i></div>
                <div>
                    <p class="card-numero"><?= $totalGovernantes ?></p>
                    <p class="card-legenda">Governantes</p>
                </div>
            </div>
        </div>

        <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <div class="card" style="flex-direction:column; align-items:flex-start; gap:0.4rem;">
                <h3 style="margin:0; font-size:0.95rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-arrow-trend-up" style="color:var(--azul);"></i> Cidade mais populosa
                </h3>
                <?php if ($cidadeMaisPopulosa): ?>
                    <p style="margin:0; font-size:1.1rem; font-weight:600; color:var(--cinza-escuro);">
                        <?= htmlspecialchars($cidadeMaisPopulosa['nome']) ?>, <?= htmlspecialchars($cidadeMaisPopulosa['pais_nome']) ?>
                    </p>
                    <p style="margin:0; color:#757575; font-size:0.85rem;">
                        <?= number_format((float) $cidadeMaisPopulosa['populacao'], 0, ',', '.') ?> habitantes
                    </p>
                <?php else: ?>
                    <p style="margin:0; color:#888;">Nenhuma cidade cadastrada ainda.</p>
                <?php endif; ?>
            </div>

            <div class="card" style="flex-direction:column; align-items:flex-start; gap:0.4rem;">
                <h3 style="margin:0; font-size:0.95rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-expand" style="color:var(--azul);"></i> Maior país em área
                </h3>
                <?php if ($maiorPais): ?>
                    <p style="margin:0; font-size:1.1rem; font-weight:600; color:var(--cinza-escuro);">
                        <?= htmlspecialchars($maiorPais['nome']) ?>
                    </p>
                    <p style="margin:0; color:#757575; font-size:0.85rem;">
                        <?= number_format((float) $maiorPais['area_km2'], 2, ',', '.') ?> km²
                    </p>
                <?php else: ?>
                    <p style="margin:0; color:#888;">Nenhum país cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </div>

        <h2 style="font-size:1.1rem; margin-top:2rem;"><i class="fa-solid fa-clock-rotate-left"></i> Últimos registros cadastrados</h2>
        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Módulo</th>
                        <th>Cadastrado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimosRegistros)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding:2rem; color:#888;">
                                Nenhum registro cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimosRegistros as $registro): ?>
                            <tr>
                                <td><?= htmlspecialchars($registro['nome']) ?></td>
                                <td><?= htmlspecialchars($registro['tipo']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($registro['criado_em'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2 style="font-size:1.1rem; margin-top:2rem;"><i class="fa-solid fa-chart-simple"></i> Desafio extra: estatísticas detalhadas</h2>

        <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); align-items: start;">
            <div>
                <h3 style="font-size:0.95rem; margin: 0 0 0.6rem 0; color:var(--cinza-escuro);">
                    <i class="fa-solid fa-city" style="color:var(--azul);"></i> Cidade mais populosa de cada país
                </h3>
                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>País</th>
                                <th>Cidade</th>
                                <th>População</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cidadeMaisPopulosaPorPais)): ?>
                                <tr>
                                    <td colspan="3" style="text-align:center; padding:1.5rem; color:#888;">
                                        Nenhuma cidade cadastrada ainda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cidadeMaisPopulosaPorPais as $linha): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($linha['pais_nome']) ?></td>
                                        <td><?= htmlspecialchars($linha['cidade_nome']) ?></td>
                                        <td><?= number_format((float) $linha['populacao'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 style="font-size:0.95rem; margin: 0 0 0.6rem 0; color:var(--cinza-escuro);">
                    <i class="fa-solid fa-globe" style="color:var(--azul);"></i> Total de cidades por continente
                </h3>
                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Continente</th>
                                <th>Total de cidades</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cidadesPorContinente)): ?>
                                <tr>
                                    <td colspan="2" style="text-align:center; padding:1.5rem; color:#888;">
                                        Nenhum continente cadastrado ainda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cidadesPorContinente as $linha): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($linha['continente_nome']) ?></td>
                                        <td><?= (int) $linha['total_cidades'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/php/includes/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/php/includes/footer.php'; ?>
