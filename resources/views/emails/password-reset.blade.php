<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - Cota Brasilis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #009739 0%, #046143 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .email-header img {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .email-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .email-body p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #009739 0%, #046143 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 151, 57, 0.4);
            transition: transform 0.2s;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 151, 57, 0.5);
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #009739;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }
        .info-box strong {
            color: #333;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .email-footer p {
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .email-footer a {
            color: #009739;
            text-decoration: none;
        }
        .email-footer img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .reset-button {
                padding: 14px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="cid:logo" alt="Cota Brasilis" style="max-width: 200px; height: auto;">
            <h1>Recuperação de Senha</h1>
            <p>Cota Brasilis</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Olá, {{ $name }}!</h2>
            
            <p>
                Recebemos uma solicitação para redefinir a senha da sua conta no <strong>Cota Brasilis</strong>.
            </p>

            <p>
                Clique no botão abaixo para criar uma nova senha:
            </p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Redefinir Minha Senha
                </a>
            </div>

            <p style="text-align: center; color: #999; font-size: 14px;">
                Ou copie e cole este link no seu navegador:<br>
                <a href="{{ $resetUrl }}" style="color: #009739; word-break: break-all;">{{ $resetUrl }}</a>
            </p>

            <div class="divider"></div>

            <div class="info-box">
                <p>
                    <strong>⏰ Importante:</strong> Este link expira em <strong>{{ $expiresIn }} minutos</strong> por motivos de segurança.
                </p>
                <p style="margin-top: 10px;">
                    <strong>🔒 Segurança:</strong> Se você não solicitou esta recuperação de senha, ignore este e-mail. Sua senha permanecerá inalterada.
                </p>
            </div>

            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Se você tiver problemas ao clicar no botão, copie e cole o link acima no seu navegador.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" style="max-width: 150px; height: auto;">
            <p>
                <strong>Cota Brasilis</strong><br>
                Sua plataforma de hospedagem por cotas de multipropriedade hoteleira
            </p>
            <p style="margin-top: 15px;">
                Este é um e-mail automático, por favor não responda.
            </p>
            <p style="margin-top: 10px;">
                © {{ date('Y') }} Cota Brasilis. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>
