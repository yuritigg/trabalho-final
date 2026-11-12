<?php
$titulo = 'Academia Cooperberg - Editar';
$pagina = 'editar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--editar">
        <section class="cartao">
            <h1 class="so-leitor">Editar usuário</h1>

            <form id="form-busca" class="busca" role="search">
                <button type="submit" class="busca__botao" aria-label="Pesquisar">
                    <img src="img/lupa.png" alt="" width="33" height="33">
                </button>
                <input class="busca__campo" type="number" id="busca-id" min="1"
                       placeholder="Pesquise o ID..." aria-label="ID do usuário" required>
            </form>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <form id="form-usuario" class="formulario formulario--editar">
                <fieldset id="campos" class="formulario__grupo" disabled>
                    <label for="nome">Nome Completo:</label>
                    <input class="campo" type="text" id="nome" name="nome_usuario" required>

                    <label for="email">E-mail:</label>
                    <input class="campo" type="email" id="email" name="email_usuario" required>

                    <label for="cpf">CPF:</label>
                    <input class="campo" type="text" id="cpf" name="cpf_usuario" maxlength="14" inputmode="numeric" required>

                    <label for="data_nasc">Data de Nascimento:</label>
                    <input class="campo" type="date" id="data_nasc" name="data_nasc_usuario" required>

                    <label for="endereco">Endereço:</label>
                    <input class="campo" type="text" id="endereco" name="endereco_usuario" required>

                    <label for="telefone">Telefone:</label>
                    <input class="campo" type="tel" id="telefone" name="telefone_usuario" maxlength="15" required>

                    <label for="genero">Gênero:</label>
                    <select class="campo" name="genero_usuario" id="genero">
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="outro">Outro</option>
                    </select>

                    <label for="plano">Plano:</label>
                    <select class="campo" name="id_plano" id="plano"></select>

                    <label for="altura">Altura(cm):</label>
                    <input class="campo" type="number" id="altura" name="altura_usuario" min="1" required>

                    <label for="peso">Peso(kg):</label>
                    <input class="campo" type="number" id="peso" name="peso_usuario" min="1" required>

                    <button type="submit" class="botao botao--azul">SALVAR ALTERAÇÕES</button>
                </fieldset>
            </form>
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

        const caixaMensagem = document.getElementById('mensagem');

        function mostrarErro(erro) {
            caixaMensagem.classList.remove('mensagem--info');
            caixaMensagem.textContent = erro.message;
            caixaMensagem.style.color = 'red';
        }

        function mostrarInfo(texto) {
            caixaMensagem.classList.add('mensagem--info');
            caixaMensagem.textContent = texto;
        }

        function formatarValor(valor) {
            return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        async function carregarPlanos(select) {
            const planos = await chamarApi('GET', 'api/planos.php');

            planos.forEach(function (plano) {
                const opcao = document.createElement('option');
                opcao.value = plano.id_plano;
                opcao.textContent = plano.nome_plano + ' - ' + formatarValor(plano.valor_plano);
                select.appendChild(opcao);
            });
        }

        function somenteNumeros(dados) {
            dados.cpf_usuario = dados.cpf_usuario.replace(/\D/g, '');
            dados.telefone_usuario = dados.telefone_usuario.replace(/\D/g, '');
            return dados;
        }

        const form = document.getElementById('form-usuario');
        const campos = document.getElementById('campos');
        const formBusca = document.getElementById('form-busca');
        const campoBusca = document.getElementById('busca-id');
        const planosCarregados = carregarPlanos(document.getElementById('plano'));

        // O ID pode vir pela URL (editar.php?id=3) ou pela barra de pesquisa
        let id = new URLSearchParams(window.location.search).get('id');

        // Carrega o usuario e preenche o formulario
        async function carregar(idBuscado) {
            try {
                await planosCarregados;
                const usuario = await chamarApi('GET', 'api/usuarios.php?id=' + idBuscado);

                for (const campo of form.elements) {
                    if (campo.name && usuario[campo.name] !== undefined) {
                        campo.value = usuario[campo.name];
                    }
                }

                id = idBuscado;
                campoBusca.value = idBuscado;
                campos.disabled = false;
                caixaMensagem.textContent = '';
                history.replaceState(null, '', 'editar.php?id=' + idBuscado);
            } catch (erro) {
                form.reset();
                campos.disabled = true;
                mostrarErro(erro);
            }
        }

        formBusca.addEventListener('submit', function (evento) {
            evento.preventDefault();
            carregar(campoBusca.value.trim());
        });

        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();

            try {
                const dados = somenteNumeros(Object.fromEntries(new FormData(form)));
                await chamarApi('PUT', 'api/usuarios.php?id=' + id, dados);
                window.location.href = 'listar.php';
            } catch (erro) {
                mostrarErro(erro);
            }
        });

        if (id) {
            carregar(id);
        } else {
            planosCarregados.catch(mostrarErro);
            mostrarInfo('Pesquise o ID do cliente para editar.');
        }
    </script>
</body>
</html>
