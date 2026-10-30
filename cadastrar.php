<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar</title>
</head>
<body>
    <nav>
        <a href="listar.php">Listar</a>
        <a href="cadastrar.php">Cadastrar</a>
    </nav>
    <?php
        require "usuarioDAO.php";
        $usuarioDAO = new UsuarioDAO();
        $planos = $usuarioDAO->listarPlanos();
        $planos = json_decode($planos, true);
        if (isset($_POST["nome_usuario"])) {
            $usuario = [
                'nome' => $_POST["nome_usuario"],
                'email' => $_POST["email_usuario"],
                'cpf' => $_POST["cpf_usuario"],
                'data_nasc' => $_POST["data_nasc_usuario"],
                'endereco' => $_POST["endereco_usuario"],
                'telefone' => $_POST["telefone_usuario"],
                'genero' => $_POST["genero_usuario"],
                'plano' => $_POST["plano_usuario"],
                'altura' => $_POST["altura_usuario"],
                'peso' => $_POST["peso_usuario"]
                ];
                print_r($usuario);
            // $usuario = $usuarioDAO->inserir($usuario);
            };
    ?>
    



<form action="?" method="post">
    <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
    <label for="">NOME COMPLETO</label>
    <input type="text" id="nome" name="nome_usuario" required>
    <label for="">EMAIL</label>
    <input type="email" id="email" name="email_usuario" required>
    <label for="">CPF</label>
    <input type="text" id="cpf" name="cpf_usuario" required>
    <label for="">DATA DE NASCIMENTO </label>
    <input type="date" id="data_nasc" name="data_nasc_usuario" required>
    <label for="">ENDEREÇO</label>
    <input type="text" id="endereco" name="endereco_usuario" required>
    <label for="">TELEFONE</label>
    <input type="tel" id="telefone" name="telefone_usuario" required>
    <label for="">GENERO</label>
    <select name="genero_usuario" id="genero">
        <option value="masculino">Masculino</option>
        <option value="feminino">Feminino</option>
        <option value="outro">Outro</option>
    </select>
    <label for="">PLANOS</label>
    <select name="plano_usuario" id="" required>
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
    <input type="submit" onclick="ValidaDados()">

    

</form>
</body>
<!-- <script src=".\assets\validator.js\src\index.js">

</script>

<script>console.log(isDate(document.querySelector('#data_nasc')))</script> -->

<!-- <script>
    function ValidaDados() {
        let StatusValidacao = true

        const cpf = document.querySelector('#cpf').value
        if (cpf.length > 11) {
            StatusValidacao = false
            console.log('CPF INVÁLIDO') 
             
            
        }
        // const data_nasc = document.querySelector('#data_nasc')
        // if (cpf.sizeof() > 11) {
        //     StatusValidacao = FALSE
        //     console.log('CPF INVÁLIDO') 
        // }
        const altura = document.querySelector('#altura').value
        if (altura < 0) {
            StatusValidacao = false
            console.log('ALTURA INVÁLIDO') 
        }
        const peso = document.querySelector('#peso').value
        if (peso < 0) {
            StatusValidacao = false
            console.log('ALTURA INVÁLIDO') 
        }

        return
    }

</script> -->

</html>