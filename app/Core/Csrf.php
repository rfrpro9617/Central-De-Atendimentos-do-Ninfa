<?php

/**
 * Classe responsável pela proteção CSRF (Cross-Site Request Forgery).
 *
 * O token CSRF é armazenado na sessão do usuário e deve ser enviado
 * junto aos formulários da aplicação para validar se a requisição
 * foi originada pelo próprio sistema.
 *
 * Fluxo:
 *
 * 1. O formulário chama Csrf::token()
 * 2. O token é salvo na sessão e enviado no HTML
 * 3. O formulário envia o token via POST
 * 4. O backend chama Csrf::validate()
 * 5. O token é comparado com o valor salvo na sessão
 *
 * Exemplo de uso no formulário:
 *
 * <input
 *   type="hidden"
 *   name="_token"
 *   value="<?= Csrf::token() ?>"
 * >
 *
 * Exemplo de validação:
 *
 * if (!Csrf::validate($_POST['_token'] ?? '')) {
 *     die('CSRF inválido');
 * }
 */
class Csrf
{
  /**
   * Nome da chave utilizada para armazenar o token na sessão.
   */
  private const SESSION_KEY = 'csrf_token';

  /**
   * Retorna o token CSRF atual.
   *
   * Caso ainda não exista token na sessão,
   * um novo token é gerado automaticamente.
   *
   * O token é gerado utilizando:
   * - random_bytes(): geração criptograficamente segura
   * - bin2hex(): conversão para hexadecimal
   *
   * @return string Token CSRF.
   */
  public static function token(): string
  {
    if (!SessionManager::has(self::SESSION_KEY)) {
      SessionManager::set(
        self::SESSION_KEY,
        bin2hex(random_bytes(32))
      );
    }

    return SessionManager::get(self::SESSION_KEY);
  }

  /**
   * Valida o token CSRF enviado pela requisição.
   *
   * A validação compara:
   * - token enviado pelo formulário
   * - token armazenado na sessão
   *
   * Utiliza hash_equals() para evitar ataques de timing attack.
   *
   * Após validação bem-sucedida, um novo token é gerado
   * automaticamente para aumentar a segurança.
   *
   * @param string $token Token recebido via POST.
   *
   * @return bool
   * - true  => token válido
   * - false => token inválido
   */
  public static function validate(string $token): bool
  {
    $current = SessionManager::get(self::SESSION_KEY, '');

    if (
      !is_string($token) ||
      $token === '' ||
      !hash_equals($current, $token)
    ) {
      return false;
    }

    // Rotaciona o token após validação válida
    // Previne sua reutilização em ataques subsequentes
    SessionManager::set(
      self::SESSION_KEY,
      bin2hex(random_bytes(32))
    );

    return true;
  }
}
