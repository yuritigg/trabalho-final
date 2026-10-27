<?php
require "usuarioDAO.php";
$usuarioDAO = new UsuarioDAO();

if (isset($_POST["nome_usuario"])) {
    $usuarioDAO->atualizar(
        $_POST["id_usuario"],          
        $_POST["nome_usuario"],
        $_POST["email_usuario"],
        $_POST["cpf_usuario"],
        $_POST["data_nasc_usuario"],
        $_POST["endereco_usuario"],
        $_POST["telefone_usuario"],
        $_POST["genero_usuario"],
        $_POST["plano_usuario"],
        $_POST["altura_usuario"],
        $_POST["peso_usuario"]);
        header("Location: listar.php");
        exit;
    }
    $usuario = $usuarioDAO->buscarPorId($_GET["id"]);
    $planos = $usuarioDAO->listarPlanos();
    ?>

<form action="?" method="post">
    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
    <label for="">NOME COMPLETO</label>
    <input type="text" name="nome_usuario" value="<?= $usuario['nome_usuario'] ?>">
    <label for="">EMAIL</label>
    <input type="email" name="email_usuario" value="<?= $usuario['email_usuario'] ?>">
    <label for="">CPF</label>
    <input type="text" name="cpf_usuario" value="<?= $usuario['cpf_usuario'] ?>">
    <label for="">DATA DE NASCIMENTO</label>
    <input type="date" name="data_nasc_usuario" value="<?= $usuario['data_nasc_usuario'] ?>"> 
    <label for="">ENDEREÇO</label>
    <input type="text" id="endereco" name="endereco_usuario" value="<?= $usuario['endereco_usuario'] ?>">
    <label for="">TELEFONE</label>
    <input type="tel" name="telefone_usuario" value="<?= $usuario['telefone_usuario'] ?>">
    <label for="">GENERO</label>
    <select name="genero_usuario" id="genero">
        <option value="masculino">Masculino</option>
        <option value="feminino">Feminino</option>
        <option value="outro">Outro</option>
    </select>
    <label for="">PLANOS</label>
    <select name="plano_usuario" id="">
        <?php 
            foreach ($planos as $plano) {
            echo "<option value='" . $plano['id_plano'] . "'>" . $plano['nome_plano'] . "</option>";
            }
        ?>
    </select>
    <label for="">ALTURA</label>
    <input type="number" name="altura_usuario" value="<?= $usuario['altura_usuario'] ?>">
    <label for="">PESO</label>
    <input type="number" name="peso_usuario" value="<?= $usuario['peso_usuario'] ?>">
    <input type="submit">
</form>