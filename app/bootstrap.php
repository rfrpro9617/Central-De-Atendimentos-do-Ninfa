<?php
define('APP_ROOT', realpath(__DIR__ . '/..'));

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Core/Autoloader.php';
require_once __DIR__ . '/Routes/TicketsRouter.php';

Autoloader::register();

$config = require __DIR__ . '/../config/app.php';
$db = Database::getConnection();
Logger::setConnection($db);

if ($config['app']['debug']) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 1);
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

registerErrorHandlers($config);

function registerErrorHandlers(array $config): void
{
  set_exception_handler(function (Throwable $e) use ($config): void {
    $message = sprintf(
      '[%s] %s in %s on line %s',
      get_class($e),
      $e->getMessage(),
      $e->getFile(),
      $e->getLine()
    );

    try {
      Logger::error($message);
    } catch (Throwable $loggerException) {
      error_log($message);
    }

    if ($config['app']['debug']) {
      echo '<pre>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
      echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
      http_response_code(500);
      echo 'Ocorreu um erro interno. Tente novamente mais tarde.';
    }

    exit;
  });

  set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
  });

  register_shutdown_function(function () use ($config): void {
    $error = error_get_last();
    if ($error !== null) {
      $exception = new ErrorException(
        $error['message'],
        0,
        $error['type'],
        $error['file'],
        $error['line']
      );
      call_user_func(function () use ($exception, $config): void {
        if (is_callable($handler = set_exception_handler(null))) {
          $handler($exception);
        }
      });
    }
  });
}
