<?php

require 'usuarioDAO.php';

function api($acao, $dados = [])
{
    $usuarioDAO = new UsuarioDAO();

    switch ($acao) {
        case 'listar':
            return $usuarioDAO->listar();

        case 'listarPlanos':
            return $usuarioDAO->listarPlanos();

        case 'buscarPorId':
            return $usuarioDAO->buscarPorId($dados['id']);

        // case 'inserir':
        //     return $usuarioDAO->inserir(
        //         $dados['nome_usuario'],
        //         $dados['email_usuario'],
        //         $dados['cpf_usuario'],
        //         $dados['data_nasc_usuario'],
        //         $dados['endereco_usuario'],
        //         $dados['telefone_usuario'],
        //         $dados['genero_usuario'],
        //         $dados['plano_usuario'],
        //         $dados['altura_usuario'],
        //         $dados['peso_usuario']
        //     );

        case 'atualizar':
            return $usuarioDAO->atualizar(
                $dados['id_usuario'],
                $dados['nome_usuario'],
                $dados['email_usuario'],
                $dados['cpf_usuario'],
                $dados['data_nasc_usuario'],
                $dados['endereco_usuario'],
                $dados['telefone_usuario'],
                $dados['genero_usuario'],
                $dados['plano_usuario'],
                $dados['altura_usuario'],
                $dados['peso_usuario']
            );

        case 'excluir':
            return $usuarioDAO->excluir($dados['id']);

        default:
            return json_encode(['erro' => 'Acao invalida: ' . $acao]);
    }
}