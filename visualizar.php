<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizar</title>
</head>
<body>
    <?php
        require "usuario.php";
        $usuario = json_decode(api('buscarPorId', ['id' => $_GET["id"]]), true);
        $planos = json_decode(api('listarPlanos'), true);

        $nomePlano = "";
        foreach ($planos as $plano) {
            if ($plano['id_plano'] == $usuario['id_plano']) {
                $nomePlano = $plano['nome_plano'];
            }
        }
    ?>

    <h1><?= $usuario['nome_usuario'] ?></h1>
    <p><strong>EMAIL:</strong> <?= $usuario['email_usuario'] ?></p>
    <p><strong>CPF:</strong> <?= $usuario['cpf_usuario'] ?></p>
    <p><strong>DATA DE NASCIMENTO:</strong> <?= $usuario['data_nasc_usuario'] ?></p>
    <p><strong>ENDEREÇO:</strong> <?= $usuario['endereco_usuario'] ?></p>
    <p><strong>TELEFONE:</strong> <?= $usuario['telefone_usuario'] ?></p>
    <p><strong>GENERO:</strong> <?= $usuario['genero_usuario'] ?></p>
    <p><strong>PLANO:</strong> <?= $nomePlano ?></p>
    <p><strong>ALTURA:</strong> <?= $usuario['altura_usuario'] ?></p>
    <p><strong>PESO:</strong> <?= $usuario['peso_usuario'] ?></p>

    <a href="editar.php?id=<?= $usuario['id_usuario'] ?>">Editar</a>
    <a href="listar.php">Voltar</a>
</body>
</html>