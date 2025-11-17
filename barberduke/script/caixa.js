// caixa.js

let chartCaixa = null;

// Função para carregar o caixa
async function carregarCaixa() {
    const barbeiro_id = document.getElementById('selectBarbeiro').value;
    const dataInicial = document.getElementById('dataInicial').value;
    const dataFinal = document.getElementById('dataFinal').value;
    const tipo = document.getElementById('selectTipo').value;
    const status = document.getElementById('selectStatus').value;
    const forma = document.getElementById('selectForma').value;

    const params = new URLSearchParams({
        action: 'listar',
        barbeiro_id,
        data_inicial: dataInicial,
        data_final: dataFinal,
        tipo,
        status,
        forma
    });

    try {
        const response = await fetch(`/barberduke/api/caixa.php?${params.toString()}`);
        const data = await response.json();

        if (data.erro) {
            alert("Erro: " + data.erro);
            return;
        }

        atualizarTabela(data.movimentacoes || []);
        calcularTotais(data); // pega os totais diretamente do objeto
        atualizarGrafico(data.movimentacoes || []);

    } catch (err) {
        console.error(err);
        alert("Erro ao carregar o caixa.");
    }
}
// Carregar barbeiros
async function carregarBarbeiros() {
    try {
        const response = await fetch('/barberduke/api/barbeiros_admin.php?action=listar');
        const data = await response.json();

        if (data.erro) {
            alert("Erro ao carregar barbeiros: " + data.erro);
            return;
        }

        const selectFiltro = document.getElementById('selectBarbeiro');
        const selectAdicionar = document.getElementById('barbeiro_id');

        selectFiltro.innerHTML = '<option value="">Todos</option>';
        selectAdicionar.innerHTML = '<option value="">Selecione</option>';

        data.forEach(barbeiro => {
            const optionFiltro = document.createElement('option');
            optionFiltro.value = barbeiro.id;
            optionFiltro.textContent = barbeiro.nome;
            selectFiltro.appendChild(optionFiltro);

            const optionAdicionar = document.createElement('option');
            optionAdicionar.value = barbeiro.id;
            optionAdicionar.textContent = barbeiro.nome;
            selectAdicionar.appendChild(optionAdicionar);
        });

    } catch (err) {
        console.error(err);
        alert("Erro ao carregar barbeiros.");
    }
}

// Inicializa ao carregar a página
window.addEventListener('DOMContentLoaded', () => {
    carregarBarbeiros();
    carregarCaixa();
});

// Atualiza a tabela de movimentações
function atualizarTabela(movimentacoes) {
    const tbody = document.getElementById('listaCaixa');
    tbody.innerHTML = '';

    if (!movimentacoes.length) {
        tbody.innerHTML = `<tr><td colspan="8">Nenhuma movimentação encontrada.</td></tr>`;
        return;
    }

    movimentacoes.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.id}</td>
            <td>${item.barbeiro_id}</td>
            <td>${parseFloat(item.valor).toFixed(2)}</td>
            <td>${item.status}</td>
            <td>${item.tipo}</td>
            <td>${item.forma}</td>
            <td>${item.data}</td>
            <td><button onclick="excluirMovimentacao(${item.id})">Excluir</button></td>
        `;
        tbody.appendChild(tr);
    });
}

// Atualiza os totais do caixa
function calcularTotais(data) {
    document.getElementById('totalEntrada').textContent = (data.totalEntrada ?? 0).toFixed(2);
    document.getElementById('totalSaida').textContent = (data.totalSaida ?? 0).toFixed(2);
    document.getElementById('totalDespesas').textContent = (data.totalDespesas ?? 0).toFixed(2);
    document.getElementById('lucroLiquido').textContent = (data.lucroLiquido ?? 0).toFixed(2);
}

// Atualiza gráfico com Chart.js
function atualizarGrafico(movimentacoes) {
    const ctx = document.getElementById('graficoCaixa').getContext('2d');

    // Pegar todos os tipos existentes
    const tipos = [...new Set(movimentacoes.map(m => m.tipo))];

    // Somar entradas e saídas por tipo
    const entradas = tipos.map(tipo => 
        movimentacoes
            .filter(m => m.tipo === tipo && m.status === 'entrada')
            .reduce((sum, m) => sum + parseFloat(m.valor), 0)
    );

    const saidas = tipos.map(tipo => 
        movimentacoes
            .filter(m => m.tipo === tipo && m.status === 'saida')
            .reduce((sum, m) => sum + parseFloat(m.valor), 0)
    );

    if (chartCaixa) chartCaixa.destroy();

    chartCaixa = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: tipos,
            datasets: [
                { label: 'Entradas', data: entradas, backgroundColor: 'green' },
                { label: 'Saídas', data: saidas, backgroundColor: 'red' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Movimentações de Caixa por Tipo' }
            }
        }
    });
}


// Filtrar movimentações
function filtrar() {
    carregarCaixa();
}

// Adicionar movimentação
async function adicionarMovimentacao() {
    const barbeiro_id = document.getElementById('barbeiro_id').value;
    const valor = document.getElementById('valor').value;
    const status = document.getElementById('status').value;
    const tipo = document.getElementById('tipo').value;
    const forma = document.getElementById('forma').value;

    if (!barbeiro_id || !valor || !status || !tipo || !forma) {
        alert("Preencha todos os campos!");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'adicionar');
    formData.append('barbeiro_id', barbeiro_id);
    formData.append('valor', valor);
    formData.append('status', status);
    formData.append('tipo', tipo);
    formData.append('forma', forma);

    try {
        const response = await fetch('/barberduke/api/caixa.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text();
        alert(text);
        carregarCaixa();
    } catch (err) {
        console.error(err);
        alert("Erro ao adicionar movimentação.");
    }
}

// Excluir movimentação
async function excluirMovimentacao(id) {
    if (!confirm("Deseja realmente excluir essa movimentação?")) return;

    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);

    try {
        const response = await fetch('/barberduke/api/caixa.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text();
        alert(text);
        carregarCaixa();
    } catch (err) {
        console.error(err);
        alert("Erro ao excluir movimentação.");
    }
}

// Inicializa ao carregar a página
window.addEventListener('DOMContentLoaded', () => {
    carregarCaixa();
});
