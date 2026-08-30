<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

$pdo = conectarBanco();

// Valida o ID recebido pela URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    definirMensagem('erro', 'Continente inválido.');
    header('Location: index.php');
    exit;
}

// Busca o registro atual
$stmt = $pdo->prepare('SELECT * FROM continentes WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$continenteAtual = $stmt->fetch();

if (!$continenteAtual) {
    definirMensagem('erro', 'Continente não encontrado.');
    header('Location: index.php');
    exit;
}

$erros = [];
$dados = [
    'nome'       => $continenteAtual['nome'],
    'populacao'  => $continenteAtual['populacao'],
    'area_km2'   => $continenteAtual['area_km2'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['nome']      = trim((string) filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW));
    $dados['populacao'] = filter_input(INPUT_POST, 'populacao', FILTER_VALIDATE_INT);
    $dados['area_km2']  = filter_input(INPUT_POST, 'area_km2', FILTER_VALIDATE_FLOAT);

    if ($dados['nome'] === '') {
        $erros[] = 'O nome do continente é obrigatório.';
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
                'UPDATE continentes SET nome = :nome, populacao = :populacao, area_km2 = :area_km2 WHERE id = :id'
            );
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':populacao', $dados['populacao'], PDO::PARAM_INT);
            $stmt->bindParam(':area_km2', $dados['area_km2']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            definirMensagem('sucesso', 'Continente atualizado com sucesso!');
            registrarLog('Editou o continente "' . $dados['nome'] . '" (ID ' . $id . ')');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                $erros[] = 'Já existe outro continente cadastrado com esse nome.';
            } else {
                error_log($e->getMessage());
                $erros[] = 'Ocorreu um erro ao atualizar o continente. Tente novamente.';
            }
        }
    }
}

$tituloPagina = 'Editar Continente - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-pen"></i> Editar Continente</h1>
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
                    <span class="erro-campo">Preencha o nome do continente.</span>
                </div>

                <div class="form-grupo">
                    <label for="populacao">População *</label>
                    <input type="number" id="populacao" name="populacao" min="0"
                           value="<?= htmlspecialchars((string) $dados['populacao']) ?>" required>
                    <span class="erro-campo">Informe uma população válida.</span>
                </div>

                <div class="form-grupo">
                    <label for="area_km2">Área (km²) *</label>
                    <input type="number" id="area_km2" name="area_km2" min="0" step="0.01"
                           value="<?= htmlspecialchars((string) $dados['area_km2']) ?>" required>
                    <span class="erro-campo">Informe uma área válida.</span>
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
