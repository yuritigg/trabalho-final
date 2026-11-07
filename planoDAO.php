<?php
// DAO = Data Access Object
// Esta classe concentra TODO o SQL da tabela "planos".
// Os metodos devolvem arrays/numeros do PHP; quem transforma em JSON e a API.

require_once __DIR__ . '/conexao.php';

class PlanoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = conectar();
    }

    // C - Create: cadastra um plano e devolve o id gerado
    public function inserir($nome, $valor)
    {
        $sql = $this->pdo->prepare('INSERT INTO planos (nome_plano, valor_plano) VALUES (?, ?)');
        $sql->execute([$nome, $valor]);

        return (int) $this->pdo->lastInsertId();
    }

    // R - Read: devolve todos os planos
    public function listar()
    {
        $sql = $this->pdo->query('SELECT id_plano, nome_plano, valor_plano FROM planos ORDER BY id_plano');

        return $sql->fetchAll();
    }

    // R - Read: devolve um plano pelo id (ou false se nao existir)
    public function buscarPorId($id)
    {
        $sql = $this->pdo->prepare('SELECT id_plano, nome_plano, valor_plano FROM planos WHERE id_plano = ?');
        $sql->execute([$id]);

        return $sql->fetch();
    }

    // Conta quantos usuarios estao neste plano (um plano em uso nao pode ser excluido)
    public function contarUsuarios($id)
    {
        $sql = $this->pdo->prepare('SELECT COUNT(*) FROM usuario WHERE id_plano = ?');
        $sql->execute([$id]);

        return (int) $sql->fetchColumn();
    }

    // U - Update: altera os dados de um plano
    public function atualizar($id, $nome, $valor)
    {
        $sql = $this->pdo->prepare('UPDATE planos SET nome_plano = ?, valor_plano = ? WHERE id_plano = ?');

        return $sql->execute([$nome, $valor, $id]);
    }

    // D - Delete: remove um plano e devolve quantas linhas foram apagadas (0 ou 1)
    public function excluir($id)
    {
        $sql = $this->pdo->prepare('DELETE FROM planos WHERE id_plano = ?');
        $sql->execute([$id]);

        return $sql->rowCount();
    }
}
