<?php

class PrioridadeHelper
{
  public static function cssClass(string $prioridade = null): string
  {
    if (!$prioridade) {
      return 'bg-gray-100 text-gray-600';
    }

    return match (mb_strtolower($prioridade)) {
      'alta', 'urgente' => 'bg-red-100 text-red-700',
      'média', 'media'  => 'bg-yellow-100 text-yellow-700',
      'baixa'           => 'bg-emerald-100 text-emerald-700',
      default           => 'bg-gray-100 text-gray-600',
    };
  }

  public static function label(string $prioridade = null): string
  {
    if (!$prioridade) {
      return '-';
    }

    return ucfirst(mb_strtolower($prioridade));
  }
}
