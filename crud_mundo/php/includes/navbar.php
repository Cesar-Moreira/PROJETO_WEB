<?php
/**
 * navbar.php
 * -------------------------------------------------------------
 * Menu superior, presente em todas as páginas.
 * Usa a variável global $_SERVER['REQUEST_URI'] para destacar
 * (classe "ativo") o link do módulo em que o usuário está.
 * -------------------------------------------------------------
 */
$uriAtual = $_SERVER['REQUEST_URI'];

function linkAtivo(string $trecho, string $uriAtual): string
{
    return str_contains($uriAtual, $trecho) ? 'ativo' : '';
}
?>
<header class="navbar">
    <div class="navbar-marca">
        <i class="fa-solid fa-earth-americas"></i>
        <span>CRUD Mundo</span>
    </div>

    <button class="navbar-toggle" id="navbarToggle" aria-label="Abrir menu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="navbar-links" id="navbarLinks">
        <a href="<?= URL_BASE ?>/index.php" class="<?= linkAtivo('/index.php', $uriAtual) ?>">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        <a href="<?= URL_BASE ?>/php/continentes/index.php" class="<?= linkAtivo('/continentes/', $uriAtual) ?>">
            <i class="fa-solid fa-globe"></i> Continentes
        </a>
        <a href="<?= URL_BASE ?>/php/paises/index.php" class="<?= linkAtivo('/paises/', $uriAtual) ?>">
            <i class="fa-solid fa-flag"></i> Países
        </a>
        <a href="<?= URL_BASE ?>/php/cidades/index.php" class="<?= linkAtivo('/cidades/', $uriAtual) ?>">
            <i class="fa-solid fa-city"></i> Cidades
        </a>
        <a href="<?= URL_BASE ?>/php/governantes/index.php" class="<?= linkAtivo('/governantes/', $uriAtual) ?>">
            <i class="fa-solid fa-user-tie"></i> Governantes
        </a>
    </nav>
</header>
