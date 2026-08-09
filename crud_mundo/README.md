# 🌍 CRUD Mundo — Programação Web

**Aluno(a):** _Cesar Fernando Araujo Moreira_
**Curso:** Desenvolvimento de Sistemas
**Unidade:** Etec — São José dos Campos
**Disciplina:** Programação Web

## 📖 Descrição do projeto

Sistema web completo para gerenciamento de informações geográficas do mundo, permitindo cadastrar, listar, editar e excluir **Continentes**, **Países**, **Cidades** e **Governantes**, respeitando os relacionamentos entre essas entidades (um continente tem vários países, um país tem várias cidades, e países/cidades podem ter um governante vinculado).

## 🎯 Objetivo

Implementar um CRUD (Create, Read, Update, Delete) completo, com interface web responsiva, validação de dados no frontend e no backend, e integridade referencial no banco de dados.

## 🛠️ Tecnologias utilizadas

- HTML5
- CSS3 (Flexbox, Grid, responsivo)
- JavaScript (validação, pesquisa dinâmica, confirmação de exclusão)
- PHP 8+ (orientado a procedimentos, com PDO)
- MySQL (banco relacional)
- Font Awesome (ícones)
- Google Fonts (tipografia)
- Normalize.css (consistência entre navegadores)

## 📁 Estrutura do projeto

```
crud_mundo/
├── php/                     # TODO o backend (lógica PHP), agrupado aqui
│   ├── config/              # Conexão com o banco e configurações globais
│   │   ├── database.php     # Cria a conexão PDO (usada por todos os módulos)
│   │   └── config.php        # Carrega o .env, define constantes e sessão
│   ├── includes/            # Trechos de HTML reutilizados em todas as páginas
│   │   ├── header.php       # <head>, fontes, ícones, CSS
│   │   ├── navbar.php       # Menu superior
│   │   ├── sidebar.php      # Atalhos rápidos de cadastro
│   │   └── footer.php       # Fechamento do HTML e scripts JS
│   ├── continentes/         # CRUD de continentes
│   ├── paises/                # CRUD de países
│   ├── cidades/               # CRUD de cidades
│   └── governantes/           # CRUD de governantes
├── css/                     # TODO o CSS do projeto
│   ├── style.css             # Estilo principal (cores, cards, tabelas, formulários)
│   └── responsive.css         # Ajustes para tablet e celular
├── js/                      # TODO o JavaScript do projeto
│   ├── script.js              # Menu mobile, pesquisa dinâmica, confirmação de exclusão
│   └── validacoes.js          # Validação de campos obrigatórios nos formulários
├── sql/
│   └── bd_mundo.sql          # Script de criação do banco, tabelas e dados de exemplo
├── img/                     # Imagens do projeto (se houver)
├── .env                     # Credenciais do banco (NÃO versionado no Git)
├── .env.example              # Modelo do .env, esse sim é versionado
├── .gitignore
├── index.php                 # Dashboard inicial (fica na raiz para ser a página de entrada do site)
└── README.md
```

> **Por que `index.php` fica fora da pasta `php/`?** Ele é o "ponto de entrada" do site — o Apache carrega automaticamente `index.php` quando alguém acessa a raiz do projeto (`http://localhost/crud_mundo/`). Se ele estivesse dentro de `php/`, o endereço do dashboard mudaria para `.../crud_mundo/php/index.php`, o que é menos natural para o visitante.

Cada módulo (`php/continentes/`, `php/paises/`, `php/cidades/`, `php/governantes/`) segue o mesmo padrão:
- `index.php` — lista os registros, com pesquisa e ordenação por nome
- `cadastrar.php` — formulário de criação
- `editar.php` — formulário de edição, pré-preenchido
- `excluir.php` — processa a exclusão (com confirmação via JavaScript antes)

## 🗄️ Estrutura do banco de dados (bd_mundo)

| Tabela | Campos principais | Relacionamento |
|---|---|---|
| `continentes` | nome, população, área, total_países | Tabela raiz |
| `governantes` | nome, partido, nascimento, idade*, mandato | Independente |
| `paises` | nome, população, área, idioma, clima, regime, moeda | FK → continentes, FK → governantes |
| `cidades` | nome, população, área, clima, fundação | FK → países, FK → governantes |

`*` idade é calculada automaticamente por uma trigger no banco, a partir da data de nascimento.

**Regras de integridade:**
- Não é possível excluir um continente que tenha países vinculados, nem um país que tenha cidades vinculadas (`ON DELETE RESTRICT`).
- Excluir um governante **não** apaga o país/cidade vinculado — apenas remove o vínculo (`ON DELETE SET NULL`).

## ✅ Funcionalidades

- CRUD completo (criar, listar, editar, excluir) para os 4 módulos
- Pesquisa dinâmica pelo nome, sem recarregar a página
- Ordenação da listagem por nome (clicando no cabeçalho da coluna)
- Mensagens de sucesso e erro após cada ação
- Confirmação via JavaScript antes de excluir qualquer registro
- Validação de campos obrigatórios no frontend (JavaScript) e no backend (PHP)
- Dashboard com estatísticas em tempo real (contagens, cidade mais populosa, maior país, últimos cadastros)
- Layout responsivo (desktop, tablet e celular)

## ⚙️ Como instalar e configurar

### Pré-requisitos
- [XAMPP](https://www.apachefriends.org/) instalado (Apache + MySQL + PHP)

### Passo a passo

1. Copie a pasta `crud_mundo` para dentro de `htdocs` do XAMPP.
2. Abra o XAMPP Control Panel e inicie **Apache** e **MySQL**.
3. Copie o arquivo `.env.example`, cole na mesma pasta e renomeie a cópia para `.env`.
4. Abra o `.env` e confirme usuário/senha do seu MySQL (no XAMPP padrão, `root` sem senha já funciona).
5. Acesse `http://localhost/phpmyadmin`, vá na aba **SQL**, cole o conteúdo de `sql/bd_mundo.sql` e execute. Isso cria o banco `bd_mundo`, as tabelas e alguns dados de exemplo.
6. Acesse `http://localhost/crud_mundo/` no navegador.

## 🚀 Como usar

1. O **Dashboard** mostra um resumo geral do sistema.
2. Use o menu superior para navegar entre Continentes, Países, Cidades e Governantes.
3. Em cada módulo, use o botão "Novo(a) ..." para cadastrar, os ícones de lápis/lixeira na tabela para editar/excluir, e a caixa de pesquisa para filtrar pelo nome.
4. Cadastre primeiro os **Continentes**, depois **Governantes** (opcional), depois **Países** (que dependem de um continente) e por fim **Cidades** (que dependem de um país).

## 🔒 Boas práticas de segurança aplicadas

- Credenciais do banco isoladas em `.env`, fora do controle de versão
- Conexão via PDO com **Prepared Statements** em 100% das queries (proteção contra SQL Injection)
- Saída sempre tratada com `htmlspecialchars()` (proteção contra XSS)
- Validação dos dados tanto no frontend (JavaScript) quanto no backend (PHP) — nunca confiando apenas no JavaScript
- Tratamento de erros com `try/catch`, sem expor detalhes sensíveis ao usuário final
- **Exclusões feitas via formulário POST** (não mais por link GET), evitando que a ação seja disparada acidentalmente por um crawler ou link externo
- **Proteção CSRF**: todo formulário de cadastro, edição e exclusão envia um token único de sessão, validado no servidor antes de qualquer alteração no banco

> Este projeto não possui sistema de login — qualquer pessoa com acesso à URL pode cadastrar/editar/excluir. Isso é intencional, pois autenticação não fazia parte do escopo da atividade; veja "Possíveis melhorias futuras".

## 🏆 Desafio extra implementado

- Pesquisa dinâmica (JavaScript) por nome em todos os módulos, sem recarregar a página
- Dashboard com **cidade mais populosa de cada país** (não só a mais populosa do mundo)
- Dashboard com **total de cidades cadastradas por continente**

## 🔮 Possíveis melhorias futuras

- Sistema de login/autenticação para restringir o acesso ao CRUD
- Upload de bandeiras/fotos para países e cidades
- Exportação dos dados em PDF ou Excel
- Paginação nas listagens para grandes volumes de dados
- Gráficos no dashboard (população por continente, etc.)

## 📄 Licença

Projeto acadêmico, desenvolvido para fins de avaliação na disciplina de Programação Web — Etec.
