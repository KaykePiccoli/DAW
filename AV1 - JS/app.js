// JavaScript envia e recebe JSON.
// Apenas o PHP manipula os arquivos .txt.

const $ = seletor => document.querySelector(seletor);

let dados = {
  usuarios: [],
  me: [],
  texto: []
};

// REQUISIÇÕES HTTP

async function requisitar(tipo, metodo = 'GET', corpo = null) {
  const resposta = await fetch(`api.php?tipo=${tipo}`, {
    method: metodo,
    headers: { 'Content-Type': 'application/json' },
    body: corpo ? JSON.stringify(corpo) : null
  });

  const resultado = await resposta.json();

  if (!resposta.ok) {
    throw new Error(resultado.erro || 'Falha na requisição.');
  }

  return resultado;
}

// FUNÇÕES AUXILIARES

function avisar(texto) {
  $('#mensagem').textContent = texto;
}

function escapar(valor) {
  return String(valor ?? '').replace(/[&<>"']/g, c =>
    ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[c]
  );
}

function mostrar(tipo) {
  ['usuarios', 'me', 'texto'].forEach(id => {
    $(`#${id}`).hidden = id !== tipo;
  });

  avisar('');
}

function limpar(id) {
  $(`#${id}`).reset();
  $(`#${id} [name=id]`).value = '';
}

// CONSULTAR REGISTROS

async function carregar() {
  try {
    for (const tipo of Object.keys(dados)) {
      dados[tipo] = await requisitar(tipo);
    }

    // Lista usuários
    $('#listaUsuarios').innerHTML = dados.usuarios.map(u => `
      <article>
        <b>#${u.id} — ${escapar(u.nome)}</b>
        (${escapar(u.email)})

        <button onclick="editar('usuarios',${u.id})">Alterar</button>
        <button onclick="excluir('usuarios',${u.id})">Excluir</button>
      </article>
    `).join('') || 'Nenhum usuário.';

    // Lista perguntas
    for (const tipo of ['me', 'texto']) {
      const lista = tipo === 'me' ? 'listaME' : 'listaTexto';

      $(`#${lista}`).innerHTML = dados[tipo].map(p => `
        <article>
          <b>#${p.id} — ${escapar(p.enunciado)}</b>

          <button onclick="detalhes('${tipo}',${p.id})">Visualizar</button>
          <button onclick="editar('${tipo}',${p.id})">Alterar</button>
          <button onclick="excluir('${tipo}',${p.id})">Excluir</button>

          <div id="detalhe-${tipo}-${p.id}"></div>
        </article>
      `).join('') || 'Nenhuma pergunta.';
    }

  } catch (erro) {
    avisar(erro.message);
  }
}

// VISUALIZAR RESPOSTAS

function detalhes(tipo, id) {
  const p = dados[tipo].find(item => item.id === id);
  const destino = $(`#detalhe-${tipo}-${id}`);

  destino.innerHTML = tipo === 'texto'
    ? `<p>Gabarito: ${escapar(p.gabarito)}</p>`
    : `<ol>${p.opcoes.map(o => `
        <li>
          ${escapar(o.texto)}
          ${o.correta ? '<b>(Correta)</b>' : ''}
        </li>
      `).join('')}</ol>`;
}

// EDITAR REGISTRO

function editar(tipo, id) {
  const p = dados[tipo].find(item => item.id === id);

  const form = $(`#${
    tipo === 'usuarios' ? 'formUsuario'
    : tipo === 'me' ? 'formME'
    : 'formTexto'
  }`);

  limpar(form.id);
  form.elements.id.value = p.id;

  if (tipo === 'usuarios') {
    form.elements.nome.value = p.nome;
    form.elements.email.value = p.email;
  } else {
    form.elements.enunciado.value = p.enunciado;

    if (tipo === 'texto') {
      form.elements.gabarito.value = p.gabarito;
    } else {
      p.opcoes.forEach((o, i) => {
        form.querySelectorAll('[name="opcao"]')[i].value = o.texto;

        if (o.correta) {
          form.querySelectorAll('[name="correta"]')[i].checked = true;
        }
      });
    }
  }

  mostrar(tipo);
}

// EXCLUIR REGISTRO

async function excluir(tipo, id) {
  if (!confirm('Excluir este registro?')) return;

  try {
    await requisitar(tipo, 'DELETE', { id });
    await carregar();
    avisar('Registro excluído.');
  } catch (erro) {
    avisar(erro.message);
  }
}

// CADASTRAR OU ATUALIZAR

async function salvar(evento, tipo) {
  evento.preventDefault();

  const form = evento.currentTarget;
  const id = Number(form.elements.id.value);
  const corpo = { id };

  if (tipo === 'usuarios') {
    corpo.nome = form.elements.nome.value.trim();
    corpo.email = form.elements.email.value.trim();
  } else {
    corpo.enunciado = form.elements.enunciado.value.trim();

    if (tipo === 'texto') {
      corpo.gabarito = form.elements.gabarito.value.trim();
    } else {
      corpo.opcoes = [...form.querySelectorAll('[name="opcao"]')]
        .map(input => input.value.trim());

      corpo.correta = Number(
        form.querySelector('[name="correta"]:checked').value
      );
    }
  }

  try {
    await requisitar(tipo, id ? 'PUT' : 'POST', corpo);

    limpar(form.id);
    await carregar();

    avisar(id ? 'Registro atualizado.' : 'Registro cadastrado.');
  } catch (erro) {
    avisar(erro.message);
  }
}

// INICIALIZAÇÃO

$('#alternativas').innerHTML = Array.from({ length: 4 }, (_, i) => `
  <label class="opcao">
    <input type="radio" name="correta" value="${i}"
      ${i === 0 ? 'checked' : ''}>

    <input type="text" name="opcao"
      placeholder="Alternativa ${i + 1}">
  </label>
`).join('');

$('#formUsuario').addEventListener('submit', e => salvar(e, 'usuarios'));
$('#formME').addEventListener('submit', e => salvar(e, 'me'));
$('#formTexto').addEventListener('submit', e => salvar(e, 'texto'));

carregar();
