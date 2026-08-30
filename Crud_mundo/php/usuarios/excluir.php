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

$usuarioAlvo = trim((string) filter_input(INPUT_POST, 'usuario', FILTER_UNSAFE_RAW));

if ($usuarioAlvo === '') {
    definirMensagem('erro', 'Usuário inválido.');
    header('Location: index.php');
    exit;
}

// Ninguém pode se auto-excluir enquanto estiver logado (evitaria ficar sem acesso)
if ($usuarioAlvo === $_SESSION['usuario']) {
    definirMensagem('erro', 'Você não pode excluir seu próprio usuário enquanto estiver logado.');
    header('Location: index.php');
    exit;
}

$pdo = conectarBanco();

// Proteção extra: nunca deixar o sistema sem nenhum administrador
$stmtAlvo = $pdo->prepare('SELECT tipo FROM usuarios WHERE usuario = :usuario');
$stmtAlvo->execute([':usuario' => $usuarioAlvo]);
$usuarioAlvoInfo = $stmtAlvo->fetch();

if ($usuarioAlvoInfo && $usuarioAlvoInfo['tipo'] === 'Administrador') {
    $totalAdmins = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'Administrador'")->fetchColumn();
    if ($totalAdmins <= 1) {
        definirMensagem('erro', 'Não é possível excluir o único administrador do sistema.');
        header('Location: index.php');
        exit;
    }
}

try {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE usuario = :usuario');
    $stmt->execute([':usuario' => $usuarioAlvo]);

    if ($stmt->rowCount() > 0) {
        definirMensagem('sucesso', 'Usuário excluído com sucesso!');
        registrarLog('Excluiu o usuário "' . $usuarioAlvo . '"');
    } else {
        definirMensagem('erro', 'Usuário não encontrado.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    definirMensagem('erro', 'Ocorreu um erro ao excluir o usuário.');
}

header('Location: index.php');
exit;
