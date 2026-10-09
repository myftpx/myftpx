<?php
namespace App\Core;

class Database
{
    protected static ?\PDO $instance = null;

    public static function instance(): \PDO
    {
        if (self::$instance === null) {
            $cfg = config();
            $driver = $cfg['db_driver'] ?? 'sqlite';

            if ($driver === 'mysql') {
                $host = $cfg['db_host'] ?? '127.0.0.1';
                $port = $cfg['db_port'] ?? '3306';
                $name = $cfg['db_name'] ?? '';
                $user = $cfg['db_user'] ?? '';
                $pass = $cfg['db_pass'] ?? '';
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                self::$instance = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            } else {
                $path = $cfg['db_path'] ?? (dirname(__DIR__, 2) . '/storage/database.sqlite');
                $dir = dirname($path);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                self::$instance = new \PDO('sqlite:' . $path, null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
                self::$instance->exec('PRAGMA foreign_keys = ON');
            }
        }
        return self::$instance;
    }

    public static function isInstalled(): bool
    {
        $path = dirname(__DIR__, 2) . '/config.php';
        if (!is_file($path)) return false;
        try {
            $db = self::instance();
            $db->query('SELECT 1 FROM settings LIMIT 1');
            return true;
        } catch (\Throwable $t) {
            return false;
        }
    }
}
