<?php
// TODO: adicionar validações do tipo allowed
class FinalizarAtendimentoValidator
{
  public function validate(array $data): array
  {
    $errors = [];

    if (empty($data['executor'])) {
      $errors['executor'] = 'Selecione um executor';
    }

    if (empty($data['procedimento'])) {
      $errors['procedimento'] = 'Selecione pelo menos um procedimento';
    }

    if (empty($data['observacao'])) {
      $errors['observacao'] = 'Informe as observações realizadas no chamado';
    }

    if (!empty($errors)) {
      throw new ValidationExceptionHelper($errors);
    }

    return $data;
  }
}
