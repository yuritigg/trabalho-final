<?php
echo $_POST['nome_plano'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>    </title>
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
                echo "</tr>";
            }

        ?>
    </tbody>

</table>
<form action="?" method="post">
    <label for="">planos</label>
    <select name="nome_plano" id="">
        <?php 
            foreach($usuario as $user) {
                echo "<option value='". $user['id_usuario']."'>". $user['nome_usuario']."</option>";
            }


        ?>
    </select>
    <input type="submit">
</form>
</body>

</html>