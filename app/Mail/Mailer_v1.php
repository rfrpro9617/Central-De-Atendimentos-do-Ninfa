<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer
{
  private array $config;

  public function __construct(array $config = [])
  {
    $this->config = array_merge([
      'from_address' => 'ninfa@ufrgs.br',
      'from_name' => 'Ninfa - Equipe de Desenvolvimento de Sistemas',
      'content_type' => 'text/html; charset=UTF-8',
      // SMTP options (configured for Gmail authenticated SMTP)
      'smtp' => true,
      'smtp_host' => 'smtp.gmail.com',
      'smtp_port' => 587,
      'smtp_secure' => 'tls', // 'tls' or 'ssl'
      'smtp_auth' => true,
      'smtp_user' => 'fagroninfa@gmail.com', // your Gmail address
      'smtp_pass' => 'ojet beoo igxm skgj', // app password (recommended)
      'smtp_options' => [], // optional stream context options for SSL/TLS
    ], $config);
  }

  public function send(string $to, string $subject, string $message): bool
  {
    try {
      // Load local PHPMailer classes (project ships them under PHPMailer/)
      $base = __DIR__ . '/PHPMailer';
      require_once $base . '/Exception.php';
      require_once $base . '/PHPMailer.php';
      require_once $base . '/SMTP.php';

      $mail = new PHPMailer(true);

      if (!empty($this->config['smtp'])) {
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $this->config['smtp_host'];
        $mail->Port = (int)$this->config['smtp_port'];
        $mail->SMTPAutoTLS = true;
        $mail->SMTPSecure = $this->config['smtp_secure'];
        $mail->SMTPAuth = (bool)$this->config['smtp_auth'];
        if ($mail->SMTPAuth) {
          $mail->Username = $this->config['smtp_user'];
          $mail->Password = $this->config['smtp_pass'];
        }
        // Allow passing custom SMTPOptions (stream context) if needed
        if (!empty($this->config['smtp_options']) && is_array($this->config['smtp_options'])) {
          $mail->SMTPOptions = $this->config['smtp_options'];
        }
      }

      $mail->setFrom($this->config['from_address'], $this->config['from_name']);
      $mail->addAddress($to);
      $mail->Subject = $subject;
      $mail->isHTML(stripos($this->config['content_type'], 'html') !== false);
      $mail->Body = $message;
      $mail->AltBody = strip_tags($message);
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = 'base64';
      $mail->Timeout = 30;

      $mail->send();

      Logger::info("Email enviado com sucesso | Para: {$to} | Assunto: {$subject}");

      return true;
    } catch (PHPMailerException $e) {
      Logger::error("Mail Exception | Para: {$to} | Assunto: {$subject} | Erro: {$e->getMessage()}");
      return false;
    } catch (\Throwable $e) {
      Logger::error("Mail Exception | Para: {$to} | Assunto: {$subject} | Erro: {$e->getMessage()}");
      return false;
    }
  }
}
