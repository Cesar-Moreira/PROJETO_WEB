/**
 * validacoes.js
 * -------------------------------------------------------------
 * Validação de formulário no lado do cliente (frontend).
 * IMPORTANTE: isso é só uma primeira barreira para melhorar a
 * experiência do usuário — o PHP sempre valida de novo no
 * servidor, porque JavaScript pode ser desativado ou burlado.
 *
 * Funciona em qualquer formulário que tenha o atributo "novalidate"
 * (usamos esse atributo para desligar a validação padrão feiosa
 * do navegador e mostrar nossas próprias mensagens estilizadas).
 * -------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {
    var formularios = document.querySelectorAll('form[novalidate]');

    formularios.forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            var formularioValido = true;
            var camposObrigatorios = form.querySelectorAll('[required]');

            camposObrigatorios.forEach(function (campo) {
                var grupo = campo.closest('.form-grupo');
                var mensagemErro = grupo ? grupo.querySelector('.erro-campo') : null;
                var valorPreenchido = campo.value.trim() !== '';

                if (!valorPreenchido) {
                    formularioValido = false;
                    campo.style.borderColor = '#C62828';
                    if (mensagemErro) {
                        mensagemErro.style.display = 'block';
                    }
                } else {
                    campo.style.borderColor = '#ddd';
                    if (mensagemErro) {
                        mensagemErro.style.display = 'none';
                    }
                }
            });

            if (!formularioValido) {
                evento.preventDefault(); // impede o envio do formulário
            }
        });

        // Remove a mensagem de erro assim que o usuário começa a corrigir o campo
        form.querySelectorAll('[required]').forEach(function (campo) {
            campo.addEventListener('input', function () {
                var grupo = campo.closest('.form-grupo');
                var mensagemErro = grupo ? grupo.querySelector('.erro-campo') : null;
                if (campo.value.trim() !== '') {
                    campo.style.borderColor = '#ddd';
                    if (mensagemErro) {
                        mensagemErro.style.display = 'none';
                    }
                }
            });
        });
    });
});
