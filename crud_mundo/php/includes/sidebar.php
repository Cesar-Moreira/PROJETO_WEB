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
<?php if (($_SESSION['tipo'] ?? '') === 'Administrador'): ?>
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
    <a href="<?= URL_BASE ?>/php/usuarios/cadastrar.php" class="sidebar-link">
        <i class="fa-solid fa-plus"></i> Novo usuário
    </a>
</aside>
<?php else: ?>
<aside class="sidebar">
    <h3 class="sidebar-titulo">Modo de visualização</h3>
    <p style="font-size:0.85rem; color:#888; padding:0 0.4rem;">
        <i class="fa-solid fa-eye"></i> Você está logado como usuário comum: pode consultar todos os dados, mas cadastro/edição/exclusão ficam disponíveis apenas para administradores.
    </p>
</aside>
<?php endif; ?>
