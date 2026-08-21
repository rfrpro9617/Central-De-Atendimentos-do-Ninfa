<?php

class AtendimentoMailerAberto
{
  private Mailer $mailer;

  public function __construct(Mailer $mailer)
  {
    $this->mailer = $mailer;
  }

  public function enviarSolicitacaoCriada(
    string $email,
    array $atendimento
  ): void {

    try {

      Logger::info(
        "Iniciando envio de email de solicitação criada | Email: {$email} | Atendimento: " .
          json_encode($atendimento, JSON_UNESCAPED_UNICODE)
      );

      $subject = "Nova solicitação criada - #{$atendimento['codigo']} - NINFA";

      // Monta HTML dos anexos, caso existam (campo 'arquivo' pode ter caminhos separados por ';')
      $arquivoCampo = $atendimento['arquivo'] ?? '';
      $arquivos = array_filter(array_map('trim', explode(';', (string) $arquivoCampo)), fn($item) => $item !== '');

      $attachmentsHtml = '';
      if (!empty($arquivos)) {
        $attachmentsHtml = "<div style='margin-top:16px;'>\n";
        $attachmentsHtml .= "<p style='margin:0 0 10px 0;font-size:15px;font-weight:bold;'>Anexo(s)</p>\n";
        $baseHost = 'https://www.ufrgs.br/fagroz/uploads/atendimentos/';
        foreach ($arquivos as $a) {
          if (preg_match('/^https?:\/\//i', $a)) {
            $url = $a;
          } else {
            // Se o caminho já contém o segmento 'uploads/atendimentos', removemos
            // essa parte antes de concatenar para evitar duplicação do caminho.
            if (preg_match('#^(?:/)?(?:fagroz/)?uploads/atendimentos/#i', $a)) {
              $aTrimmed = preg_replace('#^(?:/)?(?:fagroz/)?uploads/atendimentos/#i', '', $a);
              $url = rtrim($baseHost, '/') . '/' . ltrim($aTrimmed, '/');
            } else {
              $url = rtrim($baseHost, '/') . '/' . ltrim($a, '/');
            }
          }
          $safeUrl = htmlspecialchars($url, ENT_QUOTES);
          $nomeArquivo = htmlspecialchars(basename($a), ENT_QUOTES);
          $attachmentsHtml .= "<p style='margin:0 0 8px 0;'><a href=\"{$safeUrl}\" target=\"_blank\" style=\'color:#2563eb;text-decoration:underline;\'>{$nomeArquivo}</a></p>\n";
        }
        $attachmentsHtml .= "</div>\n";
      }

      $message = "
      <!DOCTYPE html>
      <html lang='pt-BR'>

      <head>
        <meta charset='UTF-8'>
        <title>Nova Solicitação</title>
      </head>

      <body style='
        margin:0;
        padding:0;
        background-color:#f3f4f6;
        font-family:Arial, Helvetica, sans-serif;
        color:#1f2937;
      '>

        <table
          width='100%'
          cellpadding='0'
          cellspacing='0'
          border='0'
          style='background-color:#f3f4f6;padding:40px 0;'
        >

          <tr>
            <td align='center'>

              <table
                width='650'
                cellpadding='0'
                cellspacing='0'
                border='0'
                style='
                  background:#ffffff;
                  border-radius:16px;
                  overflow:hidden;
                  border:1px solid #e5e7eb;
                '
              >

                <!-- HEADER -->
                <tr>
                  <td
                    style='
                      background:#166534;
                      padding:28px 32px;
                    '
                  >

                    <h1 style='
                      margin:0;
                      color:#ffffff;
                      font-size:28px;
                      font-weight:bold;
                    '>
                      NINFA - Atendimento
                    </h1>

                    <p style='
                      margin:8px 0 0 0;
                      color:#dcfce7;
                      font-size:14px;
                    '>
                      Nova solicitação registrada no sistema
                    </p>

                  </td>
                </tr>

                <!-- BODY -->
                <tr>

                  <td style='padding:32px;'>

                    <h2 style='
                      margin-top:0;
                      margin-bottom:24px;
                      color:#111827;
                      font-size:22px;
                    '>
                      Solicitação criada com sucesso
                    </h2>

                    <!-- ALERTA -->
                    <table
                      width='100%'
                      cellpadding='0'
                      cellspacing='0'
                      border='0'
                      style='
                        margin-bottom:24px;
                        background:#fef2f2;
                        border:1px solid #dc2626;
                        border-radius:10px;
                      '
                    >

                      <tr>

                        <td style='padding:18px 20px;'>

                          <p style='
                            margin:0;
                            color:#b91c1c;
                            font-size:14px;
                            font-weight:bold;
                            line-height:1.6;
                          '>

                            Atenção:
                            Assim que um técnico iniciar o atendimento desta solicitação,
                            você receberá automaticamente uma nova mensagem por e-mail
                            com a atualização do status.

                          </p>

                        </td>

                      </tr>

                    </table>

                    <!-- CARD -->
                    <table
                      width='100%'
                      cellpadding='0'
                      cellspacing='0'
                      border='0'
                      style='
                        border:1px solid #e5e7eb;
                        border-radius:12px;
                        background:#f9fafb;
                      '
                    >

                      <tr>
                        <td style='padding:24px;'>

                          <p style='margin:0 0 16px 0;font-size:15px;'>
                            <strong>Código:</strong>
                            {$atendimento['codigo']}
                          </p>

                          <p style='margin:0 0 16px 0;font-size:15px;'>
                            <strong>Responsável:</strong>
                            " . htmlspecialchars($atendimento['responsavel'] ?? 'Não informado') . "
                          </p>

                          <p style='margin:0 0 16px 0;font-size:15px;'>
                            <strong>Prioridade:</strong>
                            " . htmlspecialchars($atendimento['prioridade'] ?? 'Não informado') . "
                          </p>

                          <p style='margin:0 0 16px 0;font-size:15px;'>
                            <strong>Patrimônio:</strong>
                            " . htmlspecialchars($atendimento['patrimonio'] ?? 'Não informado') . "
                          </p>

                          <p style='margin:0 0 16px 0;font-size:15px;'>
                            <strong>Demanda:</strong>
                            " . htmlspecialchars($atendimento['demanda'] ?? 'Não informado') . "
                          </p>

                          <div style='margin-top:24px;'>

                            <p style='
                              margin:0 0 10px 0;
                              font-size:15px;
                              font-weight:bold;
                            '>
                              Descrição do problema
                            </p>

                            <div style='
                              background:#ffffff;
                              border:1px solid #d1d5db;
                              border-radius:10px;
                              padding:16px;
                              font-size:14px;
                              line-height:1.7;
                              color:#374151;
                            '>

                              " . nl2br(htmlspecialchars($atendimento['descricao'] ?? 'Sem descrição')) . "

                            </div>

                            {$attachmentsHtml}

                          </div>

                        </td>
                      </tr>

                    </table>

                  </td>

                </tr>

                <!-- FOOTER -->
                <tr>

                  <td
                    style='
                      background:#f9fafb;
                      border-top:1px solid #e5e7eb;
                      padding:24px 32px;
                    '
                  >

                    <p style='
                      margin:0;
                      font-size:13px;
                      color:#6b7280;
                      text-align:center;
                    '>
                      Sistema de Atendimento NINFA · Faculdade de Agronomia · UFRGS<br>
                      <a href='https://www.ufrgs.br/fagroz/ninfa.php?page=home' style='color:#2563eb;text-decoration:underline;'>Acesse o site</a>
                    </p>

                  </td>

                </tr>

              </table>

            </td>
          </tr>

        </table>

      </body>

      </html>
      ";

      Logger::info(
        "Template de email montado com sucesso | Email: {$email}"
      );

      $success = $this->mailer->send(
        $email,
        $subject,
        $message
      );

      if (!$success) {

        Logger::error(
          "Falha ao enviar email | Email: {$email} | Assunto: {$subject}"
        );

        throw new Exception(
          'Erro ao enviar email'
        );
      }

      Logger::info(
        "Email enviado com sucesso | Email: {$email} | Assunto: {$subject}"
      );
    } catch (\Throwable $e) {

      Logger::error(
        "Exception no AtendimentoMailerAberto | Erro: {$e->getMessage()}"
      );

      throw $e;
    }
  }
}
