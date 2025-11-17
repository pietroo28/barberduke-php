// admin.js
document.addEventListener("DOMContentLoaded", () => {
    mostrarSecao('barbeiros'); // seção inicial
    carregarBarbeiros();
    carregarClientes();
    carregarProcedimentos();

    // Eventos de Barbeiros
    const btnAdicionar = document.getElementById('btnAdicionarBarbeiro');
    if(btnAdicionar) btnAdicionar.addEventListener('click', adicionarBarbeiro);

    const btnEditar = document.getElementById('btnEditarBarbeiro');
    if(btnEditar) btnEditar.addEventListener('click', editarBarbeiro);

    // Eventos de Agendamento
    const btnBuscarAgendamento = document.getElementById('BuscarAgendamento');
    if(btnBuscarAgendamento) btnBuscarAgendamento.addEventListener('click', buscarAgendamentos);

    const btnAgendar = document.getElementById('btnAgendar');
    if(btnAgendar) btnAgendar.addEventListener('click', criarAgendamento);
});

// ---------------------------
// Seções
// ---------------------------
function mostrarSecao(secao) {
    const secoes = ['barbeiros', 'clientes', 'agendamentos'];
    secoes.forEach(s => {
        const el = document.getElementById(`secao-${s}`);
        if(el) el.style.display = s === secao ? 'block' : 'none';
    });

    if(secao === 'agendamentos') {
        carregarSelectBarbeiros('filtroBarbeiro');
        carregarSelectBarbeiros('novoBarbeiroAgendamento');
    } else if(secao === 'barbeiros') {
        carregarBarbeiros();
    }
}
// ---------------------------
// BARBEIROS
// ---------------------------

// Carregar lista de barbeiros na tela
async function carregarBarbeiros() {
    const lista = document.getElementById('lista-barbeiros');
    if(!lista) return;
    lista.innerHTML = "Carregando barbeiros...";

    try {
        const res = await fetch('/barberduke/api/barbeiros_admin.php');
        const barbeiros = await res.json();

        if(barbeiros.length === 0) {
            lista.innerHTML = "Nenhum barbeiro cadastrado.";
            return;
        }

        lista.innerHTML = '';
        barbeiros.forEach(b => {
            const div = document.createElement('div');
            div.innerHTML = `
                <strong>${b.nome}</strong> | CPF: ${b.cpf} | Status: ${b.status} 
                <button onclick="preencherEdicao(${b.id}, '${b.nome}', '${b.cpf}', '${b.status}')">Editar</button>
            `;
            lista.appendChild(div);
        });

        // Atualiza também o select de edição
        carregarSelectBarbeiroEditar(barbeiros);

    } catch(err) {
        lista.innerHTML = "Erro ao carregar barbeiros.";
        console.error(err);
    }
}

// Popular select de edição
function carregarSelectBarbeiroEditar(barbeiros = null) {
    const select = document.getElementById('selectBarbeiroEditar');
    if(!select) return;

    // Se não receber a lista, busca via API
    if(!barbeiros) {
        fetch('/barberduke/api/barbeiros.php')
            .then(res => res.json())
            .then(data => carregarSelectBarbeiroEditar(data))
            .catch(err => console.error(err));
        return;
    }

    select.innerHTML = `<option value="">-- Selecionar barbeiro --</option>`;
    barbeiros.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = `${b.nome} | ${b.cpf} | ${b.status}`;
        select.appendChild(opt);
    });
}

// Quando o usuário clica no botão "Editar" ou seleciona do select
function preencherEdicao(id, nome, cpf, status) {
    document.getElementById('editNomeBarbeiro').value = nome;
    document.getElementById('editCPFBarbeiro').value = cpf;
    document.getElementById('editStatusBarbeiro').value = status;
    document.getElementById('btnEditarBarbeiro').dataset.id = id;

    // Seleciona o barbeiro no select
    const select = document.getElementById('selectBarbeiroEditar');
    if(select) select.value = id;
}

// Quando o select de edição muda
function carregarBarbeiroSelecionado(id) {
    if(!id) return limparCamposBarbeiro();

    fetch('/barberduke/api/barbeiros.php')
        .then(res => res.json())
        .then(barbeiros => {
            const b = barbeiros.find(b => b.id == id);
            if(!b) return;
            document.getElementById('editNomeBarbeiro').value = b.nome;
            document.getElementById('editCPFBarbeiro').value = b.cpf;
            document.getElementById('editStatusBarbeiro').value = b.status;
            document.getElementById('btnEditarBarbeiro').dataset.id = b.id;
        })
        .catch(err => console.error(err));
}

// Adicionar novo barbeiro
async function adicionarBarbeiro() {
    const nome = document.getElementById('addNomeBarbeiro').value;
    const cpf = document.getElementById('addCPFBarbeiro').value;
    const status = document.getElementById('addStatusBarbeiro').value;

    try {
        const res = await fetch('/barberduke/api/barbeiros_admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({nome, cpf, status, id: null})
        });
        const data = await res.json();
        if(data.success) {
            alert("Barbeiro adicionado com sucesso!");
            carregarBarbeiros();
            limparCamposBarbeiro();
        } else {
            alert("Erro ao adicionar barbeiro");
        }
    } catch(err) {
        console.error(err);
    }
}

// Editar barbeiro existente
async function editarBarbeiro() {
    const btnEditar = document.getElementById('btnEditarBarbeiro');
    const id = btnEditar.dataset.id;
    const nome = document.getElementById('editNomeBarbeiro').value;
    const cpf = document.getElementById('editCPFBarbeiro').value;
    const status = document.getElementById('editStatusBarbeiro').value;

    if(!id) return alert("Selecione um barbeiro para editar");

    try {
        const res = await fetch('/barberduke/api/barbeiros_admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id, nome, cpf, status})
        });
        const data = await res.json();
        if(data.success) {
            alert("Barbeiro atualizado com sucesso!");
            carregarBarbeiros();
            limparCamposBarbeiro();
        } else {
            alert("Erro ao atualizar barbeiro");
        }
    } catch(err) {
        console.error(err);
    }
}

// Limpar campos
function limparCamposBarbeiro() {
    document.getElementById('addNomeBarbeiro').value = '';
    document.getElementById('addCPFBarbeiro').value = '';
    document.getElementById('addStatusBarbeiro').value = 'ativo';

    document.getElementById('editNomeBarbeiro').value = '';
    document.getElementById('editCPFBarbeiro').value = '';
    document.getElementById('editStatusBarbeiro').value = 'ativo';

    delete document.getElementById('btnEditarBarbeiro').dataset.id;

    // Reset select
    const select = document.getElementById('selectBarbeiroEditar');
    if(select) select.value = '';
}
// ---------------------------
// Agendamentos
// ---------------------------
async function buscarAgendamentos() {
    const barbeiroId = document.getElementById('filtroBarbeiro').value;
    const data = document.getElementById('filtroData').value;
    const status = document.getElementById('filtroStatus').value;
    const container = document.getElementById('agendados');
    if(!container) return;

    try {
        const res = await fetch(`/api/agendamentos?barbeiro=${barbeiroId}&data=${data}&status=${status}`);
        const agendamentos = await res.json();
        if(agendamentos.length === 0) {
            container.innerHTML = "Nenhum agendamento encontrado.";
            return;
        }
        container.innerHTML = '';
        agendamentos.forEach(a => {
            const div = document.createElement('div');
            div.innerHTML = `<strong>${a.nomeCliente || "Cliente Sem CPF"}</strong> | Horário: ${a.horario} | Status: ${a.status}`;
            container.appendChild(div);
        });
    } catch(err) {
        container.innerHTML = "Erro ao carregar agendamentos.";
        console.error(err);
    }
}

// ---------------------------
// Procedimentos e Novo Agendamento
// ---------------------------
async function carregarProcedimentos() {
    try {
        const res = await fetch('/api/procedimentos');
        const procedimentos = await res.json();
        const fieldset = document.getElementById('fieldsetProcedimentos');
        if(!fieldset) return;
        fieldset.innerHTML = '';
        procedimentos.forEach(p => {
            const label = document.createElement('label');
            label.innerHTML = `<input type="checkbox" value="${p.id}"> ${p.nome} (${p.preco})`;
            fieldset.appendChild(label);
        });
    } catch(err) {
        console.error(err);
    }
}

async function criarAgendamento() {
    const nomeCliente = document.getElementById('novoNomeCliente').value || 'Cliente Sem CPF';
    const barbeiroId = document.getElementById('novoBarbeiroAgendamento').value;
    const data = document.getElementById('novaData').value;
    const horario = document.getElementById('novoHorario').value;

    const procedimentos = Array.from(document.querySelectorAll('#fieldsetProcedimentos input:checked')).map(p => p.value);

    try {
        const res = await fetch('/api/agendamentos', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({nomeCliente, barbeiroId, data, horario, procedimentos})
        });
        if(res.ok) {
            alert("Agendamento criado!");
            document.getElementById('novoNomeCliente').value = '';
            document.getElementById('novaData').value = '';
            document.getElementById('novoHorario').value = '';
            document.querySelectorAll('#fieldsetProcedimentos input').forEach(i => i.checked = false);
        } else {
            alert("Erro ao criar agendamento");
        }
    } catch(err) {
        console.error(err);
    }
}
