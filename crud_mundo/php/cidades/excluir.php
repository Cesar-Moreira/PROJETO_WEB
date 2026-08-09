<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirMensagem('erro', 'Sua sessão expirou ou a requisição é inválida. Tente novamente.');
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definirMensagem('erro', 'Cidade inválida.');
    header('Location: index.php');
    exit;
}

$pdo = conectarBanco();

try {
    // Nenhuma outra tabela referencia "cidades", então a exclusão
    // aqui é sempre simples - não existe risco de erro de FK.
    $stmt = $pdo->prepare('DELETE FROM cidades WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        definirMensagem('sucesso', 'Cidade excluída com sucesso!');
    } else {
        definirMensagem('erro', 'Cidade não encontrada.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    definirMensagem('erro', 'Ocorreu um erro ao excluir a cidade.');
}

header('Location: index.php');
exit;
