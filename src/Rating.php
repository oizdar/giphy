<?php
namespace main;

class Rating
{
    /**
     * Database instance
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        $dsn = 'mysql:dbname='. DB_DATABASE.';host='.DB_HOST;
        $this->db = new \PDO($dsn, DB_USERNAME, DB_PASSWORD);
    }

    public function getAll()
    {
        $query='select * from `giphy`';
        $statement = $this->db->query($query);
        return $statement->fetchAll();
    }

    public function getGifRating(string $id)
    {
        $query = 'select * from `giphy` where id=\'' . $id .'\';';
        $statement = $this->db->query($query);

        return ($statement) ? $statement->fetch() : false;
    }

    public function addLike($id)
    {

        return ['method' => 'like', 'value' => $this->add($id, 'like')];
    }

    public function addDislike($id)
    {
        return ['method' => 'dislike', 'value' => $this->add($id, 'dislike')];
    }

    private function add($id, $column)
    {
        $row = $this->selectColumn($id, $column);
        if (!$row) {
            $value = 1;
            $query = 'INSERT INTO `giphy` (`id`, `' . $column . '`) VALUES (\''.$id.'\', 1);';
        } else {
            $value = ($row[$column]+1);
            $query = 'UPDATE `giphy` SET `' . $column . '`=' . $value . ' WHERE id=\'' . $id .'\';';
        }

        return ($this->db->query($query)) ? $value : false;
    }

    private function selectColumn($id, $column)
    {
        $query = 'select `' . $column . '` from `giphy` where id=\''.$id.'\';';
        $statement = $this->db->query($query);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function removeLike($id)
    {
        return ['method' => 'like', 'value' => $this->remove($id, 'like')];
    }

    public function removeDislike($id)
    {
        return ['method' => 'dislike', 'value' => $this->remove($id, 'dislike')];
    }

    public function remove($id, $column)
    {
        $row = $this->selectColumn($id, $column);
        if (!$row) {
            $response = false;
        } else {
            $value = ($row[$column]-1);
            $query = 'UPDATE `giphy` SET `' . $column . '`=' . $value . ' WHERE id=\'' . $id .'\';';
            $response = ($this->db->query($query)) ? $value : false;
        }
        return $response;
    }
}
