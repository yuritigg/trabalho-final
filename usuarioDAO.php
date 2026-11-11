<?php
// DAO = Data Access Object
// Esta classe concentra TODO o SQL da tabela "cliente".
// Quem usa o DAO nao precisa saber SQL: so chama os metodos.

require_once 'conexao.php';

class UsuarioDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = conectar();
    }

    // C - Create: cadastra um cliente e devolve o id gerado
    public function inserir($nome, $email, $cpf, $datanasc, $endereco, $telefone, $genero, $plano, $altura, $peso)
    {
        $sql = $this->pdo->prepare('INSERT INTO usuario (nome_usuario, email_usuario, cpf_usuario, data_nasc_usuario, endereco_usuario, telefone_usuario, genero_usuario, id_plano, altura_usuario, peso_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->execute([$nome, $email, $cpf, $datanasc, $endereco, $telefone, $genero, $plano, $altura, $peso]);

        return json_encode($this->pdo->lastInsertId());
    }

    // R - Read: devolve todos os clientes
    public function listar()
    {
        $sql = $this->pdo->query('SELECT usuario.nome_usuario, planos.nome_plano, usuario.id_usuario FROM planos, usuario where planos.id_plano = usuario.id_plano;');

        return $sql->fetchAll();
    }

    public function listarPlanos()
    {
        $sql = $this->pdo->query('SELECT id_plano, nome_plano, valor_plano FROM planos');

        return $sql->fetchAll();
    }

    // R - Read: devolve um usuario pelo id (ou false se nao existir)
    public function buscarPorId($id)
    {
        $sql = $this->pdo->prepare('SELECT usuario.*, planos.nome_plano FROM usuario INNER JOIN planos ON planos.id_plano = usuario.id_plano WHERE usuario.id_usuario = ?');
        $sql->execute([$id]);

        return $sql->fetch();
    }

    // Confere se o plano existe (usado para validar o cadastro/edicao)
    public function planoExiste($idPlano)
    {
        $sql = $this->pdo->prepare('SELECT 1 FROM planos WHERE id_plano = ?');
        $sql->execute([$idPlano]);

        return (bool) $sql->fetchColumn();
    }

    // U - Update: altera os dados de um cliente
    public function atualizar($id, $nome, $email, $cpf, $datanasc, $endereco, $telefone, $genero, $plano, $altura, $peso)
    {
        $sql = $this->pdo->prepare('UPDATE usuario SET nome_usuario = ?, email_usuario = ?, cpf_usuario = ?, data_nasc_usuario = ?, endereco_usuario = ?, telefone_usuario = ?, genero_usuario = ?, id_plano = ?, altura_usuario = ?, peso_usuario = ? WHERE id_usuario = ?');

        return json_encode($sql->execute([$nome, $email, $cpf, $datanasc, $endereco, $telefone, $genero, $plano, $altura, $peso, $id]));
    }

    // D - Delete: remove um cliente
    public function excluir($id)
    {
        $sql = $this->pdo->prepare('DELETE FROM usuario WHERE id_usuario = ?');

        return json_encode($sql->execute([$id]));
    }
}
