<?php

/**
 * Classe responsável por registrar logs de aplicação no banco de dados.
 *
 * O Logger centraliza o registro de eventos importantes do sistema,
 * como erros, informações e eventos de auditoria.
 *
 * Ele utiliza uma conexão MySQLi estática, que deve ser configurada
 * antes do uso através do método setConnection().
 *
 * Exemplo de uso:
 *
 * ```php
 * Logger::setConnection($db);
 *
 * Logger::info('Usuário acessou o sistema');
 * Logger::error('Falha ao processar pagamento');
 * ```
 */
class Logger
{
  /**
   * Conexão estática com o banco de dados.
   *
   * @var mysqli|null
   */
  private static ?mysqli $db = null;

  /**
   * Define a conexão com o banco de dados que será usada pelo logger.
   *
   * Deve ser chamada antes de qualquer operação de log.
   *
   * @param mysqli $db Conexão ativa com o MySQL.
   * @return void
   */
  public static function setConnection(mysqli $db): void
  {
    self::$db = $db;
  }

  /**
   * Registra uma mensagem de log no banco de dados.
   *
   * O nível do log (level) é armazenado em maiúsculo automaticamente,
   * e a mensagem é limpa com trim().
   *
   * Em caso de falha na conexão, preparação ou execução da query,
   * uma RuntimeException será lançada.
   *
   * @param string $level Nível do log (ex: info, error, warning, debug)
   * @param string $message Mensagem a ser registrada
   *
   * @throws RuntimeException Quando:
   * - Conexão não foi configurada
   * - Falha ao preparar a query
   * - Falha ao executar a query
   *
   * @return void
   */
  public static function write(string $level, string $message): void
  {
    if (self::$db === null) {
      throw new RuntimeException(
        'Conexão de banco de dados não configurada para o Logger.'
      );
    }

    $level = strtoupper($level);
    $message = trim($message);

    $query = "INSERT INTO agronomia2.atendimentos_logs (level, message) VALUES (?, ?)";
    $stmt = self::$db->prepare($query);

    if (!$stmt) {
      throw new RuntimeException(
        'Falha ao preparar a consulta de log: ' . self::$db->error
      );
    }

    $stmt->bind_param('ss', $level, $message);

    if (!$stmt->execute()) {
      throw new RuntimeException(
        'Falha ao escrever o log no banco de dados: ' . $stmt->error
      );
    }

    $stmt->close();
  }

  /**
   * Registra um log de nível "error".
   *
   * Usado para registrar falhas, exceções e comportamentos inesperados.
   *
   * @param string $message Mensagem de erro
   * @return void
   */
  public static function error(string $message): void
  {
    self::write('error', $message);
  }

  /**
   * Registra um log de nível "info".
   *
   * Usado para registrar informações gerais de execução do sistema,
   * como acessos, fluxos e eventos normais.
   *
   * @param string $message Mensagem informativa
   * @return void
   */
  public static function info(string $message): void
  {
    self::write('info', $message);
  }
}
