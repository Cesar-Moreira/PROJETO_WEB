-- ============================================================
-- Script isolado: só as tabelas novas do módulo de autenticação.
-- Use este arquivo se o banco bd_mundo JÁ EXISTE e você só
-- precisa adicionar as tabelas de login (evita erro de "já existe").
-- ============================================================

USE bd_mundo;

CREATE TABLE IF NOT EXISTS usuarios (
    usuario             VARCHAR(50)  NOT NULL PRIMARY KEY,
    nome_exibicao       VARCHAR(150) NOT NULL,
    senha               VARCHAR(255) NOT NULL,
    status              ENUM('Ativo', 'Inativo', 'Bloqueado') NOT NULL DEFAULT 'Ativo',
    tipo                ENUM('Administrador', 'Comum') NOT NULL DEFAULT 'Comum',
    qtd_acessos         INT UNSIGNED NOT NULL DEFAULT 0,
    tentativas_erradas  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    criado_em           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS logs (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario      VARCHAR(50)  NOT NULL,
    data_acesso  DATE         NOT NULL,
    hora_acesso  TIME         NOT NULL,
    acao         VARCHAR(255) NOT NULL,

    INDEX idx_logs_usuario (usuario),
    INDEX idx_logs_data (data_acesso),

    CONSTRAINT fk_logs_usuario
        FOREIGN KEY (usuario) REFERENCES usuarios(usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;
