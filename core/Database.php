<?php

namespace app\core;


class Database
{
    public \PDO $pdo;

    public function __construct(array $config)
    {
        $dns = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';

        $this->pdo = new \PDO($dns, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }


    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();


        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR.'/migrations');
        
        $toApplyMigrations = array_diff($files, $appliedMigrations);
        
        foreach($toApplyMigrations as $migration)
        {
            if($migration === '.' || $migration === '..'){
                continue;
            }

            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            $migrationClass = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $migrationClass();
            $this->log("Trying To Apply migration => $migration"); 
            $instance->up();
            $this->log("Applied migration => $migration. Done");
            $newMigrations[] = $migration; 
        }

        if(!empty($newMigrations)){
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All migrations are applied");
        }
    }

    public function createMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )  ENGINE=INNODB;");
    }

    public function getAppliedMigrations()
    {
        $stmt = $this->pdo->prepare("SELECT migration FROM migrations");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveMigrations(array $migrations)
    {
        
        $str = implode(",",  array_map(fn($m) => "('$m')", $migrations));
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $str");
        $stmt->execute();
    }

    public function prapare($sql)
    {
        return Application::$app->DB->pdo->prepare($sql);

        
    }


    protected function log($msg)
    {
        echo "[".date('Y-m-d H:i:s')."] - ".$msg.PHP_EOL;
    }

}
