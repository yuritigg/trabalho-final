<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Academia Cooperberg</title>
</head>
<body>

        <?php
            require "usuario.php";
            $usuario = json_decode(api('listar'), true);
        ?>

    <table>
        <thead>
            <tr>
                <th>NOME</th>
                <th>PLANO</th>
            </tr>
        </thead>

        <tbody>
            <?php 
                foreach($usuario as $user) {
                    echo "<tr>";
                    echo "<td>". $user['nome_usuario']."</td>";
                    echo "<td>". $user['nome_plano']."</td>";
                    echo "<td>". "<a href='visualizar.php?id=" . $user['id_usuario'] . "'>Visualizar</a>" . "</td>";
                    echo "<td>". "<a href='editar.php?id=" . $user['id_usuario'] . "'>Editar</a>" . "</td>";
                    echo "<td>". "<a href='excluir.php?id=" . $user['id_usuario'] . "' onclick=\"return confirm('Tem certeza que deseja excluir?');\">Excluir</a></td>";
                    echo "</tr>";
                }

            ?>
        </tbody>

    </table>
    <a href="cadastrar.php">Cadastrar</a>
</body>

</html>