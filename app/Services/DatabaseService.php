<?php

namespace App\Services;


use mysqli;


class DatabaseService
{
    private ?mysqli $databaseConnection = null;

    /**
     * DatabaseService constructor.
     */
    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $this->databaseConnection = new mysqli($host, $user, $password, $dbname, $port);
        if ($this->databaseConnection->connect_error) {
            exit($this->databaseConnection->connect_error);
        }
    }

    public function __destruct()
    {
        $this->databaseConnection->close();
    }

    public function sync(){
        $this->tableExist();
        $query = "SELECT SongID, SongName, SongAuthor FROM Songs;";
        $result = $this->databaseConnection->query($query);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        file_put_contents(dirname(__DIR__) . '/../data/songs.json', json_encode($data));
    }

    /**
     * @param string $tableName
     * @return bool
     */
    private function tableExist(string $tableName): bool
    {
        if ($tableName === 'Songs') {
                $query =
                    "CREATE TABLE IF NOT EXISTS Songs (
                    SongID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    SongName VARCHAR(255),
                    SongAuthor VARCHAR(255),
                    Song MEDIUMTEXT,
                    Capo INT,
                    MadeBy VARCHAR(255),
                    Revision JSON
                );
            ";
        } elseif ($tableName === 'Persons') {
            $query =
                "CREATE TABLE IF NOT EXISTS Persons (
                    PersonID INT,
                    FavouriteSongs JSON,
                    IsAdmin INT
                );
            ";
        } else {
            return false;
        }
        $this->databaseConnection->query($query);
    }

    /**
     * @param int $SongID
     * @return array|null
     */
    public function getSong(int $SongID): ?array
    {
        $query = "SELECT * FROM Songs WHERE SongID = $SongID;";
        $result = $this->databaseConnection->query($query);
        return $result->fetch_assoc();
    }

    /**
     * @param int $PersonID
     * @return array|null
     */
    public function getPerson(int $PersonID): ?array
    {
        $query = "SELECT * FROM Persons WHERE PersonID = $PersonID;";
        $result = $this->databaseConnection->query($query);
        return $result->fetch_assoc();
    }
}