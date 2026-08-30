<?php
require_once __DIR__ . '/../config/auth.php';
exigirAdministrador();

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
    definirMensagem('erro', 'Governante inválido.');
    header('Location: index.php');
    exit;
}

$pdo = conectarBanco();

$stmtNome = $pdo->prepare('SELECT nome FROM governantes WHERE id = :id');
$stmtNome->bindParam(':id', $id, PDO::PARAM_INT);
$stmtNome->execute();
$nomeGovernante = $stmtNome->fetchColumn();

try {
    // governante_id em países/cidades usa ON DELETE SET NULL, então
    // excluir um governante não é bloqueado por integridade referencial.
    $stmt = $pdo->prepare('DELETE FROM governantes WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        definirMensagem('sucesso', 'Governante excluído com sucesso!');
        registrarLog('Excluiu o governante "' . $nomeGovernante . '" (ID ' . $id . ')');
    } else {
        definirMensagem('erro', 'Governante não encontrado.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    definirMensagem('erro', 'Ocorreu um erro ao excluir o governante.');
}

header('Location: index.php');
exit;
