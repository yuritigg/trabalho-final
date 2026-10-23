<?php
echo $_POST['nome_plano'];
?>

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