<?php
require "usuarioDAO.php";

if (isset($_GET["id"])) {
    $usuarioDAO = new UsuarioDAO();
    $usuarioDAO->excluir($_GET["id"]);
}

header("Location: listar.php");
exit;