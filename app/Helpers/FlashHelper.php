<?php

/**
 * Classe responsável por gerenciar mensagens "flash" na sessão.
 *
 * Flash messages são mensagens temporárias que permanecem disponíveis
 * apenas para a próxima requisição HTTP. Após serem recuperadas,
 * são automaticamente removidas da sessão.
 *
 * São amplamente utilizadas para exibir mensagens de sucesso,
 * erro ou alerta após redirecionamentos (redirect).
 *
 * Exemplo de uso:
 *
 * ```php
 * FlashHelper::set('success', 'Usuário criado com sucesso!');
 * header('Location: /users');
 * exit;
 * ```
 *
 * Na view:
 *
 * ```php
 * if (FlashHelper::has('success')) {
 *     echo FlashHelper::get('success');
 * }
 * ```
 */
class FlashHelper
{
  /**
   * Armazena uma mensagem flash na sessão.
   *
   * @param string $key Chave da mensagem (ex: success, error, warning)
   * @param mixed $value Conteúdo da mensagem a ser armazenado
   * @return void
   */
  public static function set(string $key, mixed $value): void
  {
    $_SESSION[$key] = $value;
  }

  /**
   * Recupera uma mensagem flash da sessão e a remove imediatamente.
   *
   * Esse comportamento garante que a mensagem seja exibida apenas uma vez,
   * seguindo o padrão de flash messages.
   *
   * @param string $key Chave da mensagem
   * @return mixed Retorna o valor armazenado ou null se não existir
   */
  public static function get(string $key): mixed
  {
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $value;
  }

  /**
   * Verifica se existe uma mensagem flash armazenada na sessão.
   *
   * @param string $key Chave da mensagem
   * @return bool Retorna true se existir mensagem, caso contrário false
   */
  public static function has(string $key): bool
  {
    return isset($_SESSION[$key]);
  }
}
