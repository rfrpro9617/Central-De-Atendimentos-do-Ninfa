<?php

/**
 * =========================================================
 * AUTOLOADER
 * =========================================================
 *
 * Responsável por carregar automaticamente classes PHP
 * quando elas forem utilizadas na aplicação.
 *
 * Evita a necessidade de vários:
 *
 * require_once 'Arquivo.php';
 *
 * =========================================================
 * COMO FUNCIONA
 * =========================================================
 *
 * Quando uma classe é usada:
 *
 *   new UserService();
 *
 * ou:
 *
 *   UserController::class
 *
 * o PHP verifica se a classe já foi carregada.
 *
 * Caso NÃO tenha sido carregada,
 * o spl_autoload_register executa automaticamente
 * a função registrada abaixo.
 *
 * Essa função procura o arquivo da classe
 * em várias pastas da aplicação.
 *
 * =========================================================
 * EXEMPLO
 * =========================================================
 *
 * Classe utilizada:
 *
 *   new UserService();
 *
 * O autoloader tentará localizar:
 *
 *   /Services/UserService.php
 *
 * Se encontrar:
 *
 *   require_once é executado automaticamente.
 *
 * =========================================================
 * FLUXO INTERNO
 * =========================================================
 *
 * 1. Classe é utilizada
 * 2. PHP percebe que ela não existe
 * 3. spl_autoload_register é disparado
 * 4. O autoloader procura o arquivo
 * 5. Se encontrar:
 *      require_once arquivo
 * 6. Classe fica disponível
 *
 * =========================================================
 * COMO USAR
 * =========================================================
 *
 * Registrar o autoloader no bootstrap da aplicação:
 *
 *   require_once 'Core/Autoloader.php';
 *
 *   Autoloader::register();
 *
 * Depois disso:
 *
 * NÃO é mais necessário usar require_once
 * manualmente para classes da aplicação.
 *
 * =========================================================
 * ESTRUTURA ESPERADA
 * =========================================================
 *
 * /Controllers
 * /Services
 * /Repositories
 * /Helpers
 * /Validators
 * /Mail
 * /Constants
 * /DTO
 * /Models
 * /Core
 * /Middleware
 *
 * =========================================================
 * EXEMPLOS
 * =========================================================
 *
 * Classe:
 *
 *   UserService
 *
 * Arquivo esperado:
 *
 *   /Services/UserService.php
 *
 * ---------------------------------------------------------
 *
 * Classe:
 *
 *   AuthMiddleware
 *
 * Arquivo esperado:
 *
 *   /Middleware/AuthMiddleware.php
 *
 * =========================================================
 * OBSERVAÇÕES IMPORTANTES
 * =========================================================
 *
 * 1. O nome da classe DEVE ser igual ao nome do arquivo.
 *
 * Exemplo:
 *
 *   class UserService
 *
 * Arquivo:
 *
 *   UserService.php
 *
 * ---------------------------------------------------------
 *
 * 2. Atualmente o autoloader faz busca manual
 * em múltiplas pastas.
 *
 * Em projetos maiores, o ideal é evoluir para:
 *
 * - Namespaces
 * - PSR-4
 * - Composer
 *
 * =========================================================
 * LIMITAÇÕES ATUAIS
 * =========================================================
 *
 * O autoloader percorre várias pastas até encontrar
 * a classe, então:
 *
 * - funciona muito bem para projetos pequenos/médios
 * - pode perder performance em projetos grandes
 *
 * =========================================================
 */

class Autoloader
{
  /**
   * Registra o autoloader da aplicação.
   */
  public static function register(): void
  {
    spl_autoload_register(function (string $className) {

      /**
       * Pasta base da aplicação.
       */
      $basePath = __DIR__ . '/..';

      /**
       * Remove "\" inicial do namespace/classe.
       */
      $normalizedClass = ltrim($className, '\\');

      /**
       * Converte namespace em caminho de arquivo.
       *
       * Exemplo:
       *
       * App\Services\UserService
       *
       * vira:
       *
       * App/Services/UserService.php
       */
      $classPath = str_replace(
        '\\',
        DIRECTORY_SEPARATOR,
        $normalizedClass
      ) . '.php';

      /**
       * Lista de locais onde o autoloader
       * tentará encontrar a classe.
       */
      $candidatePaths = [
        // Caminho direto
        $basePath . DIRECTORY_SEPARATOR . $classPath,
        // Pastas da arquitetura
        $basePath . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Exceptions' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Validators' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Mail' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Constants' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'DTO' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
        $basePath . DIRECTORY_SEPARATOR . 'Middleware' . DIRECTORY_SEPARATOR . $normalizedClass . '.php',
      ];

      /**
       * Percorre os caminhos procurando o arquivo.
       */
      foreach ($candidatePaths as $path) {

        /**
         * Se encontrar o arquivo:
         * carrega automaticamente.
         */
        if (is_file($path)) {
          require_once $path;
          return;
        }
      }
    });
  }
}
