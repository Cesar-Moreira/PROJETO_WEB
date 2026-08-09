<?php
require_once __DIR__ . '/../config/database.php';

$pdo = conectarBanco();

$paisesDisponiveis = $pdo->query('SELECT id, nome FROM paises ORDER BY nome ASC')->fetchAll();
$governantes = $pdo->query('SELECT id, nome FROM governantes ORDER BY nome ASC')->fetchAll();

if (empty($paisesDisponiveis)) {
    definirMensagem('erro', 'Cadastre pelo menos um país antes de cadastrar uma cidade.');
    header('Location: index.php');
    exit;
}

$erros = [];
$dados = [
    'nome' => '', 'pais_id' => '', 'populacao' => '', 'area_km2' => '',
    'clima' => '', 'governante_id' => '', 'data_fundacao' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['nome']          = trim((string) filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW));
    $dados['pais_id']       = filter_input(INPUT_POST, 'pais_id', FILTER_VALIDATE_INT);
    $dados['populacao']     = filter_input(INPUT_POST, 'populacao', FILTER_VALIDATE_INT);
    $dados['area_km2']      = filter_input(INPUT_POST, 'area_km2', FILTER_VALIDATE_FLOAT);
    $dados['clima']         = trim((string) filter_input(INPUT_POST, 'clima', FILTER_UNSAFE_RAW));
    $dados['governante_id'] = filter_input(INPUT_POST, 'governante_id', FILTER_VALIDATE_INT);
    $dados['data_fundacao'] = trim((string) filter_input(INPUT_POST, 'data_fundacao', FILTER_UNSAFE_RAW));

    if ($dados['nome'] === '') {
        $erros[] = 'O nome da cidade é obrigatório.';
    }
    if (!$dados['pais_id']) {
        $erros[] = 'Selecione um país.';
    }
    if ($dados['populacao'] === false || $dados['populacao'] === null || $dados['populacao'] < 0) {
        $erros[] = 'Informe uma população válida.';
    }
    if ($dados['area_km2'] === false || $dados['area_km2'] === null || $dados['area_km2'] < 0) {
        $erros[] = 'Informe uma área válida.';
    }
    if ($dados['data_fundacao'] !== '' && !DateTime::createFromFormat('Y-m-d', $dados['data_fundacao'])) {
        $erros[] = 'A data de fundação informada é inválida.';
    }

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO cidades (nome, pais_id, populacao, area_km2, clima, governante_id, data_fundacao)
                 VALUES (:nome, :pais_id, :populacao, :area_km2, :clima, :governante_id, :data_fundacao)'
            );
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':pais_id', $dados['pais_id'], PDO::PARAM_INT);
            $stmt->bindValue(':populacao', $dados['populacao'], PDO::PARAM_INT);
            $stmt->bindValue(':area_km2', $dados['area_km2']);
            $stmt->bindValue(':clima', $dados['clima'] ?: null);
            $stmt->bindValue(':governante_id', $dados['governante_id'] ?: null, $dados['governante_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':data_fundacao', $dados['data_fundacao'] ?: null);
            $stmt->execute();

            definirMensagem('sucesso', 'Cidade "' . $dados['nome'] . '" cadastrada com sucesso!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erros[] = 'Ocorreu um erro ao salvar a cidade. Tente novamente.';
        }
    }
}

$tituloPagina = 'Nova Cidade - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-plus"></i> Nova Cidade</h1>
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
                    <label for="nome">Nome *</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                    <span class="erro-campo">Preencha o nome da cidade.</span>
                </div>

                <div class="form-grupo">
                    <label for="pais_id">País *</label>
                    <select id="pais_id" name="pais_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($paisesDisponiveis as $pais): ?>
                            <option value="<?= $pais['id'] ?>" <?= (string) $dados['pais_id'] === (string) $pais['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pais['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="erro-campo">Selecione um país.</span>
                </div>

                <div class="form-grupo">
                    <label for="populacao">População *</label>
                    <input type="number" id="populacao" name="populacao" min="0" value="<?= htmlspecialchars((string) ($dados['populacao'] ?: '')) ?>" required>
                    <span class="erro-campo">Informe uma população válida.</span>
                </div>

                <div class="form-grupo">
                    <label for="area_km2">Área (km²) *</label>
                    <input type="number" id="area_km2" name="area_km2" min="0" step="0.01" value="<?= htmlspecialchars((string) ($dados['area_km2'] ?: '')) ?>" required>
                    <span class="erro-campo">Informe uma área válida.</span>
                </div>

                <div class="form-grupo">
                    <label for="clima">Clima</label>
                    <input type="text" id="clima" name="clima" value="<?= htmlspecialchars($dados['clima']) ?>">
                </div>

                <div class="form-grupo">
                    <label for="governante_id">Governante</label>
                    <select id="governante_id" name="governante_id">
                        <option value="">Nenhum</option>
                        <?php foreach ($governantes as $governante): ?>
                            <option value="<?= $governante['id'] ?>" <?= (string) $dados['governante_id'] === (string) $governante['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($governante['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="data_fundacao">Data de fundação</label>
                    <input type="date" id="data_fundacao" name="data_fundacao" value="<?= htmlspecialchars($dados['data_fundacao']) ?>">
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
