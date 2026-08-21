<?php

/**
 * Exceção lançada quando um ticket/atendimento não é encontrado
 */
class TicketNotFoundException extends DomainException
{
  public function __construct(int $id = 0)
  {
    $message = 'Solicitação não encontrada.';
    if ($id > 0) {
      $message = "Solicitação #{$id} não encontrada.";
    }
    parent::__construct($message);
  }
}
