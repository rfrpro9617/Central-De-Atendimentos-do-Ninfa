<?php

class AtendimentoMailerEmAndamento
{
  private Mailer $mailer;

  public function __construct(Mailer $mailer)
  {
    $this->mailer = $mailer;
  }

  public function enviarAtendimentoEmAndamento(
    string $email,
    string $tecnico,
    array $atendimento,
    string $origem = 'aberto',
    ?string $mensagemUsuario = null,
  ): void {

    try {

      Logger::info(
        "Iniciando envio de email EM ANDAMENTO | Origem: {$origem} | Email: {$email} | Atendimento: " .
          json_encode($atendimento, JSON_UNESCAPED_UNICODE)
      );

      $subject = 'Atualização de atendimento - NINFA';

      $titulo = 'Atendimento em andamento';
      $mensagemAlerta = '';
      $blocoMensagemUsuario = '';

      // =========================
      // ABERTO
      // =========================
      if ($origem === 'aberto') {

        $subject = "Atendimento iniciado - #{$atendimento['codigo']} - NINFA";

        $titulo = 'Atendimento iniciado';

        $mensagemAlerta = "
          O técnico {$tecnico} iniciou o atendimento da sua solicitação.<br><br>
          Fique atento ao seu e-mail, pois o técnico pode solicitar informações adicionais.
        ";
      }

      // =========================
      // RETORNO USUÁRIO
      // =========================
      if ($origem === 'retorno_usuario') {

        $subject = "Retorno do usuário ao técnico - #{$atendimento['codigo']} - NINFA";

        $titulo = 'Retorno do usuário para o técnico';

        $mensagemAlerta = "
          O usuário respondeu ao chamado e o atendimento voltou automaticamente
          para EM ANDAMENTO.<br><br>

          O técnico deve continuar a análise e seguir com a solução.
        ";

        if (!empty($mensagemUsuario)) {

          Logger::info(
            "Mensagem do usuário adicionada ao template | Atendimento: {$atendimento['codigo']}"
          );

          $blocoMensagemUsuario = "
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
                    Mensagem do usuário:
                  </p>

                  <p style='
                    margin:0;
                    color:#713f12;
                    font-size:14px;
                    line-height:1.6;
                  '>

                    " . nl2br(htmlspecialchars($mensagemUsuario)) . "

                  </p>

                </td>

              </tr>

            </table>
          ";
        }
      }

      $message = "
      <!DOCTYPE html>
      <html lang='pt-BR'>

      <head>
        <meta charset='UTF-8'>
        <title>{$titulo}</title>
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
                      Atualização de status do atendimento
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
                      {$titulo}
                    </h2>

                    <!-- ALERTA -->
                    <table
                      width='100%'
                      cellpadding='0'
                      cellspacing='0'
                      border='0'
                      style='
                        margin-bottom:24px;
                        background:#ecfdf5;
                        border:1px solid #16a34a;
                        border-radius:10px;
                      '
                    >

                      <tr>

                        <td style='padding:18px 20px;'>

                          <p style='
                            margin:0;
                            color:#166534;
                            font-size:14px;
                            font-weight:bold;
                            line-height:1.6;
                          '>

                            {$mensagemAlerta}

                          </p>

                        </td>

                      </tr>

                    </table>

                    {$blocoMensagemUsuario}

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
                            Em andamento
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
        "Template de email EM ANDAMENTO montado com sucesso | Email: {$email} | Origem: {$origem}"
      );

      $success = $this->mailer->send(
        $email,
        $subject,
        $message
      );

      if (!$success) {

        Logger::error(
          "Falha ao enviar email EM ANDAMENTO | Email: {$email} | Assunto: {$subject}"
        );

        throw new Exception(
          'Erro ao enviar email de atendimento em andamento'
        );
      }

      Logger::info(
        "Email EM ANDAMENTO enviado com sucesso | Email: {$email} | Assunto: {$subject}"
      );
    } catch (\Throwable $e) {

      Logger::error(
        "Exception no AtendimentoMailerEmAndamento | Erro: {$e->getMessage()}"
      );

      throw $e;
    }
  }
}
