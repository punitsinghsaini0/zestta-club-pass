<?php
class CI_DB
{
    protected $pdo;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    protected function connect()
    {
        if (empty($this->config['dsn'])) {
            $driver = $this->config['dbdriver'] ?? 'mysql';
            $host = $this->config['hostname'] ?? 'localhost';
            $db = $this->config['database'] ?? '';
            $charset = $this->config['char_set'] ?? 'utf8mb4';
            $this->config['dsn'] = sprintf('%s:host=%s;dbname=%s;charset=%s', $driver, $host, $db, $charset);
        }

        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $options = $this->config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $this->pdo = new PDO($this->config['dsn'], $username, $password, $options);
    }

    public function query($sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function insert($table, array $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', $columns), implode(',', $placeholders));
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $col => $value) {
            $stmt->bindValue(':' . $col, $value);
        }
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function update($table, array $data, $where, array $params = [])
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = $column . '=:' . $column;
        }
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, implode(',', $set), $where);
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $col => $value) {
            $stmt->bindValue(':' . $col, $value);
        }
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value);
        }
        return $stmt->execute();
    }

    public function delete($table, $where, array $params = [])
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function get_connection()
    {
        return $this->pdo;
    }
}

function DB(array $params)
{
    return new CI_DB($params);
}

