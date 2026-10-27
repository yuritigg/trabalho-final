

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Academia Cooperberg</title>
</head>
<body>
    <?php
        require "usuarioDAO.php";
        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->listar();
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
                echo "<td>". "<a href='editar.php?id=" . $user['id_usuario'] . "'>Editar</a>" . "</td>";
                echo "</tr>";
            }

        ?>
    </tbody>

</table>

</body>

</html>