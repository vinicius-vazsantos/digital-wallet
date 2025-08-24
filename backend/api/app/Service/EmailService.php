<?php

declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Hyperf\Utils\Env;

class EmailService
{
    public function sendWithdrawalEmail(string $to, float $amount, string $pixKey, string $pixType, string $date): void
    {
        $mail = new PHPMailer(true);

        try {
            // Configurações de conexão
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST') ?: 'localhost';
            $mail->Port = getenv('MAIL_PORT') ?: 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;

            // Remetente
            $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@digitalwallet.com', 'Digital Wallet');
            $mail->addAddress($to);

            // Conteúdo do email
            $mail->isHTML(true);
            $mail->Subject = 'Saque realizado - Digital Wallet';
            
            $html = $this->renderEmailTemplate($amount, $pixKey, $pixType, $date);
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);

            // Envia o email
            $mail->send();
            
        } catch (PHPMailerException $e) {
            throw new \RuntimeException("Erro ao enviar email para Mailhog: " . $e->getMessage());
        }
    }

    private function renderEmailTemplate(float $amount, string $pixKey, string $pixType, string $date): string
    {
        $amountFormatted = number_format($amount, 2, ',', '.');
        
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Saque Realizado - Digital Wallet</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { background-color: #f8f9fa; padding: 20px; }
                    .footer { background-color: #343a40; color: white; padding: 10px; text-align: center; }
                    .info-item { margin-bottom: 10px; }
                    .amount { font-size: 18px; font-weight: bold; color: #28a745; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>💰 Saque Realizado</h1>
                    </div>
                    
                    <div class="content">
                        <p>Olá,</p>
                        <p>Seu saque foi processado com sucesso! Aqui estão os detalhes:</p>
                        
                        <div class="info-item">
                            <strong>Valor sacado:</strong> 
                            <span class="amount">R$ {$amountFormatted}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Data e hora:</strong> {$date}
                        </div>
                        
                        <div class="info-item">
                            <strong>Chave PIX:</strong> {$pixKey}
                        </div>
                        
                        <div class="info-item">
                            <strong>Tipo de chave:</strong> {$pixType}
                        </div>
                        
                        <p>O valor será transferido para sua conta em até 30 minutos.</p>
                    </div>
                    
                    <div class="footer">
                        <p><small>Digital Wallet &copy; 2025 - Este é um email automático</small></p>
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }
}