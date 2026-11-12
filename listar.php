<?php
$titulo = 'Academia Cooperberg - Clientes';
$pagina = 'listar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--listar">
        <section class="cartao cartao--largo">
            <h1 class="cartao__titulo">Clientes</h1>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <div class="tabela-clientes">
                <div class="tabela-clientes__cabecalho">
                    <span>Nome</span>
                    <span>Plano</span>
                    <span class="so-leitor">Detalhes</span>
                </div>

                <ul id="tabela-usuarios" class="tabela-clientes__corpo"></ul>
            </div>
        </section>
    </main>

<?php include __DIR__ . '/partes/rodape.php'; ?>

<script>
    const TOKEN_API = 'meu-token-secreto-123';

    async function chamarApi(metodo, url, dados = null) {
        const opcoes = {
            method: metodo,
            headers: { 'Authorization': 'Bearer ' + TOKEN_API }
        };

        if (dados !== null) {
            opcoes.headers['Content-Type'] = 'application/json';
            opcoes.body = JSON.stringify(dados);
        }

        const resposta = await fetch(url, opcoes);
        const json = await resposta.json();

        if (!resposta.ok) {
            throw new Error(json.erro);
        }

        return json;
    }

    function mostrarErro(erro) {
        const caixa = document.getElementById('mensagem');
        caixa.textContent = erro.message;
        caixa.style.color = 'red';
    }

    // ---------- Formatacao dos dados para exibir ----------
    function formatarCpf(cpf) {
        return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    }

    function formatarTelefone(tel) {
        return tel.replace(/^(\d{2})(\d{4,5})(\d{4})$/, '($1) $2-$3');
    }

    function formatarData(data) {
        const [ano, mes, dia] = data.split('-');
        return dia + '/' + mes + '/' + ano;
    }

    function capitalizar(texto) {
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }

    function criar(tag, classe, texto) {
        const elemento = document.createElement(tag);
        if (classe) elemento.className = classe;
        if (texto !== undefined) elemento.textContent = texto;
        return elemento;
    }

    const tabela = document.getElementById('tabela-usuarios');

    async function excluir(usuario, item) {
        if (!confirm('Tem certeza que deseja excluir ' + usuario.nome_usuario + '?')) {
            return;
        }

        try {
            await chamarApi('DELETE', 'api/usuarios.php?id=' + usuario.id_usuario);
            item.remove();

            if (!tabela.children.length) {
                mostrarVazio();
            }
        } catch (erro) {
            mostrarErro(erro);
        }
    }

    // Busca os dados completos do usuario (so na primeira vez que expandir)
    async function preencherDetalhes(usuario, detalhes, item) {
        const dados = await chamarApi('GET', 'api/usuarios.php?id=' + usuario.id_usuario);

        const linhas = [
            ['E-mail', dados.email_usuario],
            ['CPF', formatarCpf(dados.cpf_usuario)],
            ['Data de Nascimento', formatarData(dados.data_nasc_usuario)],
            ['Endereço', dados.endereco_usuario],
            ['Telefone', formatarTelefone(dados.telefone_usuario)],
            ['Gênero', capitalizar(dados.genero_usuario)],
            ['Plano', dados.nome_plano],
            ['Altura(cm)', dados.altura_usuario],
            ['Peso(kg)', dados.peso_usuario]
        ];

        detalhes.textContent = '';
        linhas.forEach(function ([rotulo, valor]) {
            detalhes.appendChild(criar('p', '', rotulo + ': ' + valor));
        });

        detalhes.appendChild(criar('p', 'cliente__aviso', '*Confirme os dados antes de confirmar a exclusão*'));

        const acoes = criar('div', 'cliente__acoes');

        const botaoEditar = criar('a', 'botao botao--azul botao--pequeno', 'Editar');
        botaoEditar.href = 'editar.php?id=' + usuario.id_usuario;

        const botaoExcluir = criar('button', 'botao botao--vermelho botao--pequeno', 'Excluir');
        botaoExcluir.type = 'button';
        botaoExcluir.onclick = function () { excluir(usuario, item); };

        acoes.appendChild(botaoEditar);
        acoes.appendChild(botaoExcluir);
        detalhes.appendChild(acoes);
    }

    function criarItem(usuario) {
        const item = criar('li', 'cliente');

        const linha = criar('button', 'cliente__linha');
        linha.type = 'button';
        linha.setAttribute('aria-expanded', 'false');

        const expandir = criar('span', 'cliente__expandir');
        expandir.appendChild(criar('span', 'so-leitor', 'Expandir'));
        const seta = criar('img', 'cliente__seta');
        seta.src = 'img/seta.svg';
        seta.alt = '';
        expandir.appendChild(seta);

        linha.appendChild(criar('span', 'cliente__nome', usuario.nome_usuario));
        linha.appendChild(criar('span', 'cliente__plano', usuario.nome_plano));
        linha.appendChild(expandir);

        const detalhes = criar('div', 'cliente__detalhes', 'Carregando...');
        detalhes.hidden = true;

        let carregado = false;

        linha.onclick = async function () {
            const abrir = detalhes.hidden;
            detalhes.hidden = !abrir;
            item.classList.toggle('cliente--aberto', abrir);
            linha.setAttribute('aria-expanded', String(abrir));

            if (abrir && !carregado) {
                try {
                    await preencherDetalhes(usuario, detalhes, item);
                    carregado = true;
                } catch (erro) {
                    detalhes.textContent = erro.message;
                }
            }
        };

        item.appendChild(linha);
        item.appendChild(detalhes);
        return item;
    }

    function mostrarVazio() {
        tabela.appendChild(criar('li', 'tabela-clientes__vazio', 'Nenhum cliente cadastrado.'));
    }

    async function listar() {
        try {
            const usuarios = await chamarApi('GET', 'api/usuarios.php');

            if (!usuarios.length) {
                mostrarVazio();
                return;
            }

            usuarios.forEach(function (usuario) {
                tabela.appendChild(criarItem(usuario));
            });
        } catch (erro) {
            mostrarErro(erro);
        }
    }

    listar();
</script>
</body>
</html>