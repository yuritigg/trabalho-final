<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Academia Cooperberg</title>
</head>
<body>
    <p id="mensagem"></p>

    <table>
        <thead>
            <tr>
                <th>NOME</th>
                <th>PLANO</th>
            </tr>
        </thead>

        <tbody id="tabela-usuarios"></tbody>
    </table>

    <a href="cadastrar.php">Cadastrar</a>
    
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

    const tabela = document.getElementById('tabela-usuarios');

    }

        function celula(conteudo) {
            const td = document.createElement('td');
            if (typeof conteudo === 'string') {
                td.textContent = conteudo;
            } else {
                td.appendChild(conteudo);
            }
            return td;
        }

        function link(texto, href) {
            const a = document.createElement('a');
            a.textContent = texto;
            a.href = href;
            return a;
        }

        async function excluir(usuario, linha) {
            if (!confirm('Tem certeza que deseja excluir ' + usuario.nome_usuario + '?')) {
                return;
            }

            try {
                await chamarApi('DELETE', 'api/usuarios.php?id=' + usuario.id_usuario);
                linha.remove();
            } catch (erro) {
                mostrarErro(erro);
            }
        }

        async function listar() {
            try {
                const usuarios = await chamarApi('GET', 'api/usuarios.php');

                usuarios.forEach(function (usuario) {
                    const linha = document.createElement('tr');

                    const botaoExcluir = document.createElement('button');
                    botaoExcluir.textContent = 'Excluir';
                    botaoExcluir.onclick = function () { excluir(usuario, linha); };

                    linha.appendChild(celula(usuario.nome_usuario));
                    linha.appendChild(celula(usuario.nome_plano));
                    linha.appendChild(celula(link('Visualizar', 'visualizar.php?id=' + usuario.id_usuario)));
                    linha.appendChild(celula(link('Editar', 'editar.php?id=' + usuario.id_usuario)));
                    linha.appendChild(celula(botaoExcluir));

                    tabela.appendChild(linha);
                });
            } catch (erro) {
                mostrarErro(erro);
            }
        }

        listar();        

</script>
</body>
</html>