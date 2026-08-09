<?php
/**
 * database.php
 * -------------------------------------------------------------
 * Responsabilidade única: fornecer uma conexão PDO com o MySQL.
 * Nenhum outro arquivo deve montar sua própria conexão — todos
 * chamam conectarBanco() para reaproveitar esta lógica.
 * -------------------------------------------------------------
 */

require_once __DIR__ . '/config.php';

/**
 * Cria (ou retorna, se já existir) a conexão PDO com o banco.
 * Usa Prepared Statements em todo o projeto para evitar SQL Injection.
 *
 * @return PDO
 */
function conectarBanco(): PDO
{
    static $pdo = null; // reaproveita a mesma conexão durante a requisição

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      // erros viram exceções, não avisos silenciosos
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,            // resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,                       // usa prepared statements reais do MySQL
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        return $pdo;
    } catch (PDOException $e) {
        // Nunca exponha detalhes sensíveis (usuário/senha) na tela em produção.
        // Aqui mostramos uma mensagem genérica e registramos o erro real no log do servidor.
        error_log('Erro de conexão com o banco: ' . $e->getMessage());
        die('Não foi possível conectar ao banco de dados. Verifique se o MySQL está ativo e as credenciais no .env estão corretas.');
    }
}
