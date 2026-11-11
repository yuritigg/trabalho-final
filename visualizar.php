<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizar</title>
</head>
<body>

    <p id="mensagem"></p>

    <h1 id="nome_usuario"></h1>
    <p><strong>EMAIL:</strong> <span id="email_usuario"></span></p>
    <p><strong>CPF:</strong> <span id="cpf_usuario"></span></p>
    <p><strong>DATA DE NASCIMENTO:</strong> <span id="data_nasc_usuario"></span></p>
    <p><strong>ENDEREÇO:</strong> <span id="endereco_usuario"></span></p>
    <p><strong>TELEFONE:</strong> <span id="telefone_usuario"></span></p>
    <p><strong>GENERO:</strong> <span id="genero_usuario"></span></p>
    <p><strong>PLANO:</strong> <span id="nome_plano"></span></p>
    <p><strong>ALTURA (cm):</strong> <span id="altura_usuario"></span></p>
    <p><strong>PESO (kg):</strong> <span id="peso_usuario"></span></p>

    <a id="link-editar" href="#">Editar</a>
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

        async function visualizar() {
            const id = new URLSearchParams(window.location.search).get('id');

            try {
                const usuario = await chamarApi('GET', 'api/usuarios.php?id=' + id);

                // Cada <span> tem o mesmo id da coluna no banco
                for (const campo in usuario) {
                    const elemento = document.getElementById(campo);
                    if (elemento) {
                        elemento.textContent = usuario[campo];
                    }
                }

                document.getElementById('link-editar').href = 'editar.php?id=' + id;
            } catch (erro) {
                mostrarErro(erro);
            }
        }

        visualizar();
    </script>
</body>
</html>