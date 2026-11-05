<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar</title>
</head>
<body>

    <?php
    require "usuario.php";

    if (isset($_GET["id"])) {
        json_decode(api('excluir', ['id' => $_GET["id"]]), true);
    }

    header("Location: listar.php");
    exit;
    ?>
    
</body>
</html>