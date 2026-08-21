<?php
// TODO: adicionar validações do tipo allowed
class CancelarAtendimentoValidator
{
  public function validate(array $data): array
  {
    $errors = [];

    if (empty(trim($data['mensagem'] ?? ''))) {
      $errors['mensagem'] = 'Informe o motivo do cancelamento do chamado';
    }

    if (!empty($errors)) {
      throw new ValidationExceptionHelper($errors);
    }

    return $data;
  }
}
