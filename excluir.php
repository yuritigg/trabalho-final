<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar</title>
</head>
<body>

    <?php
    require "usuarioDAO.php";

    if (isset($_GET["id"])) {
        $usuarioDAO = new UsuarioDAO();
        $usuarioDAO->excluir($_GET["id"]);
    }

    header("Location: listar.php");
    exit;
    ?>
</body>
</html>