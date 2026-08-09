<?php
require_once __DIR__ . '/../config/database.php';

// Só aceita exclusão via formulário POST (não mais por link/GET)
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
    definirMensagem('erro', 'Continente inválido.');
    header('Location: index.php');
    exit;
}

$pdo = conectarBanco();

try {
    $stmt = $pdo->prepare('DELETE FROM continentes WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        definirMensagem('sucesso', 'Continente excluído com sucesso!');
    } else {
        definirMensagem('erro', 'Continente não encontrado.');
    }
} catch (PDOException $e) {
    if ((int) $e->getCode() === 23000) {
        // Erro de chave estrangeira: existem países vinculados a este continente (ON DELETE RESTRICT)
        definirMensagem('erro', 'Não é possível excluir este continente porque existem países vinculados a ele. Exclua ou mova os países primeiro.');
    } else {
        error_log($e->getMessage());
        definirMensagem('erro', 'Ocorreu um erro ao excluir o continente.');
    }
}

header('Location: index.php');
exit;
