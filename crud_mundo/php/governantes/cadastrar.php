<?php
require_once __DIR__ . '/../config/database.php';

$erros = [];
$dados = [
    'nome'             => '',
    'partido_politico' => '',
    'data_nascimento'  => '',
    'inicio_mandato'   => '',
    'fim_mandato'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
        $erros[] = 'Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.';
    }

    $dados['nome']             = trim((string) filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW));
    $dados['partido_politico'] = trim((string) filter_input(INPUT_POST, 'partido_politico', FILTER_UNSAFE_RAW));
    $dados['data_nascimento']  = trim((string) filter_input(INPUT_POST, 'data_nascimento', FILTER_UNSAFE_RAW));
    $dados['inicio_mandato']   = trim((string) filter_input(INPUT_POST, 'inicio_mandato', FILTER_UNSAFE_RAW));
    $dados['fim_mandato']      = trim((string) filter_input(INPUT_POST, 'fim_mandato', FILTER_UNSAFE_RAW));

    if ($dados['nome'] === '') {
        $erros[] = 'O nome do governante é obrigatório.';
    }

    // Validação de data: precisa existir e estar no formato AAAA-MM-DD (padrão do <input type="date">)
    $dataNascimentoValida = DateTime::createFromFormat('Y-m-d', $dados['data_nascimento']);
    if ($dados['data_nascimento'] === '' || !$dataNascimentoValida) {
        $erros[] = 'Informe uma data de nascimento válida.';
    } elseif ($dataNascimentoValida > new DateTime()) {
        $erros[] = 'A data de nascimento não pode ser no futuro.';
    }

    // Datas de mandato são opcionais, mas se preenchidas precisam ser válidas
    if ($dados['inicio_mandato'] !== '' && !DateTime::createFromFormat('Y-m-d', $dados['inicio_mandato'])) {
        $erros[] = 'A data de início de mandato informada é inválida.';
    }
    if ($dados['fim_mandato'] !== '' && !DateTime::createFromFormat('Y-m-d', $dados['fim_mandato'])) {
        $erros[] = 'A data de fim de mandato informada é inválida.';
    }
    if ($dados['inicio_mandato'] !== '' && $dados['fim_mandato'] !== '' && $dados['fim_mandato'] < $dados['inicio_mandato']) {
        $erros[] = 'A data de fim de mandato não pode ser anterior à data de início.';
    }

    if (empty($erros)) {
        try {
            $pdo = conectarBanco();
            $stmt = $pdo->prepare(
                'INSERT INTO governantes (nome, partido_politico, data_nascimento, inicio_mandato, fim_mandato)
                 VALUES (:nome, :partido, :nascimento, :inicio, :fim)'
            );
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':partido', $dados['partido_politico'] ?: null);
            $stmt->bindValue(':nascimento', $dados['data_nascimento']);
            $stmt->bindValue(':inicio', $dados['inicio_mandato'] ?: null);
            $stmt->bindValue(':fim', $dados['fim_mandato'] ?: null);
            $stmt->execute();
            // Observação: o campo "idade" NÃO é enviado aqui de propósito.
            // Uma trigger no banco (trg_governantes_idade_insert) calcula
            // automaticamente a partir da data de nascimento.

            definirMensagem('sucesso', 'Governante "' . $dados['nome'] . '" cadastrado com sucesso!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erros[] = 'Ocorreu um erro ao salvar o governante. Tente novamente.';
        }
    }
}

$tituloPagina = 'Novo Governante - CRUD Mundo';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-plus"></i> Novo Governante</h1>
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
                    <span class="erro-campo">Preencha o nome do governante.</span>
                </div>

                <div class="form-grupo">
                    <label for="partido_politico">Partido político</label>
                    <input type="text" id="partido_politico" name="partido_politico" value="<?= htmlspecialchars($dados['partido_politico']) ?>">
                </div>

                <div class="form-grupo">
                    <label for="data_nascimento">Data de nascimento *</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($dados['data_nascimento']) ?>" required>
                    <span class="erro-campo">Informe uma data de nascimento válida.</span>
                    <small style="color:#888;">A idade é calculada automaticamente pelo sistema.</small>
                </div>

                <div class="form-grupo">
                    <label for="inicio_mandato">Início do mandato</label>
                    <input type="date" id="inicio_mandato" name="inicio_mandato" value="<?= htmlspecialchars($dados['inicio_mandato']) ?>">
                </div>

                <div class="form-grupo">
                    <label for="fim_mandato">Fim do mandato</label>
                    <input type="date" id="fim_mandato" name="fim_mandato" value="<?= htmlspecialchars($dados['fim_mandato']) ?>">
                    <small style="color:#888;">Deixe em branco se o mandato ainda está em andamento.</small>
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
