<?php
/**
 * sidebar.php
 * -------------------------------------------------------------
 * Menu lateral de atalhos rápidos ("Cadastrar novo X").
 * Incluído apenas nas páginas que fazem sentido (dashboard e
 * listagens), não em todas as páginas do site.
 * -------------------------------------------------------------
 */
?>
<aside class="sidebar">
    <h3 class="sidebar-titulo">Atalhos</h3>
    <a href="<?= URL_BASE ?>/php/continentes/cadastrar.php" class="sidebar-link">
        <i class="fa-solid fa-plus"></i> Novo continente
    </a>
    <a href="<?= URL_BASE ?>/php/paises/cadastrar.php" class="sidebar-link">
        <i class="fa-solid fa-plus"></i> Novo país
    </a>
    <a href="<?= URL_BASE ?>/php/cidades/cadastrar.php" class="sidebar-link">
        <i class="fa-solid fa-plus"></i> Nova cidade
    </a>
    <a href="<?= URL_BASE ?>/php/governantes/cadastrar.php" class="sidebar-link">
        <i class="fa-solid fa-plus"></i> Novo governante
    </a>
</aside>
