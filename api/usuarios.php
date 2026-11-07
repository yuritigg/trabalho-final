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
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
        parse_str($corpo, $dados);   // aceita formato nome_usuario=Joao&email_usuario=...
    }

    return [
        'nome_usuario'      => trim($dados['nome_usuario'] ?? ''),
        'email_usuario'     => trim($dados['email_usuario'] ?? ''),
        'cpf_usuario'       => trim($dados['cpf_usuario'] ?? ''),      
        'data_nasc_usuario' => trim($dados['data_nasc_usuario'] ?? ''),
        'endereco_usuario'  => trim($dados['endereco_usuario'] ?? ''),
        'telefone_usuario'  => trim($dados['telefone_usuario'] ?? ''), 
        'genero_usuario'    => trim($dados['genero_usuario'] ?? ''),
        'id_plano'          => trim($dados['id_plano'] ?? ''),
        'altura_usuario'    => trim($dados['altura_usuario'] ?? ''),
        'peso_usuario'      => trim($dados['peso_usuario'] ?? ''),
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

function validarDados($dados, $usuarioDAO)
{
    foreach ($dados as $campo => $valor) {
        if ($valor === '') {
            responder(400, ['erro' => "O campo $campo e obrigatorio"]);
        }
    }

    if (!filter_var($dados['email_usuario'], FILTER_VALIDATE_EMAIL)) {
        responder(400, ['erro' => 'Email invalido']);
    }

    if (strlen($dados['cpf_usuario']) !== 11) {
        responder(400, ['erro' => 'O CPF deve ter 11 numeros']);
    }

    if (strlen($dados['telefone_usuario']) < 10 || strlen($dados['telefone_usuario']) > 11) {
        responder(400, ['erro' => 'O telefone deve ter 10 ou 11 numeros (com DDD)']);
    }

    $data = DateTime::createFromFormat('Y-m-d', $dados['data_nasc_usuario']);
    if (!$data || $data->format('Y-m-d') !== $dados['data_nasc_usuario']) {
        responder(400, ['erro' => 'Data de nascimento invalida (use AAAA-MM-DD)']);
    }

    if (!in_array($dados['genero_usuario'], ['masculino', 'feminino', 'outro'])) {
        responder(400, ['erro' => 'Genero invalido']);
    }

    if (!ctype_digit($dados['altura_usuario']) || (int) $dados['altura_usuario'] <= 0) {
        responder(400, ['erro' => 'Altura invalida (em cm, numero inteiro)']);
    }

    if (!ctype_digit($dados['peso_usuario']) || (int) $dados['peso_usuario'] <= 0) {
        responder(400, ['erro' => 'Peso invalido (em kg, numero inteiro)']);
    }

    if (!ctype_digit($dados['id_plano']) || !$usuarioDAO->planoExiste($dados['id_plano'])) {
        responder(400, ['erro' => 'Plano invalido']);
    }
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
            $usuario = $usuarioDAO->buscarPorId($id);

            if (!$usuario) {
                responder(404, ['erro' => 'Usuario nao encontrado']);
            }

            responder(200, $usuario);
        }

        responder(200, $usuarioDAO->listar());

    // ---------- CADASTRAR ----------
    case 'POST':
        $dados = lerDados();
        validarDados($dados, $usuarioDAO);

        $novoId = $usuarioDAO->inserir(
            $dados['nome_usuario'],
            $dados['email_usuario'],
            $dados['cpf_usuario'],
            $dados['data_nasc_usuario'],
            $dados['endereco_usuario'],
            $dados['telefone_usuario'],
            $dados['genero_usuario'],
            $dados['id_plano'],
            $dados['altura_usuario'],
            $dados['peso_usuario']
        );

        responder(201, [
            'mensagem' => 'Usuario cadastrado com sucesso',
            'id'       => $novoId,
        ]);

    // ---------- ATUALIZAR ----------
    case 'PUT':
        if (!$id) {
            responder(400, ['erro' => 'Informe o id do usuario na URL']);
        }

        if (!$usuarioDAO->buscarPorId($id)) {
            responder(404, ['erro' => 'Usuario nao encontrado']);
        }

        $dados = lerDados();
        validarDados($dados, $usuarioDAO);

        $usuarioDAO->atualizar(
            $id,
            $dados['nome_usuario'],
            $dados['email_usuario'],
            $dados['cpf_usuario'],
            $dados['data_nasc_usuario'],
            $dados['endereco_usuario'],
            $dados['telefone_usuario'],
            $dados['genero_usuario'],
            $dados['id_plano'],
            $dados['altura_usuario'],
            $dados['peso_usuario']
        );

        responder(200, ['mensagem' => 'Usuario atualizado com sucesso']);
    
    case 'DELETE':
        if (!$id) {
            responder(400, ['erro' => 'Informe o id do usuario na URL']);
        }

        if ($usuarioDAO->excluir($id) === 0) {
            responder(404, ['erro' => 'Usuario nao encontrado']);
        }

        responder(200, ['mensagem' => 'Usuario excluido com sucesso']);
    
    default:
        responder(405, ['erro' => 'Metodo nao permitido']);
}