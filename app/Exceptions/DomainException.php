<?php

/**
 * Exceção base para erros de domínio (lógica de negócio)
 * 
 * Exceções de domínio representam violações de regras de negócio,
 * diferente de exceções técnicas como erros de banco de dados.
 */
class DomainException extends Exception
{
}
