<?php
// CRUD de planos da academia
// GET    api/planos.php          -> lista todos
// GET    api/planos.php?id=1     -> busca um
// POST   api/planos.php          -> cadastra
// PUT    api/planos.php?id=1     -> atualiza
// DELETE api/planos.php?id=1     -> exclui (so se nenhum usuario estiver no plano)
//
// POST e PUT recebem um JSON com os campos:
//   nome_plano, valor_plano
//
// Todas as rotas exigem o cabecalho:  Authorization: Bearer <token>

require '../planoDAO.php';

// Token fixo da API (o mesmo para todos os clientes da API)
define('TOKEN_API', 'meu-token-secreto-123');

header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Libera o acesso a partir de paginas HTML de outros enderecos (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Antes de um POST/PUT/DELETE o navegador manda um OPTIONS de sondagem (preflight),
// e essa sondagem nao leva o token junto -- por isso ela responde antes da autenticacao
if ($metodo === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Devolve uma resposta em JSON e encerra o script
function responder($codigo, $conteudo)
{
    http_response_code($codigo);
    echo json_encode($conteudo, JSON_UNESCAPED_UNICODE);
    exit;
}

// Se acontecer qualquer erro inesperado (ex.: banco fora do ar),
// responde em JSON em vez de mostrar a mensagem de erro do PHP
set_exception_handler(function ($erro) {
    responder(500, ['erro' => 'Erro interno no servidor']);
});

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

// Le os dados enviados no corpo da requisicao (JSON ou formulario)
function lerDados()
{
    $corpo = file_get_contents('php://input');
    $dados = json_decode($corpo, true);

    if (!is_array($dados)) {
        parse_str($corpo, $dados);   
    }

    return [
        'nome_plano'  => trim($dados['nome_plano'] ?? ''),
        'valor_plano' => str_replace(',', '.', trim($dados['valor_plano'] ?? '')),
    ];
}

// Confere os dados recebidos. Se algo estiver errado, responde 400 e encerra.
function validarDados($dados)
{
    if ($dados['nome_plano'] === '' || $dados['valor_plano'] === '') {
        responder(400, ['erro' => 'Informe nome_plano e valor_plano']);
    }

    // a coluna nome_plano e varchar(20)
    if (mb_strlen($dados['nome_plano']) > 20) {
        responder(400, ['erro' => 'O nome do plano pode ter no maximo 20 caracteres']);
    }

    if (!is_numeric($dados['valor_plano']) || $dados['valor_plano'] < 0) {
        responder(400, ['erro' => 'Valor do plano invalido']);
    }
}

$planoDAO = new PlanoDAO();

switch ($metodo) {

    // ---------- LISTAR / BUSCAR ----------
    case 'GET':
        if ($id) {
            $plano = $planoDAO->buscarPorId($id);

            if (!$plano) {
                responder(404, ['erro' => 'Plano nao encontrado']);
            }

            responder(200, $plano);
        }

        responder(200, $planoDAO->listar());

    // ---------- CADASTRAR ----------
    case 'POST':
        $dados = lerDados();
        validarDados($dados);

        $novoId = $planoDAO->inserir($dados['nome_plano'], $dados['valor_plano']);

        responder(201, [
            'mensagem' => 'Plano cadastrado com sucesso',
            'id'       => $novoId,
        ]);

    // ---------- ATUALIZAR ----------
    case 'PUT':
        if (!$id) {
            responder(400, ['erro' => 'Informe o id do plano na URL']);
        }

        if (!$planoDAO->buscarPorId($id)) {
            responder(404, ['erro' => 'Plano nao encontrado']);
        }

        $dados = lerDados();
        validarDados($dados);

        $planoDAO->atualizar($id, $dados['nome_plano'], $dados['valor_plano']);

        responder(200, ['mensagem' => 'Plano atualizado com sucesso']);

    // ---------- EXCLUIR ----------
    case 'DELETE':
        if (!$id) {
            responder(400, ['erro' => 'Informe o id do plano na URL']);
        }

        if (!$planoDAO->buscarPorId($id)) {
            responder(404, ['erro' => 'Plano nao encontrado']);
        }

        // o banco nao deixa apagar um plano que tem usuarios (chave estrangeira)
        if ($planoDAO->contarUsuarios($id) > 0) {
            responder(409, ['erro' => 'Este plano tem usuarios cadastrados e nao pode ser excluido']);
        }

        $planoDAO->excluir($id);

        responder(200, ['mensagem' => 'Plano excluido com sucesso']);

    default:
        responder(405, ['erro' => 'Metodo nao permitido']);
}
