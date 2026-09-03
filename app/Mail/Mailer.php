<?php

require_once __DIR__ . '/../../config/Env.php';
loadEnv(__DIR__ . '/../../.env_');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer
{
  private array $config;

  public function __construct(array $config = [])
  {
    $this->config = array_merge([
      'from_address' => env('MAIL_FROM_ADDRESS'),
      'from_name' => env('MAIL_FROM_NAME'),
      'content_type' => 'text/html; charset=UTF-8',
      'smtp' => true,
      'smtp_host' => env('MAIL_HOST', 'smtp.gmail.com'),
      'smtp_port' => (int)env('MAIL_PORT', 587),
      'smtp_secure' => env('MAIL_ENCRYPTION', 'tls'),
      'smtp_auth' => true,
      'smtp_user' => env('MAIL_USERNAME'),
      'smtp_pass' => env('MAIL_PASSWORD'),
      'smtp_options' => [],
    ], $config);
  }

  public function send(string $to, string $subject, string $message): bool
  {
    try {
      // Load local PHPMailer classes
      $base = __DIR__ . '/PHPMailer';

      require_once $base . '/Exception.php';
      require_once $base . '/PHPMailer.php';
      require_once $base . '/SMTP.php';

      $mail = new PHPMailer(true);

      if (!empty($this->config['smtp'])) {
        $mail->isSMTP();

        /*
                 * ==========================================================
                 * DEBUG SMTP TEMPORÁRIO
                 * ==========================================================
                 *
                 * O PHPMailer enviará todas as mensagens de diagnóstico
                 * para o Logger do sistema, sem imprimir nada na tela.
                 */

        $mail->SMTPDebug = 3;

        $mail->Debugoutput = function ($str, $level) {
          Logger::error(
            "PHPMailer SMTP DEBUG | Nivel: {$level} | {$str}"
          );
        };

        /*
                 * ==========================================================
                 * CONFIGURAÇÃO SMTP
                 * ==========================================================
                 */

        $mail->Host = $this->config['smtp_host'];
        $mail->Port = (int)$this->config['smtp_port'];
        $mail->SMTPAutoTLS = true;
        $mail->SMTPSecure = $this->config['smtp_secure'];
        $mail->SMTPAuth = (bool)$this->config['smtp_auth'];

        if ($mail->SMTPAuth) {
          $mail->Username = $this->config['smtp_user'];
          $mail->Password = $this->config['smtp_pass'];
        }

        /*
                 * Permite passar opções customizadas de SMTP
                 */
        if (
          !empty($this->config['smtp_options']) &&
          is_array($this->config['smtp_options'])
        ) {
          $mail->SMTPOptions = $this->config['smtp_options'];
        }
      }

      /*
             * ==========================================================
             * CONFIGURAÇÃO DO E-MAIL
             * ==========================================================
             */

      $mail->setFrom(
        $this->config['from_address'],
        $this->config['from_name']
      );

      $mail->addAddress($to);
      $mail->Subject = $subject;

      $mail->isHTML(
        stripos(
          $this->config['content_type'],
          'html'
        ) !== false
      );

      $mail->Body = $message;
      $mail->AltBody = strip_tags($message);
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = 'base64';
      $mail->Timeout = 30;

      /*
             * ==========================================================
             * LOG DA CONFIGURAÇÃO (SEM SENHA)
             * ==========================================================
             */

      Logger::info(
        "Tentando enviar e-mail | " .
          "Para: {$to} | " .
          "Assunto: {$subject} | " .
          "SMTP: {$this->config['smtp_host']}:{$this->config['smtp_port']} | " .
          "Encryption: {$this->config['smtp_secure']}"
      );

      /*
             * ==========================================================
             * ENVIO
             * ==========================================================
             */

      $mail->send();

      Logger::info(
        "Email enviado com sucesso | Para: {$to} | Assunto: {$subject}"
      );

      return true;
    } catch (PHPMailerException $e) {
      Logger::error(
        "Mail Exception | " .
          "Para: {$to} | " .
          "Assunto: {$subject} | " .
          "Erro: {$e->getMessage()} | " .
          "SMTP Error: {$mail->ErrorInfo}"
      );

      return false;
    } catch (\Throwable $e) {
      Logger::error(
        "Mail Exception | " .
          "Para: {$to} | " .
          "Assunto: {$subject} | " .
          "Erro: {$e->getMessage()}"
      );

      return false;
    }
  }
}
