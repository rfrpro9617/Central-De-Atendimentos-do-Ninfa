<?php

class StatusHelper
{
  public static function cssClass(string $status = null): string
  {
    if (!$status) {
      return 'bg-gray-100 text-gray-600';
    }

    return match (mb_strtoupper($status)) {
      'ABERTO'              => 'bg-blue-100 text-blue-700',
      'EM_ANDAMENTO'        => 'bg-yellow-100 text-yellow-700',
      'FINALIZADO'          => 'bg-green-100 text-green-700',
      'CANCELADO'           => 'bg-red-100 text-red-700',
      'AGUARDANDO_USUARIO'  => 'bg-gray-200 text-gray-700',
      default               => 'bg-gray-100 text-gray-600',
    };
  }

  public static function label(string $status = null): string
  {
    if (!$status) {
      return '-';
    }

    return mb_convert_case(
      str_replace('_', ' ', mb_strtolower($status)),
      MB_CASE_TITLE,
      'UTF-8'
    );
  }
}
