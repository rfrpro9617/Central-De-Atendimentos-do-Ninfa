<?php

/**
 * Exceção lançada quando uma ou mais validações de negócio falham.
 *
 * Esta classe permite transportar uma lista de erros de validação
 * para a camada superior da aplicação (Controller, Middleware, etc.),
 * facilitando a exibição das mensagens ao usuário ou o retorno de
 * respostas de erro em APIs.
 *
 * Exemplo de uso:
 *
 * ```php
 * $errors = [
 *   'name' => 'Nome é obrigatório',
 *   'email' => 'E-mail inválido'
 * ];
 *
 * throw new ValidationExceptionHelper($errors);
 * ```
 */
class ValidationExceptionHelper extends Exception
{
  /**
   * Lista de erros de validação.
   *
   * A chave normalmente representa o campo validado e o valor
   * contém a mensagem de erro correspondente.
   *
   * Exemplo:
   *
   * ```php
   * [
   *   'name' => 'Nome é obrigatório',
   *   'email' => 'E-mail inválido'
   * ]
   * ```
   *
   * @var array<string, string>
   */
  public array $errors;

  /**
   * Cria uma nova exceção de validação.
   *
   * @param array<string, string> $errors Lista de erros encontrados durante a validação.
   */
  public function __construct(array $errors)
  {
    parent::__construct('Erro de validação');

    $this->errors = $errors;
  }
}
