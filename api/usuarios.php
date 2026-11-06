<?php
// CRUD simples de clientes
// GET    /clientes.php          -> lista todos
// GET    /clientes.php?id=1     -> busca um
// POST   /clientes.php          -> cadastra (nome, email, telefone)
// PUT    /clientes.php?id=1     -> atualiza (nome, email, telefone)
//
// Todas as rotas exigem o cabecalho:  Authorization: Bearer <token>

require '../usuarioDAO.php';

// Token fixo da API (o mesmo para todos os clientes da API)
define('TOKEN_API', 'meu-token-secreto-123');

header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Libera o acesso a partir de paginas HTML de outros enderecos (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Antes de um POST/PUT o navegador manda um OPTIONS de sondagem (preflight),
// e essa sondagem nao leva o token junto -- por isso ela responde antes da autenticacao
if ($metodo === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Le os dados enviados no corpo da requisicao (JSON ou formulario)
function lerDados()
{
    $corpo = file_get_contents('php://input');
    $dados = json_decode($corpo, true);

    if (!is_array($dados)) {
        parse_str($corpo, $dados);   // aceita formato nome=Joao&email=...
    }

    return [
        'nome'     => trim($dados['nome_usuario'] ?? ''),
        'email'    => trim($dados['email_usuario'] ?? ''),
        'telefone' => trim($dados['telefone_usuario'] ?? ''),
    ];
}

// Devolve uma resposta em JSON e encerra o script
function responder($codigo, $conteudo)
{
    http_response_code($codigo);
    echo json_encode($conteudo, JSON_UNESCAPED_UNICODE);
    exit;
}

// Pega o cabecalho Authorization enviado na requisicao
function lerTokenEnviado()
{
    $cabecalho = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($cabecalho === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $nome => $valor) {
            if (strtolower($nome) === 'authorization') {
                $cabecalho = $valor;
                break;
            }
        }
    }

    if (preg_match('/Bearer\s+(.+)/i', $cabecalho, $partes)) {
        return trim($partes[1]);
    }

    return '';
}

// Bloqueia a requisicao se o token nao for o esperado
function autenticar()
{
    $token = lerTokenEnviado();

    if ($token === '') {
        responder(401, ['erro' => 'Token nao informado']);
    }

    if (!hash_equals(TOKEN_API, $token)) {
        responder(401, ['erro' => 'Token invalido']);
    }
}

autenticar();

              $usuarioDAO = new UsuarioDAO();
switch ($metodo) {

    // ---------- LISTAR / BUSCAR ----------
    case 'GET':
        if ($id) {
             responder(200, $usuarioDAO->buscarPorId($id));
        }

        $clientes = $pdo->query('SELECT * FROM cliente ORDER BY id')->fetchAll();
        responder(200, $clientes);

    // ---------- CADASTRAR ----------
    case 'POST':
        $dados = lerDados();

        if ($dados['nome_usuario'] === '' || $dados['email_usuario'] === '' || $dados['telefone_usuario'] === '') {
            responder(400, ['erro' => 'Informe nome, email e telefone']);
        }

        $sql = $pdo->prepare('INSERT INTO usuario (nome_usuario, email_usuario, cpf_usuario, data_nasc_usuario, endereco_usuario, telefone_usuario, genero_usuario, id_plano, altura_usuario, peso_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->execute([$nome, $dados[email], $cpf, $datanasc, $endereco, $telefone, $genero, $plano, $altura, $peso]);

        responder(201, [
            'mensagem' => 'Cliente cadastrado com sucesso',
            'id'       => (int) $pdo->lastInsertId(),
        ]);

    // ---------- ATUALIZAR ----------
    case 'PUT':
        if (!$id) {
            responder(400, ['erro' => 'Informe o id do cliente na URL']);
        }

        $dados = lerDados();

        if ($dados['nome'] === '' || $dados['email'] === '' || $dados['telefone'] === '') {
            responder(400, ['erro' => 'Informe nome, email e telefone']);
        }

        $sql = $pdo->prepare('UPDATE cliente SET nome = ?, email = ?, telefone = ? WHERE id = ?');
        $sql->execute([$dados['nome'], $dados['email'], $dados['telefone'], $id]);

        if ($sql->rowCount() === 0) {
            $existe = $pdo->prepare('SELECT id FROM cliente WHERE id = ?');
            $existe->execute([$id]);

            if (!$existe->fetch()) {
                responder(404, ['erro' => 'Cliente nao encontrado']);
            }
        }

        responder(200, ['mensagem' => 'Cliente atualizado com sucesso']);

    default:
        responder(405, ['erro' => 'Metodo nao permitido']);
}