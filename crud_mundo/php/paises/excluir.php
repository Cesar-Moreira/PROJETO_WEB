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
    definirMensagem('erro', 'País inválido.');
    header('Location: index.php');
    exit;
}

$pdo = conectarBanco();

try {
    $stmt = $pdo->prepare('DELETE FROM paises WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        definirMensagem('sucesso', 'País excluído com sucesso!');
        // A trigger trg_paises_after_delete já atualiza o total_paises automaticamente
    } else {
        definirMensagem('erro', 'País não encontrado.');
    }
} catch (PDOException $e) {
    if ((int) $e->getCode() === 23000) {
        // Existem cidades vinculadas a este país (ON DELETE RESTRICT)
        definirMensagem('erro', 'Não é possível excluir este país porque existem cidades vinculadas a ele. Exclua as cidades primeiro.');
    } else {
        error_log($e->getMessage());
        definirMensagem('erro', 'Ocorreu um erro ao excluir o país.');
    }
}

header('Location: index.php');
exit;
