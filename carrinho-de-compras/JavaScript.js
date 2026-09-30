const produtos = [
    { nome: "Mouse", preco: 25 },
    { nome: "Teclado", preco: 80 },
    { nome: "Fone", preco: 45 },
    { nome: "Monitor", preco: 300 },
    { nome: "Webcam", preco: 120 },
    { nome: "Mousepad", preco: 30 }
];

let carrinho = [];

document.addEventListener("DOMContentLoaded", () => {
    const dados = localStorage.getItem("carrinho");

    if (dados) {
        carrinho = JSON.parse(dados);
    }

    listarProdutos(produtos);
    atualizarCarrinho();
});

function listarProdutos(lista) {
    const div = document.getElementById("lista-produtos");
    div.innerHTML = "";

    lista.forEach((produto, index) => {
        const item = document.createElement("div");

        item.innerHTML = `
            <p>${produto.nome} - R$ ${produto.preco}</p>
            <button onclick="adicionarCarrinho(${index})">Adicionar</button>
        `;

        div.appendChild(item);
    });
}

function adicionarCarrinho(index) {
    const produto = produtos[index];

    let existe = false;

    carrinho.forEach(item => {
        if (item.nome === produto.nome) {
            item.quantidade++;
            existe = true;
        }
    });

    if (!existe) {
        carrinho.push({
            nome: produto.nome,
            preco: produto.preco,
            quantidade: 1
        });
    }

    salvar();
    atualizarCarrinho();
}

function removerCarrinho(index) {
    if (carrinho[index].quantidade > 1) {
        carrinho[index].quantidade--;
    } else {
        carrinho.splice(index, 1);
    }

    salvar();
    atualizarCarrinho();
}

function atualizarCarrinho() {
    const div = document.getElementById("lista-carrinho");
    const totalSpan = document.getElementById("total");

    div.innerHTML = "";

    let total = 0;

    carrinho.forEach((item, index) => {
        const elemento = document.createElement("div");

        const precoTotal = item.preco * item.quantidade;
        total += precoTotal;

        elemento.innerHTML = `
            <p>${item.nome} - Qtd: ${item.quantidade} - R$ ${precoTotal}</p>
            <button onclick="removerCarrinho(${index})">Remover</button>
        `;

        div.appendChild(elemento);
    });

    totalSpan.innerText = total;
}

function salvar() {
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
}


function limparCarrinho() {
    if (confirm("Tem certeza que deseja limpar o carrinho?")) {
        carrinho = [];
        salvar();
        atualizarCarrinho();
    }
}


document.getElementById("filtro").addEventListener("change", function () {
    let filtrados = [];

    switch (this.value) {
        case "ate50":
            filtrados = produtos.filter(p => p.preco <= 50);
            break;

        case "acima50":
            filtrados = produtos.filter(p => p.preco > 50);
            break;

        default:
            filtrados = produtos;
    }

    listarProdutos(filtrados);
});