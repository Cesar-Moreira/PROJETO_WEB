<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$pdo = conectarBanco();

// Listas para popular os <select> do formulário
$continentes = $pdo->query('SELECT id, nome FROM continentes ORDER BY nome ASC')->fetchAll();
$governantes = $pdo->query('SELECT id, nome FROM governantes ORDER BY nome ASC')->fetchAll();

$erros = [];
$dados = [
    'nome' => '', 'continente_id' => '', 'populacao' => '', 'area_km2' => '',
    'idioma' => '', 'governante_id' => '', 'clima' => '', 'regime_politico' => '', 'moeda' => '',
];

// Se não existe nenhum continente cadastrado, é impossível criar um país (regra de negócio)
if (empty($continentes)) {
    definirMensagem('erro', 'Cadastre pelo menos um continente antes de cadastrar um país.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['nome']            = trim((string) filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW));
    $dados['continente_id']   = filter_input(INPUT_POST, 'continente_id', FILTER_VALIDATE_INT);
    $dados['populacao']       = filter_input(INPUT_POST, 'populacao', FILTER_VALIDATE_INT);
    $dados['area_km2']        = filter_input(INPUT_POST, 'area_km2', FILTER_VALIDATE_FLOAT);
    $dados['idioma']          = trim((string) filter_input(INPUT_POST, 'idioma', FILTER_UNSAFE_RAW));
    $dados['governante_id']   = filter_input(INPUT_POST, 'governante_id', FILTER_VALIDATE_INT);
    $dados['clima']           = trim((string) filter_input(INPUT_POST, 'clima', FILTER_UNSAFE_RAW));
    $dados['regime_politico'] = trim((string) filter_input(INPUT_POST, 'regime_politico', FILTER_UNSAFE_RAW));
    $dados['moeda']           = trim((string) filter_input(INPUT_POST, 'moeda', FILTER_UNSAFE_RAW));

    if ($dados['nome'] === '') {
        $erros[] = 'O nome do país é obrigatório.';
    }
    if (!$dados['continente_id']) {
        $erros[] = 'Selecione um continente.';
    }
    if ($dados['populacao'] === false || $dados['populacao'] === null || $dados['populacao'] < 0) {
        $erros[] = 'Informe uma população válida.';
    }
    if ($dados['area_km2'] === false || $dados['area_km2'] === null || $dados['area_km2'] < 0) {
        $erros[] = 'Informe uma área válida.';
    }

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO paises (nome, continente_id, populacao, area_km2, idioma, governante_id, clima, regime_politico, moeda)
                 VALUES (:nome, :continente_id, :populacao, :area_km2, :idioma, :governante_id, :clima, :regime, :moeda)'
            );
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':continente_id', $dados['continente_id'], PDO::PARAM_INT);
            $stmt->bindValue(':populacao', $dados['populacao'], PDO::PARAM_INT);
            $stmt->bindValue(':area_km2', $dados['area_km2']);
            $stmt->bindValue(':idioma', $dados['idioma'] ?: null);
            $stmt->bindValue(':governante_id', $dados['governante_id'] ?: null, $dados['governante_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':clima', $dados['clima'] ?: null);
            $stmt->bindValue(':regime', $dados['regime_politico'] ?: null);
            $stmt->bindValue(':moeda', $dados['moeda'] ?: null);
            $stmt->execute();
            // A trigger trg_paises_after_insert atualiza sozinha o total_paises do continente

            definirMensagem('sucesso', 'País "' . $dados['nome'] . '" cadastrado com sucesso!');
            registrarLog('Cadastrou o país "' . $dados['nome'] . '"');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                $erros[] = 'Já existe um país cadastrado com esse nome.';
            } else {
                error_log($e->getMessage());
                $erros[] = 'Ocorreu um erro ao salvar o país. Tente novamente.';
            }
        }
    }
}

$tituloPagina = 'Novo País - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-plus"></i> Novo País</h1>
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
                    <span class="erro-campo">Preencha o nome do país.</span>
                </div>

                <div class="form-grupo">
                    <label for="continente_id">Continente *</label>
                    <select id="continente_id" name="continente_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($continentes as $continente): ?>
                            <option value="<?= $continente['id'] ?>" <?= (string) $dados['continente_id'] === (string) $continente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($continente['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="erro-campo">Selecione um continente.</span>
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
                    <label for="idioma">Idioma</label>
                    <input type="text" id="idioma" name="idioma" value="<?= htmlspecialchars($dados['idioma']) ?>">
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
                    <label for="clima">Clima</label>
                    <input type="text" id="clima" name="clima" value="<?= htmlspecialchars($dados['clima']) ?>">
                </div>

                <div class="form-grupo">
                    <label for="regime_politico">Regime político</label>
                    <input type="text" id="regime_politico" name="regime_politico" value="<?= htmlspecialchars($dados['regime_politico']) ?>">
                </div>

                <div class="form-grupo">
                    <label for="moeda">Moeda</label>
                    <input type="text" id="moeda" name="moeda" value="<?= htmlspecialchars($dados['moeda']) ?>">
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
