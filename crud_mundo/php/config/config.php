<?php
/**
 * config.php
 * -------------------------------------------------------------
 * Responsabilidade única: configurar o ambiente da aplicação.
 * - Carrega variáveis do arquivo .env (sem precisar de Composer)
 * - Define constantes globais usadas no projeto todo
 * - Inicia a sessão (usada para mensagens de sucesso/erro)
 *
 * Este arquivo NUNCA imprime HTML. Ele só prepara o ambiente.
 * -------------------------------------------------------------
 */

// Exibir erros durante desenvolvimento. Em produção, troque para 0.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Carrega manualmente as variáveis do arquivo .env para dentro de $_ENV.
 * Como o projeto não pode usar frameworks/bibliotecas externas,
 * fazemos um parser simples de arquivo "CHAVE=valor".
 */
function carregarEnv(string $caminhoArquivo): void
{
    if (!file_exists($caminhoArquivo)) {
        die('Arquivo .env não encontrado. Copie o .env.example para .env e configure suas credenciais.');
    }

    $linhas = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        // Ignora comentários
        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }

        if (str_contains($linha, '=')) {
            [$chave, $valor] = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);
            $_ENV[$chave] = $valor;
        }
    }
}

carregarEnv(__DIR__ . '/../../.env');

// Constantes globais de conexão (lidas do .env)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'bd_mundo');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// URL base do projeto — ajuste conforme o nome da sua pasta no XAMPP/htdocs
define('URL_BASE', '/crud_mundo');

// Inicia a sessão (necessária para mensagens flash de sucesso/erro)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Funções auxiliares de mensagem flash (sucesso/erro),
 * usadas por todos os módulos para dar feedback ao usuário
 * após um redirecionamento (padrão Post-Redirect-Get).
 */
function definirMensagem(string $tipo, string $texto): void
{
    $_SESSION['mensagem'] = [
        'tipo'  => $tipo, // 'sucesso' ou 'erro'
        'texto' => $texto,
    ];
}

function obterMensagem(): ?array
{
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']); // exibida uma única vez
        return $mensagem;
    }
    return null;
}

/**
 * Proteção CSRF (Cross-Site Request Forgery).
 * -------------------------------------------------------------
 * Gera um "código secreto" único por sessão e exige que todo
 * formulário (cadastrar, editar, excluir) devolva esse mesmo
 * código junto com os dados. Isso impede que outro site consiga
 * disparar uma exclusão ou cadastro no seu sistema escondido
 * num link ou botão malicioso, sem você saber.
 */
function gerarTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $tokenRecebido): bool
{
    if (empty($_SESSION['csrf_token']) || $tokenRecebido === null) {
        return false;
    }
    // hash_equals evita "timing attacks" (comparação seria vulnerável com ===)
    return hash_equals($_SESSION['csrf_token'], $tokenRecebido);
}
