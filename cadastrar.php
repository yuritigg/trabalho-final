<?php
$titulo = 'Academia Cooperberg - Cadastrar';
$pagina = 'cadastrar';
include __DIR__ . '/partes/topo.php';
?>
    <main class="conteudo conteudo--cadastrar">
        <section class="cartao">
            <h1 class="cartao__titulo">NOVO USUÁRIO</h1>

            <p id="mensagem" class="mensagem" role="alert"></p>

            <form id="form-usuario" class="formulario">
                <label for="nome">Nome Completo:</label>
                <input class="campo" type="text" id="nome" name="nome_usuario" required>

                <label for="email">E-mail:</label>
                <input class="campo" type="email" id="email" name="email_usuario" required>

                <label for="cpf">CPF:</label>
                <input class="campo" type="text" id="cpf" name="cpf_usuario" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" required>

                <label for="data_nasc">Data de Nascimento:</label>
                <input class="campo" type="date" id="data_nasc" name="data_nasc_usuario" required>

                <label for="endereco">Endereço:</label>
                <input class="campo" type="text" id="endereco" name="endereco_usuario" required>

                <label for="telefone">Telefone:</label>
                <input class="campo" type="tel" id="telefone" name="telefone_usuario" maxlength="15" placeholder="(00) 00000-0000" required>

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

                <label class="termos">
                    <input type="checkbox" id="termos" required>
                    Li e aceito os Termos de Contrato e o Regulamento Interno da Academia Cooperberg.
                </label>

                <button type="submit" class="botao botao--verde">CADASTRAR</button>
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

        function mostrarErro(erro) {
            const caixa = document.getElementById('mensagem');
            caixa.textContent = erro.message;
            caixa.style.color = 'red';
            caixa.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

        // A API espera CPF e telefone so com numeros
        function somenteNumeros(dados) {
            dados.cpf_usuario = dados.cpf_usuario.replace(/\D/g, '');
            dados.telefone_usuario = dados.telefone_usuario.replace(/\D/g, '');
            return dados;
        }

        const form = document.getElementById('form-usuario');

        carregarPlanos(document.getElementById('plano')).catch(mostrarErro);

        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();

            try {
                const dados = somenteNumeros(Object.fromEntries(new FormData(form)));
                await chamarApi('POST', 'api/usuarios.php', dados);
                window.location.href = 'listar.php';
            } catch (erro) {
                mostrarErro(erro);
            }
        });
    </script>
</body>
</html>
