<?php
/**
 * header.php
 * -------------------------------------------------------------
 * Início de toda página HTML do site.
 * $tituloPagina deve ser definida ANTES de incluir este arquivo,
 * ex.: $tituloPagina = "Países - CRUD Mundo";
 * -------------------------------------------------------------
 */
if (!isset($tituloPagina)) {
    $tituloPagina = 'CRUD Mundo';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome (ícones) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Normalize.css (zera diferenças entre navegadores) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">

    <!-- CSS próprio do projeto -->
    <link rel="stylesheet" href="<?= URL_BASE ?>/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/css/responsive.css">
</head>
<body>
