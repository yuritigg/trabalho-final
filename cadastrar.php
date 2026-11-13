<?php
$titulo = 'Novo aluno';
$pagina = 'cadastrar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--cadastrar">
        <section class="cartao">
            <h1 class="cartao__titulo">Novo aluno</h1>
            <p class="cartao__subtitulo">Todos os campos são obrigatórios.</p>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <form id="form-usuario" class="formulario">
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
                        <input class="campo" type="number" id="altura" name="altura_usuario" min="1" placeholder="Ex.: 175" required>
                    </div>
                    <div>
                        <label for="peso">Peso (kg)</label>
                        <input class="campo" type="number" id="peso" name="peso_usuario" min="1" placeholder="Ex.: 70" required>
                    </div>
                </div>

                <label class="termos">
                    <input type="checkbox" id="termos" required>
                    <span>O aluno assinou o contrato e o regulamento interno da Academia Cooperberg.</span>
                </label>

                <button type="submit" id="botao-salvar" class="botao botao--verde">Cadastrar aluno</button>
            </form>
        </section>
    </main>

<?php include __DIR__ . '/partes/rodape.php'; ?>

    <script>
        const form = document.getElementById('form-usuario');
        const botao = document.getElementById('botao-salvar');

        aplicarMascaras(form);
        carregarPlanos(document.getElementById('plano')).catch(mostrarErro);

        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();
            limparMensagem();

            const destravar = travarBotao(botao, 'Salvando…');
            const dados = lerFormulario(form);

            try {
                await chamarApi('POST', 'api/usuarios.php', dados);
                window.location.href = 'listar.php?ok=cadastrado&nome=' + encodeURIComponent(dados.nome_usuario);
            } catch (erro) {
                destravar();
                mostrarErro(erro);
            }
        });
    </script>
</body>
</html>
