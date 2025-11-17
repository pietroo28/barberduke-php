const API_URL = "/barberduke/api/procedimentos.php";

async function carregarProcedimentos() {
  const res = await fetch(API_URL);
  const dados = await res.json();
  exibirProcedimentos(dados);
}

function exibirProcedimentos(lista) {
  const div = document.getElementById("lista-procedimentos");
  div.innerHTML = "";

  if (lista.length === 0) {
    div.textContent = "Nenhum procedimento cadastrado.";
    return;
  }

  lista.forEach(p => {
    const item = document.createElement("div");
    item.innerHTML = `
      <strong>${p.nome}</strong> - ${p.duracao} min - R$ ${parseFloat(p.valor).toFixed(2)}<br>
      <button onclick="preencherEdicao(${p.id}, '${p.nome}', ${p.duracao}, ${p.valor})">Editar</button>
      <button onclick="excluirProcedimento(${p.id})">Excluir</button>
      <hr>
    `;
    div.appendChild(item);
  });
}

async function adicionarProcedimento() {
  const nome = document.getElementById("addNomeProcedimento").value.trim();
  const duracao = parseInt(document.getElementById("addDuracaoProcedimento").value);
  const valor = parseFloat(document.getElementById("addValorProcedimento").value);

  if (!nome || isNaN(duracao) || isNaN(valor)) {
    alert("Preencha todos os campos!");
    return;
  }

  await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ nome, duracao, valor })
  });

  carregarProcedimentos();
}

function preencherEdicao(id, nome, duracao, valor) {
  document.getElementById("editIdProcedimento").value = id;
  document.getElementById("editNomeProcedimento").value = nome;
  document.getElementById("editDuracaoProcedimento").value = duracao;
  document.getElementById("editValorProcedimento").value = valor;
}

async function editarProcedimento() {
  const id = parseInt(document.getElementById("editIdProcedimento").value);
  const nome = document.getElementById("editNomeProcedimento").value.trim();
  const duracao = parseInt(document.getElementById("editDuracaoProcedimento").value);
  const valor = parseFloat(document.getElementById("editValorProcedimento").value);

  await fetch(API_URL, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, nome, duracao, valor })
  });

  carregarProcedimentos();
}

async function excluirProcedimento(id) {
  if (confirm("Deseja excluir este procedimento?")) {
    await fetch(API_URL, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });
    carregarProcedimentos();
  }
}

window.onload = carregarProcedimentos;
