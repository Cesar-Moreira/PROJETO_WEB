<?php
/**
 * footer.php
 * -------------------------------------------------------------
 * Fim de toda página HTML. Inclui os arquivos JS por último
 * (boa prática: não trava o carregamento visual da página).
 * -------------------------------------------------------------
 */
?>
    <footer class="rodape">
        <p>&copy; <?= date('Y') ?> CRUD Mundo — Projeto acadêmico (Etec / Curso de Desenvolvimento de Sistemas).</p>
    </footer>

    <script src="<?= URL_BASE ?>/js/script.js"></script>
    <script src="<?= URL_BASE ?>/js/validacoes.js"></script>
</body>
</html>
