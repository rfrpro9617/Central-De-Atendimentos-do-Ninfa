<?php

class AtendimentoMailerAguardandoUsuario
{
  private Mailer $mailer;

  public function __construct(Mailer $mailer)
  {
    $this->mailer = $mailer;
  }

  public function enviarSolicitacaoUsuario(
    string $email,
    array $atendimento,
    string $mensagem
  ): void {

    try {

      Logger::info(
        "Iniciando envio de email aguardando usuário | Email: {$email} | Atendimento: " .
          json_encode($atendimento, JSON_UNESCAPED_UNICODE)
      );

      $subject = "Solicitação de informações ao usuário - #{$atendimento['codigo']} - NINFA";

      $message = "
      <!DOCTYPE html>
      <html lang='pt-BR'>

      <head>
        <meta charset='UTF-8'>
        <title>Solicitação de informações ao usuário</title>
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
                      Solicitação de informações do técnico
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
                      Aguardando informações do usuário
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

                            O técnico solicitou informações adicionais.
                            <br><br>

                            Responda com as informações solicitadas para que o chamado
                            retorne automaticamente para o status
                            <strong>Em andamento</strong>.

                          </p>

                        </td>

                      </tr>

                    </table>

                    <!-- MENSAGEM DO TÉCNICO -->
                    <table
                      width='100%'
                      cellpadding='0'
                      cellspacing='0'
                      border='0'
                      style='
                        margin-bottom:24px;
                        background:#fef9c3;
                        border:1px solid #eab308;
                        border-radius:10px;
                      '
                    >

                      <tr>

                        <td style='padding:18px 20px;'>

                          <p style='
                            margin:0 0 10px 0;
                            color:#854d0e;
                            font-size:14px;
                            font-weight:bold;
                          '>
                            Solicitação do técnico:
                          </p>

                          <p style='
                            margin:0;
                            color:#713f12;
                            font-size:14px;
                            line-height:1.6;
                          '>

                            " . nl2br(htmlspecialchars($mensagem)) . "

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
                            <strong>Status:</strong>
                            Aguardando usuário
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

                          </div>

                        </td>
                      </tr>

                    </table>

                    <!-- BOTÃO CTA -->
                    <table
                      width='100%'
                      cellpadding='0'
                      cellspacing='0'
                      border='0'
                      style='margin-top:32px;'
                    >
                      <tr>
                        <td align='center'>
                          <table
                            cellpadding='0'
                            cellspacing='0'
                            border='0'
                          >
                            <tr>
                              <td
                                style='
                                  background:#166534;
                                  border-radius:8px;
                                  padding:16px 32px;
                                  text-align:center;
                                '
                              >
                                <a
                                  href='https://www.ufrgs.br/fagroz/ninfa.php?page=atendimentos&action=tickets.show&id={$atendimento['codigo']}'
                                  target='_blank'
                                  rel='noopener noreferrer'
                                  style='
                                    color:#ffffff;
                                    text-decoration:none;
                                    font-weight:bold;
                                    font-size:15px;
                                  '
                                >
                                  Retornar para Técnico
                                </a>
                              </td>
                            </tr>
                          </table>
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
        "Template de email aguardando usuário montado com sucesso | Email: {$email}"
      );

      $success = $this->mailer->send(
        $email,
        $subject,
        $message
      );

      if (!$success) {

        Logger::error(
          "Falha ao enviar email aguardando usuário | Email: {$email} | Assunto: {$subject}"
        );

        throw new Exception(
          'Erro ao enviar email'
        );
      }

      Logger::info(
        "Email aguardando usuário enviado com sucesso | Email: {$email} | Assunto: {$subject}"
      );
    } catch (\Throwable $e) {

      Logger::error(
        "Exception no AtendimentoMailerAguardandoUsuario | Erro: {$e->getMessage()}"
      );

      throw $e;
    }
  }
}
