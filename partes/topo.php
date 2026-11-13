<?php
// Cabecalho comum a todas as paginas.
// Antes do include, defina: $titulo (texto da aba) e $pagina (item ativo do menu).
$itensMenu = [
    'listar'    => ['Alunos', 'listar.php'],
    'cadastrar' => ['Novo aluno', 'cadastrar.php'],
    'editar'    => ['Editar aluno', 'editar.php'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Sistema interno: nao aparecer no Google -->
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($titulo) ?> · Academia Cooperberg</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js"></script>
</head>
<body>
    <header class="topo">
        <a class="topo__titulo" href="listar.php">ACADEMIA COOPERBERG</a>

        <nav class="menu" aria-label="Menu principal">
            <?php foreach ($itensMenu as $chave => [$texto, $link]): ?>
                <a href="<?= $link ?>"
                   class="menu__item<?= $pagina === $chave ? ' menu__item--ativo' : '' ?>"
                   <?= $pagina === $chave ? 'aria-current="page"' : '' ?>><?= $texto ?></a>
            <?php endforeach; ?>
        </nav>
    </header>
