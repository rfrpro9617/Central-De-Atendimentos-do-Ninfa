<?php

require_once __DIR__ . '/Env.php';

loadEnv(__DIR__ . '/../.env_');

class Database
{
  private static ?mysqli $connection = null;

  public static function getConnection(): mysqli
  {
    if (self::$connection === null) {

      self::$connection = new mysqli(
        env('DB_HOST'),
        env('DB_USERNAME'),
        env('DB_PASSWORD'),
        env('DB_DATABASE')
      );

      if (self::$connection->connect_error) {
        throw new RuntimeException('Erro ao conectar no banco.');
        // TODO: Logar o erro de conexão
      }

      self::$connection->set_charset(
        env('DB_CHARSET')
      );
    }

    return self::$connection;
  }
}
