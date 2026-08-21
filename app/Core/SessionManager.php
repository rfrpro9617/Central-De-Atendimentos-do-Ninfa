<?php

class SessionManager
{
  private const SESSION_TIMEOUT = 1800; // 30 minutos

  public static function start(): void
  {
    // Nenhuma sessão ativa, iniciar uma nova
    if (session_status() === PHP_SESSION_NONE) {
      // Antes de mostrar uma página armazenada em cache, verifique com o servidor se ela ainda é válida
      session_cache_limiter('must-revalidate');
      session_name('INTRANETNEWSESSID');
      ini_set('session.cookie_httponly', '1');
      // Só envia cookies em  HTTPS
      ini_set('session.cookie_secure', '1');
      // Impede que o PHP aceite IDs invalidos, aumentando a segurança contra ataques de fixação de sessão
      ini_set('session.use_strict_mode', '1');
      session_start();
    }

    if (
      isset($_SESSION['last_activity']) &&
      (time() - $_SESSION['last_activity']) > self::SESSION_TIMEOUT
    ) {
      self::destroy();
      session_start();
    }

    $_SESSION['last_activity'] = time();
  }

  public static function get(string $key, mixed $default = null): mixed
  {
    return $_SESSION[$key] ?? $default;
  }

  public static function set(string $key, mixed $value): void
  {
    $_SESSION[$key] = $value;
  }

  public static function remove(string $key): void
  {
    unset($_SESSION[$key]);
  }

  public static function destroy(): void
  {
    // Verifica se existe uma sessão ativa antes de tentar destruí-la
    if (session_status() !== PHP_SESSION_ACTIVE) {
      return;
    }

    // Limpa os dados da sessão
    $_SESSION = [];

    // Se a sessão utiliza cookies, remove o cookie de sessão
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }

    session_destroy();
  }

  public static function has(string $key): bool
  {
    return isset($_SESSION[$key]);
  }

  public static function flash(string $key, mixed $value): void
  {
    self::set($key, $value);
  }

  public static function pull(string $key, mixed $default = null): mixed
  {
    $value = self::get($key, $default);
    self::remove($key);
    return $value;
  }
}
