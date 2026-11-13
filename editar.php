<?php
$titulo = 'Editar aluno';
$pagina = 'editar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--editar">
        <section class="cartao">
            <h1 class="cartao__titulo">Editar aluno</h1>
            <p class="cartao__subtitulo">Busque o aluno pelo nome, CPF ou ID.</p>

            <form id="form-busca" class="busca" role="search">
                <label for="busca" class="so-leitor">Buscar aluno por nome, CPF ou ID</label>
                <input class="busca__campo" type="search" id="busca" placeholder="Nome, CPF ou ID" autocomplete="off" required>
                <button type="submit" class="busca__botao" aria-label="Buscar">
                    <img src="img/lupa.png" alt="" width="22" height="22">
                </button>
            </form>

            <ul id="resultados" class="resultados" aria-label="Alunos encontrados" hidden></ul>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <form id="form-usuario" class="formulario">
                <fieldset id="campos" class="formulario__grupo" disabled>
                    <label for="nome">Nome completo</label>
                    <input class="campo" type="text" id="nome" name="nome_usuario" autocomplete="off" required>

                    <label for="email">E-mail</label>
                    <input class="campo" type="email" id="email" name="email_usuario" autocomplete="off" required>

                    <label for="cpf">CPF</label>
                    <input class="campo" type="text" id="cpf" name="cpf_usuario" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" required>

                    <label for="data_nasc">Data de nascimento</label>
                    <input class="campo" type="date" id="data_nasc" name="data_nasc_usuario" required>

                    <label for="endereco">Endereço</label>
                    <input class="campo" type="text" id="endereco" name="endereco_usuario" autocomplete="off" required>

                    <label for="telefone">Telefone</label>
                    <input class="campo" type="tel" id="telefone" name="telefone_usuario" maxlength="15" placeholder="(00) 00000-0000" required>

                    <label for="genero">Gênero</label>
                    <select class="campo" name="genero_usuario" id="genero" required>
                        <option value="" disabled selected>Selecione…</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="outro">Outro</option>
                    </select>

                    <label for="plano">Plano</label>
                    <select class="campo" name="id_plano" id="plano" required>
                        <option value="" disabled selected>Selecione…</option>
                    </select>

                    <div class="formulario__linha">
                        <div>
                            <label for="altura">Altura (cm)</label>
                            <input class="campo" type="number" id="altura" name="altura_usuario" min="1" required>
                        </div>
                        <div>
                            <label for="peso">Peso (kg)</label>
                            <input class="campo" type="number" id="peso" name="peso_usuario" min="1" required>
                        </div>
                    </div>

                    <button type="submit" id="botao-salvar" class="botao botao--azul">Salvar alterações</button>
                    <a href="listar.php" class="botao botao--secundario">Cancelar</a>
                </fieldset>
            </form>
        </section>
    </main>

<?php include __DIR__ . '/partes/rodape.php'; ?>

    <script>
        const form = document.getElementById('form-usuario');
        const campos = document.getElementById('campos');
        const botao = document.getElementById('botao-salvar');
        const formBusca = document.getElementById('form-busca');
        const campoBusca = document.getElementById('busca');
        const resultados = document.getElementById('resultados');
        const planosCarregados = carregarPlanos(document.getElementById('plano'));

        let id = new URLSearchParams(window.location.search).get('id');
        let listaAlunos = null;   // carregada so na primeira busca

        aplicarMascaras(form);

        // Carrega o aluno e preenche o formulario
        async function carregar(idAluno) {
            resultados.hidden = true;

            try {
                await planosCarregados;
                const usuario = await chamarApi('GET', 'api/usuarios.php?id=' + idAluno);

                for (const campo of form.elements) {
                    if (campo.name && usuario[campo.name] !== undefined) {
                        campo.value = usuario[campo.name];
                    }
                }

                form.cpf_usuario.value = formatar.cpf(usuario.cpf_usuario);
                form.telefone_usuario.value = formatar.telefone(usuario.telefone_usuario);

                id = idAluno;
                campoBusca.value = usuario.nome_usuario;
                campos.disabled = false;
                limparMensagem();
                history.replaceState(null, '', 'editar.php?id=' + idAluno);
                document.getElementById('nome').focus();
            } catch (erro) {
                form.reset();
                campos.disabled = true;
                mostrarErro(erro);
            }
        }

        // Mostra a lista de alunos encontrados para escolher
        function mostrarResultados(encontrados) {
            resultados.textContent = '';

            encontrados.forEach(function (aluno) {
                const item = document.createElement('li');
                const escolher = document.createElement('button');
                escolher.type = 'button';
                escolher.className = 'resultados__item';

                const nome = document.createElement('strong');
                nome.textContent = aluno.nome_usuario;
                const extra = document.createElement('span');
                extra.textContent = 'CPF ' + formatar.cpf(aluno.cpf_usuario) + ' · ' + aluno.nome_plano;

                escolher.append(nome, extra);
                escolher.onclick = function () { carregar(aluno.id_usuario); };
                item.appendChild(escolher);
                resultados.appendChild(item);
            });

            resultados.hidden = false;
        }

        formBusca.addEventListener('submit', async function (evento) {
            evento.preventDefault();
            const termo = normalizar(campoBusca.value);
            const numeros = termo.replace(/\D/g, '');

            try {
                listaAlunos = listaAlunos || await chamarApi('GET', 'api/usuarios.php');
            } catch (erro) {
                mostrarErro(erro);
                return;
            }

            const soNumeros = numeros !== '' && numeros === termo.replace(/[.\-\s]/g, '');
            let encontrados;

            if (soNumeros) {
                // Numero: primeiro tenta o ID exato; se nao achar, procura no CPF
                encontrados = listaAlunos.filter(function (a) { return String(a.id_usuario) === numeros; });
                if (!encontrados.length) {
                    encontrados = listaAlunos.filter(function (a) { return String(a.cpf_usuario).includes(numeros); });
                }
            } else {
                encontrados = listaAlunos.filter(function (a) { return normalizar(a.nome_usuario).includes(termo); });
            }

            if (encontrados.length === 1) {
                carregar(encontrados[0].id_usuario);
            } else if (encontrados.length > 1) {
                limparMensagem();
                mostrarResultados(encontrados);
                mostrarMensagem(encontrados.length + ' alunos encontrados. Escolha um na lista acima.', 'info');
            } else {
                resultados.hidden = true;
                mostrarMensagem('Nenhum aluno encontrado para “' + campoBusca.value + '”.', 'info');
            }
        });

        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();
            limparMensagem();

            const destravar = travarBotao(botao, 'Salvando…');
            const dados = lerFormulario(form);

            try {
                await chamarApi('PUT', 'api/usuarios.php?id=' + id, dados);
                window.location.href = 'listar.php?ok=editado&nome=' + encodeURIComponent(dados.nome_usuario);
            } catch (erro) {
                destravar();
                mostrarErro(erro);
            }
        });

        if (id) {
            carregar(id);
        } else {
            planosCarregados.catch(mostrarErro);
            campoBusca.focus();
        }
    </script>
</body>
</html>
    