<?php
// TODO: adicionar validações do tipo allowed
class StoreSolicitacaoValidator
{
  public function validate(array $data): array
  {
    $errors = [];

    if (empty($data['responsavel'])) {
      $errors['responsavel'] = 'Responsável é obrigatório';
    }

    if (empty($data['prioridade_id'])) {
      $errors['prioridade_id'] = 'Prioridade é obrigatória';
    }

    if (empty($data['demanda'])) {
      $errors['demanda'] = 'Demanda é obrigatória';
    }

    if (mb_strlen($data['patrimonio'] ?? '') > 24) {
      $errors['patrimonio'] = 'Patrimônio deve ter no máximo 24 caracteres';
    }

    if (empty($data['descricao'])) {
      $errors['descricao'] = 'Descrição é obrigatória';
    }

    if (!empty($errors)) {
      throw new ValidationExceptionHelper($errors);
    }

    return $data;
  }
}
