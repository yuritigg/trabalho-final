// ==========================================================================
// Funcoes compartilhadas por todas as paginas
// ==========================================================================

const TOKEN_API = 'meu-token-secreto-123';

// Chama a API e devolve o JSON (ou lanca um erro com a mensagem da API)
async function chamarApi(metodo, url, dados = null) {
    const opcoes = {
        method: metodo,
        headers: { 'Authorization': 'Bearer ' + TOKEN_API }
    };

    if (dados !== null) {
        opcoes.headers['Content-Type'] = 'application/json';
        opcoes.body = JSON.stringify(dados);
    }

    let resposta;
    try {
        resposta = await fetch(url, opcoes);
    } catch (e) {
        throw new Error('Não foi possível falar com o servidor. Verifique a conexão e tente de novo.');
    }

    // Se o PHP der erro, ele devolve HTML em vez de JSON: isso NAO pode virar sucesso
    const json = await resposta.json().catch(function () { return null; });

    if (json === null) {
        throw new Error('Erro no servidor: a resposta não veio no formato esperado. Confira se todos os arquivos PHP estão atualizados.');
    }

    if (!resposta.ok) {
        throw new Error(json.erro || 'Algo deu errado. Tente de novo.');
    }

    return json;
}

// ---------- Mensagens (erro, sucesso, aviso) ----------
function mostrarMensagem(texto, tipo = 'erro') {
    const caixa = document.getElementById('mensagem');
    caixa.className = 'mensagem mensagem--' + tipo;
    caixa.textContent = texto;

    if (texto) {
        caixa.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function mostrarErro(erro) {
    mostrarMensagem(erro.message, 'erro');
}

function limparMensagem() {
    mostrarMensagem('', 'erro');
}

// ---------- Formatacao para exibir ----------
const formatar = {
    cpf: function (cpf) {
        const n = String(cpf).replace(/\D/g, '').slice(0, 11);
        return n
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
    },
    telefone: function (tel) {
        const n = String(tel).replace(/\D/g, '').slice(0, 11);
        if (n.length <= 2) return n.replace(/^(\d+)/, '($1');
        if (n.length <= 6) return n.replace(/^(\d{2})(\d+)/, '($1) $2');
        if (n.length <= 10) return n.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
        return n.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    },
    data: function (data) {
        return String(data).split('-').reverse().join('/');
    },
    valor: function (valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },
    capitalizar: function (texto) {
        texto = String(texto);
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }
};

// Remove acentos e deixa minusculo (para a busca)
function normalizar(texto) {
    return String(texto).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

// ---------- Formularios ----------

// Mascara de CPF e telefone enquanto digita, e limite da data de nascimento
function aplicarMascaras(form) {
    const cpf = form.querySelector('[name="cpf_usuario"]');
    const tel = form.querySelector('[name="telefone_usuario"]');
    const nascimento = form.querySelector('[name="data_nasc_usuario"]');

    if (nascimento) {
        // Data de hoje no formato AAAA-MM-DD (horario local)
        const hoje = new Date();
        hoje.setMinutes(hoje.getMinutes() - hoje.getTimezoneOffset());

        nascimento.min = '1900-01-01';
        nascimento.max = hoje.toISOString().slice(0, 10);

        nascimento.addEventListener('input', function () {
            if (nascimento.value > nascimento.max) {
                nascimento.setCustomValidity('A data de nascimento não pode ser no futuro.');
            } else if (nascimento.value && nascimento.value < nascimento.min) {
                nascimento.setCustomValidity('Confira o ano da data de nascimento.');
            } else {
                nascimento.setCustomValidity('');
            }
        });
    }

    if (cpf) cpf.addEventListener('input', function () { cpf.value = formatar.cpf(cpf.value); });
    if (tel) tel.addEventListener('input', function () { tel.value = formatar.telefone(tel.value); });
}

// Coloca os planos (com preco) no <select>
async function carregarPlanos(select) {
    const planos = await chamarApi('GET', 'api/planos.php');

    planos.forEach(function (plano) {
        const opcao = document.createElement('option');
        opcao.value = plano.id_plano;
        opcao.textContent = plano.nome_plano + ' – ' + formatar.valor(plano.valor_plano);
        select.appendChild(opcao);
    });
}

// Le o formulario e deixa CPF e telefone so com numeros (como a API espera)
function lerFormulario(form) {
    const dados = Object.fromEntries(new FormData(form));
    dados.cpf_usuario = (dados.cpf_usuario || '').replace(/\D/g, '');
    dados.telefone_usuario = (dados.telefone_usuario || '').replace(/\D/g, '');
    return dados;
}

// Desabilita o botao durante o envio (evita cadastro duplicado).
// Devolve uma funcao que restaura o botao.
function travarBotao(botao, textoEnquanto) {
    const original = botao.textContent;
    botao.disabled = true;
    botao.textContent = textoEnquanto;
    botao.setAttribute('aria-busy', 'true');

    return function () {
        botao.disabled = false;
        botao.textContent = original;
        botao.removeAttribute('aria-busy');
    };
}

// ---------- Janela de confirmacao (substitui o confirm do navegador) ----------
// Uso: if (await confirmar({ titulo, mensagem, nomeDestaque, textoConfirmar })) { ... }
function confirmar(opcoes) {
    return new Promise(function (resolver) {
        const janela = document.createElement('dialog');
        janela.className = 'modal';
        janela.setAttribute('aria-labelledby', 'modal-titulo');

        const titulo = document.createElement('h2');
        titulo.id = 'modal-titulo';
        titulo.className = 'modal__titulo';
        titulo.textContent = opcoes.titulo;

        const texto = document.createElement('p');
        texto.className = 'modal__texto';
        texto.append(opcoes.antes || '');
        if (opcoes.nomeDestaque) {
            const forte = document.createElement('strong');
            forte.textContent = opcoes.nomeDestaque;
            texto.append(forte);
        }
        texto.append(opcoes.depois || '');

        const acoes = document.createElement('div');
        acoes.className = 'modal__acoes';

        const cancelar = document.createElement('button');
        cancelar.type = 'button';
        cancelar.className = 'botao botao--secundario';
        cancelar.textContent = 'Cancelar';

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'botao ' + (opcoes.classeConfirmar || 'botao--vermelho');
        ok.textContent = opcoes.textoConfirmar || 'Confirmar';

        acoes.append(cancelar, ok);
        janela.append(titulo, texto, acoes);
        document.body.appendChild(janela);

        function fechar(resultado) {
            janela.close();
            janela.remove();
            resolver(resultado);
        }

        cancelar.onclick = function () { fechar(false); };
        ok.onclick = function () { fechar(true); };
        janela.addEventListener('cancel', function (e) { e.preventDefault(); fechar(false); });
        janela.addEventListener('click', function (e) { if (e.target === janela) fechar(false); });

        janela.showModal();
        cancelar.focus();
    });
}
