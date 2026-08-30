<?php
/**
 * seed_usuarios.php
 * -------------------------------------------------------------
 * Script de USO ÚNICO para criar os 2 usuários de teste.
 * Precisa ser um arquivo PHP (e não um INSERT direto no .sql)
 * porque a senha precisa passar pela função password_hash() do
 * PHP — nunca se grava senha em texto puro no banco.
 *
 * Depois de rodar uma vez, pode apagar este arquivo (ou deixar,
 * já que ele não recria os usuários se eles já existirem).
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../php/config/database.php';

$pdo = conectarBanco();

$usuariosTeste = [
    [
        'usuario'       => 'admin',
        'nome_exibicao' => 'Administrador',
        'senha'         => 'admin123',
        'tipo'          => 'Administrador',
    ],
    [
        'usuario'       => 'usuario',
        'nome_exibicao' => 'Usuário Comum',
        'senha'         => 'usuario123',
        'tipo'          => 'Comum',
    ],
];

echo '<div style="font-family:sans-serif; max-width:600px; margin:2rem auto;">';
echo '<h2 style="color:#1565C0;">Criando usuários de teste...</h2>';

foreach ($usuariosTeste as $dadosUsuario) {
    // Verifica se o usuário já existe, para não sobrescrever por engano
    $stmt = $pdo->prepare('SELECT usuario FROM usuarios WHERE usuario = :usuario');
    $stmt->execute([':usuario' => $dadosUsuario['usuario']]);

    if ($stmt->fetch()) {
        echo '<p>⚠️ Usuário <strong>' . htmlspecialchars($dadosUsuario['usuario']) . '</strong> já existe — nada foi alterado.</p>';
        continue;
    }

    $hashSenha = password_hash($dadosUsuario['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (usuario, nome_exibicao, senha, status, tipo, qtd_acessos, tentativas_erradas)
         VALUES (:usuario, :nome_exibicao, :senha, "Ativo", :tipo, 0, 0)'
    );
    $stmt->execute([
        ':usuario'       => $dadosUsuario['usuario'],
        ':nome_exibicao' => $dadosUsuario['nome_exibicao'],
        ':senha'         => $hashSenha,
        ':tipo'          => $dadosUsuario['tipo'],
    ]);

    echo '<p>✅ Usuário <strong>' . htmlspecialchars($dadosUsuario['usuario']) . '</strong> ('
        . htmlspecialchars($dadosUsuario['tipo']) . ') criado com sucesso! Senha: <code>'
        . htmlspecialchars($dadosUsuario['senha']) . '</code></p>';
}

echo '<p style="margin-top:1.5rem;"><a href="../login.php" style="color:#1565C0;">Ir para a tela de login →</a></p>';
echo '</div>';
