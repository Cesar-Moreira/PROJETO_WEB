<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Estatístico Escolar</title>
    <link rel="stylesheet" href="Estilizar.css">
</head>
<body>

<div class="container">
    <h1>Sistema de Análise Estatística</h1>

    <form action="" method="POST" class="form-box">
        <label>Nome da Turma</label>
        <input type="text" name="turma" required>

        <label>Quantidade de Alunos</label>
        <input type="number" name="quantidade" min="1" required>

        <button type="submit" class="btn">
            Gerar Campos
        </button>
    </form>

    <?php
    if(isset($_POST['quantidade'])) {
        $quantidade = $_POST['quantidade'];
        $turma = $_POST['turma'];
    ?>

    <form action="Processar.php" method="POST" class="form-box">
        <input type="hidden" name="turma" value="<?= $turma ?>">
        <input type="hidden" name="quantidade" value="<?= $quantidade ?>">

        <h2>Cadastro dos Alunos</h2>

        <?php for($i = 1; $i <= $quantidade; $i++) { ?>
            <div class="aluno-box">
                <h3>Aluno <?= $i ?></h3>

                <input type="text" name="nome[]" placeholder="Nome do aluno" required>

                <input type="number" step="0.1" name="nota1[]" placeholder="Nota Prova 1" required>

                <input type="number" step="0.1" name="nota2[]" placeholder="Nota Prova 2" required>

                <input type="number" step="0.1" name="trabalho[]" placeholder="Nota Trabalho" required>
            </div>
        <?php } ?>

        <button type="submit" class="btn">
            Gerar Relatório
        </button>
    </form>

    <?php } ?>
</div>

</body>
</html>