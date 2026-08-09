-- ============================================================
-- Banco de Dados: bd_mundo
-- Projeto: CRUD Mundo - Programação Web
-- Descrição: Gerenciamento de Continentes, Países, Cidades e Governantes
-- ============================================================

-- Criação do banco (só execute se ainda não existir)
CREATE DATABASE IF NOT EXISTS bd_mundo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bd_mundo;

-- ------------------------------------------------------------
-- Tabela: continentes
-- Independente (não possui FK). É a "raiz" do relacionamento.
-- ------------------------------------------------------------
CREATE TABLE continentes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(100)    NOT NULL,
    populacao      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2       DECIMAL(15,2)   NOT NULL DEFAULT 0,
    total_paises   INT UNSIGNED    NOT NULL DEFAULT 0,
    criado_em      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_continente_nome (nome)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: governantes
-- Independente. País e cidade referenciam governantes,
-- e não o contrário — assim um governante pode existir
-- antes de ser vinculado a qualquer lugar.
-- ------------------------------------------------------------
CREATE TABLE governantes (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome               VARCHAR(150) NOT NULL,
    partido_politico   VARCHAR(100) NULL,
    data_nascimento    DATE         NOT NULL,
    idade              TINYINT UNSIGNED NULL, -- calculada por trigger, não digitada pelo usuário
    inicio_mandato     DATE         NULL,
    fim_mandato        DATE         NULL,
    criado_em          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_governante_nome (nome)
) ENGINE=InnoDB;

-- Trigger: calcula idade automaticamente ao inserir
DELIMITER $$
CREATE TRIGGER trg_governantes_idade_insert
BEFORE INSERT ON governantes
FOR EACH ROW
BEGIN
    SET NEW.idade = TIMESTAMPDIFF(YEAR, NEW.data_nascimento, CURDATE());
END$$

-- Trigger: recalcula idade se a data de nascimento for editada
CREATE TRIGGER trg_governantes_idade_update
BEFORE UPDATE ON governantes
FOR EACH ROW
BEGIN
    SET NEW.idade = TIMESTAMPDIFF(YEAR, NEW.data_nascimento, CURDATE());
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- Tabela: paises
-- Depende de continentes (obrigatório) e governantes (opcional).
-- ------------------------------------------------------------
CREATE TABLE paises (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150)    NOT NULL,
    continente_id    INT UNSIGNED    NOT NULL,
    populacao        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2         DECIMAL(15,2)   NOT NULL DEFAULT 0,
    idioma           VARCHAR(100)    NULL,
    governante_id    INT UNSIGNED    NULL,
    clima            VARCHAR(100)    NULL,
    regime_politico  VARCHAR(100)    NULL,
    moeda            VARCHAR(60)     NULL,
    criado_em        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_pais_nome (nome),
    INDEX idx_pais_continente (continente_id),
    INDEX idx_pais_governante (governante_id),

    CONSTRAINT fk_pais_continente
        FOREIGN KEY (continente_id) REFERENCES continentes(id)
        ON DELETE RESTRICT  -- não deixa apagar continente com países cadastrados
        ON UPDATE CASCADE,

    CONSTRAINT fk_pais_governante
        FOREIGN KEY (governante_id) REFERENCES governantes(id)
        ON DELETE SET NULL  -- apagar o governante não apaga o país, só desvincula
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: cidades
-- Depende de paises (obrigatório) e governantes (opcional).
-- ------------------------------------------------------------
CREATE TABLE cidades (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150)    NOT NULL,
    pais_id          INT UNSIGNED    NOT NULL,
    populacao        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2         DECIMAL(15,2)   NOT NULL DEFAULT 0,
    clima            VARCHAR(100)    NULL,
    governante_id    INT UNSIGNED    NULL,
    data_fundacao    DATE            NULL,
    criado_em        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cidade_pais (pais_id),
    INDEX idx_cidade_governante (governante_id),
    INDEX idx_cidade_nome (nome),

    CONSTRAINT fk_cidade_pais
        FOREIGN KEY (pais_id) REFERENCES paises(id)
        ON DELETE RESTRICT  -- não deixa apagar país com cidades cadastradas
        ON UPDATE CASCADE,

    CONSTRAINT fk_cidade_governante
        FOREIGN KEY (governante_id) REFERENCES governantes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Triggers para manter total_paises em continentes sincronizado
-- (evita que o campo fique desatualizado manualmente)
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_paises_after_insert
AFTER INSERT ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes
       SET total_paises = (SELECT COUNT(*) FROM paises WHERE continente_id = NEW.continente_id)
     WHERE id = NEW.continente_id;
END$$

CREATE TRIGGER trg_paises_after_delete
AFTER DELETE ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes
       SET total_paises = (SELECT COUNT(*) FROM paises WHERE continente_id = OLD.continente_id)
     WHERE id = OLD.continente_id;
END$$
DELIMITER ;

-- ============================================================
-- DADOS DE EXEMPLO (opcional, ajuda a testar o CRUD já na 1ª execução)
-- ============================================================
INSERT INTO continentes (nome, populacao, area_km2) VALUES
('América do Sul', 434000000, 17840000),
('Europa', 746000000, 10180000);

INSERT INTO governantes (nome, partido_politico, data_nascimento, inicio_mandato, fim_mandato) VALUES
('Governante Exemplo 1', 'Partido A', '1970-05-12', '2023-01-01', NULL),
('Governante Exemplo 2', 'Partido B', '1965-11-03', '2022-05-01', NULL);

INSERT INTO paises (nome, continente_id, populacao, area_km2, idioma, governante_id, clima, regime_politico, moeda) VALUES
('Brasil', 1, 214000000, 8515767, 'Português', 1, 'Tropical', 'República Federativa', 'Real'),
('Portugal', 2, 10300000, 92212, 'Português', 2, 'Mediterrâneo', 'República Semipresidencialista', 'Euro');

INSERT INTO cidades (nome, pais_id, populacao, area_km2, clima, governante_id, data_fundacao) VALUES
('São Paulo', 1, 12300000, 1521, 'Subtropical', NULL, '1554-01-25'),
('Lisboa', 2, 545000, 100, 'Mediterrâneo', NULL, '1200-01-01');
