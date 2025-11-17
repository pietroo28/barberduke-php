document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridDay',
    locale: 'pt-br',
    slotMinTime: "08:00:00",
    slotMaxTime: "21:30:00",
    slotDuration: "00:10:00",
    allDaySlot: false,
    selectable: true,
    editable: true,
    eventOverlap: true,
    headerToolbar: { left: '', center: 'title', right: '' },

    // ==============================
    // CLIQUE → ADICIONAR OU BLOQUEAR
    // ==============================
    dateClick: async function(info) {
      const barbeiro_id = document.getElementById('filtroBarbeiro').value;
      if (!barbeiro_id) return alert('Selecione um barbeiro primeiro.');

      const dataIso = info.dateStr.split("T")[0];
      const hora = info.dateStr.split("T")[1]?.slice(0, 5);

      const acao = prompt("Digite o número da ação desejada:\n1️⃣ Adicionar Agendamento\n2️⃣ Bloquear Horário");
      if (!acao) return;

      // ==============================
      // 1️⃣ ADICIONAR AGENDAMENTO
      // ==============================
      if (acao === "1") {
        const cliente = prompt("Nome do cliente:");
        if (!cliente) return;

        let procedimentos = [];
        try {
          const res = await fetch('/barberduke/api/procedimentos.php');
          procedimentos = await res.json();
        } catch (err) {
          console.error("Erro ao carregar procedimentos:", err);
          alert("Erro ao carregar lista de procedimentos.");
          return;
        }

        if (!Array.isArray(procedimentos) || procedimentos.length === 0) {
          alert("Nenhum procedimento cadastrado!");
          return;
        }

        let lista = "Escolha o número do procedimento:\n";
        procedimentos.forEach((p, i) => {
          lista += `${i + 1}. ${p.nome} (${p.duracao}min)\n`;
        });

        const escolha = prompt(lista);
        const idx = parseInt(escolha) - 1;
        if (isNaN(idx) || !procedimentos[idx]) return alert("Procedimento inválido.");

        const procedimento = procedimentos[idx].id;
        const duracao = parseInt(procedimentos[idx].duracao || 30);

        const horaInicio = prompt("Hora de início (ex: 14:00):", hora);
        if (!horaInicio) return;

        const [h, m] = horaInicio.split(':').map(Number);
        const fimDate = new Date(0, 0, 0, h, m);
        fimDate.setMinutes(fimDate.getMinutes() + duracao);
        const horaFim = fimDate.toTimeString().slice(0, 5);

        try {
          const res = await fetch('/barberduke/api/agendamento_adicionar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              barbeiro_id,
              cliente,
              procedimento,
              data: dataIso,
              hora_inicio: horaInicio,
              duracao: duracao,
              status: 'confirmado'
            })
          });

          const text = await res.text();
          let resp;
          try { resp = JSON.parse(text); } 
          catch (err) {
            console.error("Resposta do PHP não é JSON:", text);
            alert("Erro no servidor: ver console.");
            return;
          }

          if (resp.success) {
            calendar.addEvent({
              id: resp.id || undefined,
              title: `${cliente} - ${procedimento}`,
              start: `${dataIso}T${horaInicio}`,
              end: `${dataIso}T${horaFim}`,
              color: '#4e73df',
              extendedProps: { status: 'confirmado',}
            });
            alert(`✅ Agendamento criado!\nInício: ${horaInicio}\nTérmino: ${horaFim}`);
          } else {
            alert('❌ Erro: ' + (resp.message || 'Falha ao criar agendamento.'));
          }

        } catch (err) {
          console.error(err);
          alert('Erro ao criar agendamento.');
        }
      }

      // ==============================
      // 2️⃣ BLOQUEAR HORÁRIO
      // ==============================
      else if (acao === "2") {
        const horaInicio = prompt("Informe o horário de início (ex: 09:00):", hora);
        if (!horaInicio) return;

        const horaFim = prompt("Informe o horário de fim (ex: 10:30):", horaInicio);
        if (!horaFim) return;

        try {
          const res = await fetch('/barberduke/api/bloqueio_adicionar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              barbeiro_id,
              cliente: 'Horário Bloqueado',
              procedimento: 'Bloqueio de horário',
              data: dataIso,
              hora_inicio: horaInicio,
              hora_fim: horaFim,
              status: 'bloqueado',
              usuario_nulo: true
            })
          });
          const resp = await res.json();

          if (resp.success) {
            calendar.addEvent({
              title: "Horário Bloqueado",
              start: `${dataIso}T${horaInicio}`,
              end: `${dataIso}T${horaFim}`,
              color: '#6c757d',
              extendedProps: { status: 'bloqueado' }
            });
            alert('⛔ Horário bloqueado com sucesso!');
          } else {
            alert('Atualize a página para conferir');
          }
        } catch (err) {
          console.error(err);
          alert('Atualize a página');
        }
      }
    },
  });

  calendar.render();

  // ==============================
  // FUNÇÃO AUXILIAR DE DATA
  // ==============================
  function normalizaData(data) {
    if (!data) return data;
    if (data.includes('/')) {
      const parts = data.split('/');
      return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
    }
    return data;
  }

  // ==============================
  // BOTÃO "VER AGENDA"
  // ==============================
  document.getElementById('btnVerAgenda').addEventListener('click', async () => {
    const barbeiroId = document.getElementById('filtroBarbeiro').value;
    let data = document.getElementById('filtroData').value;

    if (!barbeiroId || !data) {
      alert("Selecione um barbeiro e uma data primeiro!");
      return;
    }

    const dataFormatada = normalizaData(data);

    try {
      const res = await fetch(`/barberduke/api/agenda.php?barbeiro_id=${barbeiroId}&data=${dataFormatada}`);
      const agendamentos = await res.json();

      calendar.gotoDate(dataFormatada);
      calendar.getEvents().forEach(e => e.remove());

      const tbody = document.querySelector("#tabelaAgenda tbody");
      if (tbody) tbody.innerHTML = "";

      if (!Array.isArray(agendamentos) || agendamentos.length === 0) {
        alert("Nenhum agendamento encontrado.");
        return;
      }

      // ==============================
      // Adiciona eventos no calendário e tabela
      // ==============================
      agendamentos.forEach(ag => {
        const agData = normalizaData(ag.data);
        const cores = {
          ativo: '#4e73df',
          confirmado: '#1cc88a',
          cancelado: '#e74a3b',
          passado: '#858796',
          bloqueado: '#6c757d'
        };
        const corStatus = cores[ag.status] || '#4e73df';

        calendar.addEvent({
          id: ag.id || undefined,
          title: ag.status === 'bloqueado' ? 'Horário Bloqueado' : `${ag.cliente} - ${ag.procedimento}`,
          start: `${agData}T${ag.horario_inicio}`,
          end: `${agData}T${ag.horario_fim}`,
          color: corStatus,
          extendedProps: { status: ag.status || '', cpf: ag.cpf || ag.cliente_cpf || '' }
        });

        if (tbody && ag.status !== 'bloqueado') {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${ag.id}</td>
            <td>${ag.horario_inicio} - ${ag.horario_fim}</td>
            <td>${ag.cliente}</td>
            <td>${ag.procedimento}</td>
            <td class="status-cell" style="color:${corStatus}">${ag.status || ''}</td>
            <td>
              <select class="alterar-status" data-id="${ag.id}">
                <option value="">Alterar...</option>
                <option value="ativo">Ativo</option>
                <option value="confirmado">Confirmado</option>
                <option value="cancelado">Cancelado</option>
                <option value="passado">Passado</option>
              </select>
              <button class="btn-excluir" data-id="${ag.id}" style="margin-left:5px;color:red;">❌</button>
            </td>
          `;
          tbody.appendChild(tr);
        }
      });

      // ==============================
      // ALTERAR STATUS
      // ==============================
      document.querySelectorAll('.alterar-status').forEach(sel => {
        sel.addEventListener('change', async e => {
          const novoStatus = e.target.value;
          const id = e.target.getAttribute('data-id');
          if (!novoStatus || !id) return;

          try {
            const res = await fetch('/barberduke/api/alterar_status.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id, status: novoStatus })
            });
            const data = await res.json();

            if (data.success) {
              alert(`✅ Status alterado para ${novoStatus}`);
              const cores = {
                ativo: '#4e73df',
                confirmado: '#1cc88a',
                cancelado: '#e74a3b',
                passado: '#858796'
              };
              const tdStatus = e.target.closest('tr').querySelector('.status-cell');
              tdStatus.textContent = novoStatus;
              tdStatus.style.color = cores[novoStatus] || '#4e73df';

              const evento = calendar.getEventById(id);
              if (evento) {
                evento.setExtendedProp('status', novoStatus);
                evento.setProp('color', cores[novoStatus] || '#4e73df');
              }
            } else {
              alert('❌ Erro: ' + (data.message || 'Falha ao alterar status.'));
            }
          } catch (err) {
            console.error(err);
            alert('Erro ao atualizar status.');
          }
        });
      });

      // ==============================
      // EXCLUIR AGENDAMENTO
      // ==============================
      document.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.addEventListener('click', async e => {
          const id = e.target.getAttribute('data-id');
          if (!id) return;

          const confirmacao = confirm("Tem certeza que deseja excluir este agendamento?");
          if (!confirmacao) return;

          try {
            const res = await fetch('/barberduke/api/agendamento_excluir.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id })
            });

            const data = await res.json();

            if (data.success) {
              alert('✅ Agendamento excluído com sucesso!');
              
              const evento = calendar.getEventById(id);
              if (evento) evento.remove();
              
              e.target.closest('tr').remove();
            } else {
              alert('❌ Erro ao excluir: ' + (data.message || 'Falha ao excluir agendamento.'));
            }
          } catch (err) {
            console.error(err);
            alert('Erro ao excluir agendamento.');
          }
        });
      });

    } catch (err) {
      console.error("Erro ao carregar agenda:", err);
      alert("Erro ao carregar agenda.");
    }
  });

  // ==============================
  // CARREGAR BARBEIROS
  // ==============================
  fetch('/barberduke/api/barbeiros.php')
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById('filtroBarbeiro');
      select.innerHTML = '<option value="">Selecione...</option>';
      data.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = b.nome;
        select.appendChild(opt);
      });
    })
    .catch(err => console.error("Erro ao carregar barbeiros:", err));

});
