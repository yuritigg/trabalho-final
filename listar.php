<?php
$titulo = 'Alunos';
$pagina = 'listar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--listar">
        <section class="cartao cartao--largo">
            <div class="cartao__topo">
                <div>
                    <h1 class="cartao__titulo">Alunos</h1>
                    <p id="contador" class="contador" aria-live="polite"></p>
                </div>
                <a href="cadastrar.php" class="botao botao--verde botao--compacto">+ Novo aluno</a>
            </div>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <div class="busca">
                <img class="busca__icone" src="img/lupa.png" alt="" width="22" height="22">
                <label for="filtro" class="so-leitor">Buscar aluno por nome ou CPF</label>
                <input class="busca__campo" type="search" id="filtro" placeholder="Buscar por nome ou CPF" autocomplete="off">
            </div>

            <div class="tabela-alunos">
                <div class="tabela-alunos__cabecalho">
                    <div class="aluno__colunas">
                        <span>Nome</span>
                        <span>Plano</span>
                    </div>
                    <span class="tabela-alunos__acoes">Ações</span>
                </div>

                <ul id="tabela-usuarios" class="tabela-alunos__corpo" aria-label="Lista de alunos"></ul>
            </div>
        </section>
    </main>

<?php include __DIR__ . '/partes/rodape.php'; ?>

<script>
    const tabela = document.getElementById('tabela-usuarios');
    const campoFiltro = document.getElementById('filtro');
    const contador = document.getElementById('contador');

    const ICONE_EDITAR = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
    const ICONE_EXCLUIR = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>';

    let alunos = [];   // lista completa vinda da API

    function criar(tag, classe, texto) {
        const elemento = document.createElement(tag);
        if (classe) elemento.className = classe;
        if (texto !== undefined) elemento.textContent = texto;
        return elemento;
    }

    function atualizarContador(mostrados) {
        const total = alunos.length;
        const palavra = total === 1 ? 'aluno cadastrado' : 'alunos cadastrados';

        contador.textContent = mostrados === total
            ? total + ' ' + palavra
            : mostrados + ' de ' + total + ' ' + palavra;
    }

    async function excluir(usuario) {
        const confirmado = await confirmar({
            titulo: 'Excluir aluno',
            antes: 'Tem certeza que deseja excluir ',
            nomeDestaque: usuario.nome_usuario,
            depois: '? Essa ação não pode ser desfeita.',
            textoConfirmar: 'Excluir aluno'
        });

        if (!confirmado) return;

        try {
            await chamarApi('DELETE', 'api/usuarios.php?id=' + usuario.id_usuario);
            alunos = alunos.filter(function (a) { return a.id_usuario !== usuario.id_usuario; });
            filtrar();
            mostrarMensagem('Aluno ' + usuario.nome_usuario + ' excluído.', 'sucesso');
        } catch (erro) {
            mostrarErro(erro);
        }
    }

    // Busca os dados completos do aluno (so na primeira vez que expandir)
    async function preencherDetalhes(usuario, detalhes) {
        const dados = await chamarApi('GET', 'api/usuarios.php?id=' + usuario.id_usuario);

        const linhas = [
            ['E-mail', dados.email_usuario],
            ['CPF', formatar.cpf(dados.cpf_usuario)],
            ['Data de nascimento', formatar.data(dados.data_nasc_usuario)],
            ['Endereço', dados.endereco_usuario],
            ['Telefone', formatar.telefone(dados.telefone_usuario)],
            ['Gênero', formatar.capitalizar(dados.genero_usuario)],
            ['Plano', dados.nome_plano],
            ['Altura', dados.altura_usuario + ' cm'],
            ['Peso', dados.peso_usuario + ' kg']
        ];

        const lista = criar('dl', 'aluno__dados');
        linhas.forEach(function ([rotulo, valor]) {
            lista.appendChild(criar('dt', '', rotulo));
            lista.appendChild(criar('dd', '', valor));
        });

        detalhes.textContent = '';
        detalhes.appendChild(lista);
    }

    function criarItem(usuario) {
        const item = criar('li', 'aluno');
        const linha = criar('div', 'aluno__linha');

        // Botao que abre/fecha os detalhes
        const abrir = criar('button', 'aluno__abrir');
        abrir.type = 'button';
        abrir.setAttribute('aria-expanded', 'false');

        const colunas = criar('span', 'aluno__colunas');
        colunas.appendChild(criar('span', 'aluno__nome', usuario.nome_usuario));
        colunas.appendChild(criar('span', 'aluno__plano', usuario.nome_plano));
        const seta = criar('img', 'aluno__seta');
        seta.src = 'img/seta.svg';
        seta.alt = '';
        colunas.appendChild(seta);
        abrir.appendChild(colunas);
        abrir.appendChild(criar('span', 'so-leitor', ' – ver dados'));

        // Acoes sempre visiveis
        const editar = criar('a', 'icone-botao icone-botao--editar');
        editar.href = 'editar.php?id=' + usuario.id_usuario;
        editar.innerHTML = ICONE_EDITAR;
        editar.title = 'Editar ' + usuario.nome_usuario;
        editar.setAttribute('aria-label', 'Editar ' + usuario.nome_usuario);

        const apagar = criar('button', 'icone-botao icone-botao--excluir');
        apagar.type = 'button';
        apagar.innerHTML = ICONE_EXCLUIR;
        apagar.title = 'Excluir ' + usuario.nome_usuario;
        apagar.setAttribute('aria-label', 'Excluir ' + usuario.nome_usuario);
        apagar.onclick = function () { excluir(usuario); };

        const acoes = criar('span', 'aluno__acoes');
        acoes.append(editar, apagar);
        linha.append(abrir, acoes);

        const detalhes = criar('div', 'aluno__detalhes', 'Carregando...');
        detalhes.hidden = true;

        let carregado = false;

        abrir.onclick = async function () {
            const abrirAgora = detalhes.hidden;
            detalhes.hidden = !abrirAgora;
            item.classList.toggle('aluno--aberto', abrirAgora);
            abrir.setAttribute('aria-expanded', String(abrirAgora));

            if (abrirAgora && !carregado) {
                try {
                    await preencherDetalhes(usuario, detalhes);
                    carregado = true;
                } catch (erro) {
                    detalhes.textContent = erro.message;
                }
            }
        };

        item.append(linha, detalhes);
        return item;
    }

    // Mostra so os alunos que batem com a busca (nome ou CPF)
    function filtrar() {
        const termo = normalizar(campoFiltro.value);
        const numeros = termo.replace(/\D/g, '');

        const encontrados = alunos.filter(function (a) {
            return normalizar(a.nome_usuario).includes(termo)
                || (numeros.length >= 3 && String(a.cpf_usuario).includes(numeros));
        });

        tabela.textContent = '';

        if (!alunos.length) {
            tabela.appendChild(criar('li', 'tabela-alunos__vazio', 'Nenhum aluno cadastrado ainda.'));
        } else if (!encontrados.length) {
            tabela.appendChild(criar('li', 'tabela-alunos__vazio', 'Nenhum aluno encontrado para “' + campoFiltro.value + '”.'));
        }

        encontrados.forEach(function (usuario) {
            tabela.appendChild(criarItem(usuario));
        });

        atualizarContador(encontrados.length);
    }

    // Aviso vindo de outra pagina (ex.: listar.php?ok=cadastrado&nome=Maria)
    function mostrarAvisoDaUrl() {
        const params = new URLSearchParams(window.location.search);
        const nome = params.get('nome') || '';
        const avisos = {
            cadastrado: 'Aluno ' + nome + ' cadastrado com sucesso.',
            editado: 'Alterações de ' + nome + ' salvas com sucesso.'
        };

        if (avisos[params.get('ok')]) {
            mostrarMensagem(avisos[params.get('ok')], 'sucesso');
            history.replaceState(null, '', 'listar.php');
        }
    }

    async function listar() {
        tabela.appendChild(criar('li', 'tabela-alunos__vazio', 'Carregando alunos...'));

        try {
            alunos = await chamarApi('GET', 'api/usuarios.php');
            filtrar();
        } catch (erro) {
            tabela.textContent = '';
            mostrarErro(erro);
        }
    }

    campoFiltro.addEventListener('input', filtrar);

    mostrarAvisoDaUrl();
    listar();
</script>
</body>
</html>
