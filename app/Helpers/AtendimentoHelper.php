<?php

class AtendimentoHelper
{
  public static function transform(string $txt): string
  {
    $txt = strip_tags($txt);
    $txt = trim($txt);

    return $txt;
  }

  public static function procedimentos(): array
  {
    return [
      'Instalação e Configuração de Software (sistema operacional, antivírus, Adobe Acrobat, Adobe Flash, Java, Office, back-up de arquivos, etc.)',
      'Instalação e Configuração de Hardware (placa-mãe, placa de vídeo, placa de rede, memória, fonte de alimentação, etc.)',
      'Suporte Técnico (configuração de impressoras, aplicativos, rede, multimídia, webconferência, etc.)',
      'Atualização de páginas, Publicação de notícias, Recuperação dos dados de acesso à Intranet, Manutenção e desenvolvimento de sistemas Web, etc.',
      'Outros (confecção de cabos, substituição de mouse/teclado/monitor com defeito, etc.)'
    ];
  }
}
