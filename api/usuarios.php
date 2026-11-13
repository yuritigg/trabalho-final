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

// Nomes dos campos como o funcionario ve na tela
const NOMES_CAMPOS = [
    'nome_usuario'      => 'Nome completo',
    'email_usuario'     => 'E-mail',
    'cpf_usuario'       => 'CPF',
    'data_nasc_usuario' => 'Data de nascimento',
    'endereco_usuario'  => 'Endereço',
    'telefone_usuario'  => 'Telefone',
    'genero_usuario'    => 'Gênero',
    'id_plano'          => 'Plano',
    'altura_usuario'    => 'Altura',
    'peso_usuario'      => 'Peso',
];

// Confere os digitos verificadores do CPF
function cpfValido($cpf)
{
    if (!preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    for ($posicao = 9; $posicao < 11; $posicao++) {
        $soma = 0;
        for ($i = 0; $i < $posicao; $i++) {
            $soma += $cpf[$i] * (($posicao + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }

    return true;
}

function validarDados($dados, $usuarioDAO, $idAtual = null)
{
    foreach ($dados as $campo => $valor) {
        if ($valor === '') {
            responder(400, ['erro' => 'Preencha o campo ' . NOMES_CAMPOS[$campo] . '.']);
        }
    }

    if (!filter_var($dados['email_usuario'], FILTER_VALIDATE_EMAIL)) {
        responder(400, ['erro' => 'E-mail inválido. Confira se tem @ e domínio (ex.: nome@gmail.com).']);
    }

    if (strlen($dados['cpf_usuario']) !== 11) {
        responder(400, ['erro' => 'CPF incompleto: digite os 11 números.']);
    }

    if (!cpfValido($dados['cpf_usuario'])) {
        responder(400, ['erro' => 'CPF inválido. Confira os números digitados.']);
    }

    if ($usuarioDAO->cpfEmUso($dados['cpf_usuario'], $idAtual)) {
        responder(409, ['erro' => 'Já existe um aluno cadastrado com este CPF.']);
    }

    if (strlen($dados['telefone_usuario']) < 10 || strlen($dados['telefone_usuario']) > 11) {
        responder(400, ['erro' => 'Telefone inválido: digite o DDD e o número (10 ou 11 números).']);
    }

    $data = DateTime::createFromFormat('!Y-m-d', $dados['data_nasc_usuario']);
    if (!$data || $data->format('Y-m-d') !== $dados['data_nasc_usuario']) {
        responder(400, ['erro' => 'Data de nascimento inválida.']);
    }

    if ($data > new DateTime('today')) {
        responder(400, ['erro' => 'A data de nascimento não pode ser no futuro.']);
    }

    if ($data < new DateTime('1900-01-01')) {
        responder(400, ['erro' => 'Confira o ano da data de nascimento.']);
    }

    if (!in_array($dados['genero_usuario'], ['masculino', 'feminino', 'outro'])) {
        responder(400, ['erro' => 'Selecione o gênero.']);
    }

    if (!ctype_digit($dados['altura_usuario']) || (int) $dados['altura_usuario'] <= 0) {
        responder(400, ['erro' => 'Altura inválida: use centímetros, sem vírgula (ex.: 175).']);
    }

    if (!ctype_digit($dados['peso_usuario']) || (int) $dados['peso_usuario'] <= 0) {
        responder(400, ['erro' => 'Peso inválido: use quilos, sem vírgula (ex.: 70).']);
    }

    if (!ctype_digit($dados['id_plano']) || !$usuarioDAO->planoExiste($dados['id_plano'])) {
        responder(400, ['erro' => 'Selecione um plano válido.']);
    }
}

// Bloqueia a requisicao se o token nao for o esperado
function autenticar()
{
    $token = lerTokenEnviado();

    if ($token === '') {
        responder(401, ['erro' => 'Acesso não autorizado.']);
    }

    if (!hash_equals(TOKEN_API, $token)) {
        responder(401, ['erro' => 'Acesso não autorizado.']);
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
                responder(404, ['erro' => 'Aluno não encontrado.']);
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
            'mensagem' => 'Aluno cadastrado com sucesso.',
            'id'       => $novoId,
        ]);

    // ---------- ATUALIZAR ----------
    case 'PUT':
        if (!$id) {
            responder(400, ['erro' => 'Informe o ID do aluno.']);
        }

        if (!$usuarioDAO->buscarPorId($id)) {
            responder(404, ['erro' => 'Aluno não encontrado.']);
        }

        $dados = lerDados();
        validarDados($dados, $usuarioDAO, $id);

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

        responder(200, ['mensagem' => 'Alterações salvas com sucesso.']);
    
    case 'DELETE':
        if (!$id) {
            responder(400, ['erro' => 'Informe o ID do aluno.']);
        }

        if ($usuarioDAO->excluir($id) === 0) {
            responder(404, ['erro' => 'Aluno não encontrado.']);
        }

        responder(200, ['mensagem' => 'Aluno excluído com sucesso.']);
    
    default:
        responder(405, ['erro' => 'Método não permitido.']);
}