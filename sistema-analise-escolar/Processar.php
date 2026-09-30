<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>
    <link rel="stylesheet" href="Estilizar.css">
</head>
<body>

<div class="container">

<?php

$turma = $_POST['turma'];
$quantidade = $_POST['quantidade'];

$nomes = $_POST['nome'];
$nota1 = $_POST['nota1'];
$nota2 = $_POST['nota2'];
$trabalho = $_POST['trabalho'];

$mediaGeral = 0;
$maiorMedia = 0;
$menorMedia = 10;

$aprovados = 0;
$recuperacao = 0;
$reprovados = 0;

$somaTotalNotas = 0;

echo "<h1>Relatório da Turma: $turma</h1>";

echo "<table>
<tr>
<th>Aluno</th>
<th>Média</th>
<th>Raiz da Soma</th>
<th>Diferença</th>
<th>Situação</th>
</tr>";

for($i = 0; $i < $quantidade; $i++) {

    $media = ($nota1[$i] + $nota2[$i] + $trabalho[$i]) / 3;

    $raiz = sqrt(
        $nota1[$i] +
        $nota2[$i] +
        $trabalho[$i]
    );

    $diferenca = abs(
        max($nota1[$i], $nota2[$i], $trabalho[$i]) -
        min($nota1[$i], $nota2[$i], $trabalho[$i])
    );

    if($media >= 7) {
        $situacao = "Aprovado";
        $aprovados++;
    }
    elseif($media >= 5) {
        $situacao = "Recuperação";
        $recuperacao++;
    }
    else {
        $situacao = "Reprovado";
        $reprovados++;
    }

    $mediaGeral += $media;

    if($media > $maiorMedia) {
        $maiorMedia = $media;
    }

    if($media < $menorMedia) {
        $menorMedia = $media;
    }

    $somaTotalNotas +=
        $nota1[$i] +
        $nota2[$i] +
        $trabalho[$i];

    echo "<tr>
        <td>{$nomes[$i]}</td>
        <td>" . number_format($media, 2, ',', '.') . "</td>
        <td>" . number_format($raiz, 2, ',', '.') . "</td>
        <td>" . number_format($diferenca, 2, ',', '.') . "</td>
        <td>$situacao</td>
    </tr>";
}

echo "</table>";

$mediaGeral = $mediaGeral / $quantidade;

$percentualAprovacao =
($aprovados / $quantidade) * 100;

echo "<div class='relatorio'>";

echo "<h2>Relatório Estatístico</h2>";

echo "<p><strong>Média Geral:</strong> "
. number_format($mediaGeral,2,',','.')
. "</p>";

echo "<p><strong>Maior Média:</strong> "
. number_format($maiorMedia,2,',','.')
. "</p>";

echo "<p><strong>Menor Média:</strong> "
. number_format($menorMedia,2,',','.')
. "</p>";

echo "<p><strong>Aprovados:</strong> $aprovados</p>";

echo "<p><strong>Recuperação:</strong> $recuperacao</p>";

echo "<p><strong>Reprovados:</strong> $reprovados</p>";

echo "<p><strong>Percentual de Aprovação:</strong> "
. number_format($percentualAprovacao,2,',','.')
. "%</p>";

echo "<p><strong>Soma Total das Notas:</strong> "
. number_format($somaTotalNotas,2,',','.')
. "</p>";

if($percentualAprovacao >= 70) {
    echo "<p class='mensagem positiva'>
    Excelente desempenho da turma!
    </p>";
}
else {
    echo "<p class='mensagem negativa'>
    A turma precisa melhorar.
    </p>";
}

echo "</div>";

?>

<a href="index.php" class="btn voltar">
    Voltar
</a>

</div>

</body>
</html>