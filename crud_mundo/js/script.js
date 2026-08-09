/**
 * script.js
 * -------------------------------------------------------------
 * Comportamentos gerais do site (não específicos de formulário).
 * A lógica de pesquisa dinâmica e confirmação de exclusão será
 * adicionada aqui nas próximas etapas, junto com os módulos.
 * -------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {
    // Abre/fecha o menu no celular
    var botaoMenu = document.getElementById('navbarToggle');
    var linksMenu = document.getElementById('navbarLinks');

    if (botaoMenu && linksMenu) {
        botaoMenu.addEventListener('click', function () {
            linksMenu.classList.toggle('aberto');
        });
    }

    // Pesquisa dinâmica: filtra as linhas da tabela enquanto o usuário digita,
    // sem precisar recarregar a página. Cada <tr> precisa ter o atributo data-nome.
    var campoPesquisa = document.getElementById('campoPesquisa');
    if (campoPesquisa) {
        campoPesquisa.addEventListener('input', function () {
            var termo = campoPesquisa.value.trim().toLowerCase();
            var linhas = document.querySelectorAll('tbody tr[data-nome]');

            linhas.forEach(function (linha) {
                var nome = linha.getAttribute('data-nome');
                linha.style.display = nome.indexOf(termo) !== -1 ? '' : 'none';
            });
        });
    }
});

/**
 * Confirmação antes de excluir um registro.
 * Usada nos links de exclusão de todos os módulos (continentes,
 * países, cidades, governantes) via onclick="return confirmarExclusao(...)".
 * Retornar false cancela a navegação (o delete não acontece).
 */
function confirmarExclusao(nomeRegistro) {
    return confirm('Tem certeza que deseja excluir "' + nomeRegistro + '"?\nEssa ação não pode ser desfeita.');
}
