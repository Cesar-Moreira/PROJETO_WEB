<?php
/**
 * auth.php
 * -------------------------------------------------------------
 * Toda a lógica de autenticação do sistema mora aqui:
 * - Verificar se está logado
 * - Exigir login / exigir ser Administrador nas páginas protegidas
 * - Forçar troca de senha no primeiro acesso
 * - Registrar ações no log
 *
 * Esse arquivo é incluído (via require) no topo de toda página
 * que precisa de proteção — dashboard, os 4 módulos, usuários e logs.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/database.php';

/**
 * Verifica se existe uma sessão de usuário ativa.
 */
function estaLogado(): bool
{
    return isset($_SESSION['usuario']);
}

/**
 * Barra o acesso a quem não estiver logado, mandando para a tela de login.
 * Deve ser a PRIMEIRA coisa chamada em toda página protegida.
 */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: ' . caminhoLogin());
        exit;
    }

    // Se o usuário ainda precisa trocar a senha do primeiro acesso,
    // ele não pode navegar para mais nenhum lugar até fazer isso.
    $paginaAtual = basename($_SERVER['SCRIPT_NAME']);
    if (!empty($_SESSION['forcar_troca_senha']) && $paginaAtual !== 'trocar_senha.php' && $paginaAtual !== 'logout.php') {
        header('Location: ' . caminhoRaiz() . '/trocar_senha.php');
        exit;
    }
}

/**
 * Além de exigir login, bloqueia usuários do tipo "Comum".
 * Usada em cadastrar.php / editar.php / excluir.php de todos os módulos,
 * e em todas as páginas do módulo de usuários e de logs.
 */
function exigirAdministrador(): void
{
    exigirLogin();

    if (($_SESSION['tipo'] ?? '') !== 'Administrador') {
        definirMensagem('erro', 'Acesso restrito a administradores.');
        header('Location: ' . caminhoRaiz() . '/index.php');
        exit;
    }
}

/**
 * Calcula quantos níveis de pasta a página atual está da raiz do site,
 * para montar links relativos corretos (login.php, index.php etc.)
 * não importa se quem chamou está em / , /php/continentes/ etc.
 */
function caminhoRaiz(): string
{
    // URL_BASE já é a raiz do projeto (ex.: "/crud_mundo"), definida em config.php
    return URL_BASE;
}

function caminhoLogin(): string
{
    return caminhoRaiz() . '/login.php';
}

/**
 * Registra uma ação de escrita (cadastro, edição ou exclusão) na tabela logs.
 * Chamada logo após o INSERT/UPDATE/DELETE dar certo em qualquer módulo.
 */
function registrarLog(string $acao): void
{
    if (!estaLogado()) {
        return; // segurança extra: nunca deveria acontecer, mas evita erro se acontecer
    }

    try {
        $pdo = conectarBanco();
        $stmt = $pdo->prepare(
            'INSERT INTO logs (usuario, data_acesso, hora_acesso, acao) VALUES (:usuario, :data, :hora, :acao)'
        );
        $stmt->execute([
            ':usuario' => $_SESSION['usuario'],
            ':data'    => date('Y-m-d'),
            ':hora'    => date('H:i:s'),
            ':acao'    => $acao,
        ]);
    } catch (PDOException $e) {
        // Uma falha ao registrar log não pode travar a operação principal do usuário,
        // mas precisa ficar registrada no log de erros do servidor.
        error_log('Falha ao registrar log: ' . $e->getMessage());
    }
}
