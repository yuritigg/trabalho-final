<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar</title>
</head>
<body>

    <p id="mensagem"></p>

    <form id="form-usuario">
        <label for="nome">NOME COMPLETO</label>
        <input type="text" id="nome" name="nome_usuario" required>
        <label for="email">EMAIL</label>
        <input type="email" id="email" name="email_usuario" required>
        <label for="cpf">CPF</label>
        <input type="text" id="cpf" name="cpf_usuario" maxlength="14" required>
        <label for="data_nasc">DATA DE NASCIMENTO</label>
        <input type="date" id="data_nasc" name="data_nasc_usuario" required>
        <label for="endereco">ENDEREÇO</label>
        <input type="text" id="endereco" name="endereco_usuario" required>
        <label for="telefone">TELEFONE</label>
        <input type="tel" id="telefone" name="telefone_usuario" maxlength="15" required>
        <label for="genero">GENERO</label>
        <select name="genero_usuario" id="genero">
            <option value="masculino">Masculino</option>
            <option value="feminino">Feminino</option>
            <option value="outro">Outro</option>
        </select>
        <label for="plano">PLANOS</label>
        <select name="id_plano" id="plano"></select>
        <label for="altura">ALTURA (cm)</label>
        <input type="number" id="altura" name="altura_usuario" min="1" required>
        <label for="peso">PESO (kg)</label>
        <input type="number" id="peso" name="peso_usuario" min="1" required>
        <input type="submit" value="Salvar">
    </form>
    <a href="listar.php">Voltar</a>

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

        async function carregarPlanos(select) {
            const planos = await chamarApi('GET', 'api/planos.php');

            planos.forEach(function (plano) {
                const opcao = document.createElement('option');
                opcao.value = plano.id_plano;
                opcao.textContent = plano.nome_plano;
                select.appendChild(opcao);
            });
        }

        const form = document.getElementById('form-usuario');
        const id = new URLSearchParams(window.location.search).get('id');

        // Carrega os planos e o usuario, e preenche o formulario
        async function carregar() {
            try {
                await carregarPlanos(document.getElementById('plano'));
                const usuario = await chamarApi('GET', 'api/usuarios.php?id=' + id);

                for (const campo of form.elements) {
                    if (campo.name && usuario[campo.name] !== undefined) {
                        campo.value = usuario[campo.name];
                    }
                }
            } catch (erro) {
                mostrarErro(erro);
            }
        }

        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();   

            try {
                await chamarApi('PUT', 'api/usuarios.php?id=' + id, Object.fromEntries(new FormData(form)));
                window.location.href = 'listar.php';
            } catch (erro) {
                mostrarErro(erro);
            }
        });

        carregar();
    </script>
</body>
</html>