<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar</title>
</head>
<body>

    <?php
        require "usuario.php";
        $planos = json_decode(api('listarPlanos'), true);
        if (isset($_POST["nome_usuario"])) {
            $usuario = json_decode(api('inserir', $_POST), true);
        }
        header("Location: listar.php");
    ?>




<form action="?" method="post">
    <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
    <label for="">NOME COMPLETO</label>
    <input type="text" id="nome" name="nome_usuario">
    <label for="">EMAIL</label>
    <input type="email" id="email" name="email_usuario">
    <label for="">CPF</label>
    <input type="text" id="cpf" name="cpf_usuario">
    <label for="">DATA DE NASCIMENTO </label>
    <input type="date" id="data_nasc" name="data_nasc_usuario">
    <label for="">ENDEREÇO</label>
    <input type="text" id="endereco" name="endereco_usuario">
    <label for="">TELEFONE</label>
    <input type="tel" id="telefone" name="telefone_usuario">
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
    <input type="number" id="altura" name="altura_usuario">
    <label for="">PESO</label>
    <input type="number" id="peso" name="peso_usuario">
    <input type="submit">
</form>
<a href="listar.php">Voltar</a>
</body>
</html>