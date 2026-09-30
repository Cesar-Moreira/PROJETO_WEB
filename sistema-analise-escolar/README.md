# Sistema de Análise Escolar

## Sobre o projeto
Aplicação web que analisa o desempenho de uma turma a partir das notas dos alunos. O usuário informa a turma e a quantidade de alunos, cadastra as notas e recebe um relatório com a situação de cada aluno e estatísticas gerais.

## Funcionalidades
- Informe do nome da turma e da quantidade de alunos
- Geração automática dos campos de cadastro de acordo com a quantidade informada
- Cadastro do nome e das notas de cada aluno (Prova 1, Prova 2 e Trabalho)
- Cálculo da média de cada aluno e definição da situação (Aprovado, Recuperação ou Reprovado)
- Relatório da turma com média geral, maior e menor média, quantidade por situação, percentual de aprovação e soma total das notas
- Mensagem sobre o desempenho geral da turma

## Tecnologias utilizadas
- PHP
- HTML
- CSS
- Git
- GitHub

## Estrutura do projeto
- `Index.php`: formulário de cadastro da turma e dos alunos
- `Processar.php`: cálculos e exibição do relatório
- `Estilizar.css`: estilos das páginas
- `.gitignore`: arquivos que não devem ser enviados ao GitHub

## Requisitos
- PHP 8 ou superior (ou XAMPP)
- Git

## Como executar
1. Clone o repositório.
2. Acesse a pasta `sistema-analise-escolar`.
3. Inicie um servidor PHP local, por exemplo: `php -S localhost:8000` (ou copie a pasta para o `htdocs` do XAMPP).
4. Acesse `http://localhost:8000/Index.php` no navegador.

## Autor
César Moreira
