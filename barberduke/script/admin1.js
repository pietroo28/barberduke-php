// ==============================
// Clientes - Admin
// ==============================

// Função para carregar barbeiros no select
function carregarBarbeiros() {
    fetch('/barberduke/api/barbeiros.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('filtroBarbeiro');
            select.innerHTML = '<option value="">Todos</option>'; // reset
            data.forEach(b => {
                const option = document.createElement('option');
                option.value = b.id;
                option.textContent = b.nome;
                select.appendChild(option);
            });
        })
        .catch(err => console.error("Erro ao carregar barbeiros:", err));
}

// Função para buscar clientes com filtros
async function buscarClientes() {
    const barbeiroId = document.getElementById('filtroBarbeiro').value;
    const status = document.getElementById('filtroStatus').value;
    const dataInicial = document.getElementById('dataInicial').value;
    const dataFinal = document.getElementById('dataFinal').value;

    let url = `/barberduke/api/clientes.php?barbeiro_id=${barbeiroId}&status=${status}&data_inicial=${dataInicial}&data_final=${dataFinal}`;

    try {
        const res = await fetch(url);
        const clientes = await res.json();

        console.log("CLIENTES RECEBIDOS:", clientes); // 🔍 diagnóstico

        const container = document.getElementById('lista-clientes');
        container.innerHTML = ''; // Limpar resultados

        if (!clientes || clientes.length === 0) {
            container.innerHTML = 'Nenhum cliente encontrado.';
            return;
        }

        // Criar tabela
        const table = document.createElement('table');
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>Agendamentos</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;

        const tbody = table.querySelector('tbody');

        clientes.forEach(cliente => {
            let agendamentosHtml = '';
            cliente.agendamentos.forEach(ag => {
                agendamentosHtml += `
                    <div style="margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">
                        <span>${ag.data} às ${ag.horario}</span><br>
                        <span><strong>Barbeiro:</strong> ${ag.barbeiro}</span><br>
                        <span><strong>Procedimento:</strong> ${ag.procedimento || '—'}</span><br>
                        <span><strong>Status:</strong> ${ag.status || '—'}</span>
                    </div>
                `;
            });

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cliente.nome}</td>
                <td>${cliente.cpf}</td>
                <td>${cliente.telefone}</td>
                <td>${agendamentosHtml}</td>
            `;
            tbody.appendChild(tr);
        });

        container.appendChild(table);

    } catch (err) {
        console.error("Erro ao buscar clientes:", err);
        document.getElementById('lista-clientes').innerHTML = 'Erro ao buscar clientes.';
    }
}

// ==============================
// Inicialização
// ==============================
document.addEventListener('DOMContentLoaded', () => {
    carregarBarbeiros();

    const btnBuscar = document.getElementById('btnBuscarClientes');
    if (btnBuscar) {
        btnBuscar.addEventListener('click', buscarClientes);
    }
});
