<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aluguel da cota concluído - Cota Brasilis</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .email-header { background: linear-gradient(135deg, #009739 0%, #046143 100%); padding: 30px; text-align: center; color: #fff; }
        .email-header h1 { font-size: 22px; margin: 0; }
        .email-body { padding: 30px; }
        .email-body h2 { color: #009739; font-size: 18px; margin-bottom: 16px; }
        .email-body p { color: #555; margin-bottom: 12px; }
        .info-block { background: #f8f9fa; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info-block strong { display: inline-block; min-width: 120px; }
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
        .email-footer img {
            max-width: 150px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Aluguel da cota concluído com sucesso</h1>
        </div>
        <div class="email-body">
            <p>Olá, <strong>{{ $recipientName }}</strong>.</p>
            <p>O aluguel da cota de multipropriedade hoteleira foi concluído com êxito.</p>
            <div class="info-block">
                <p><strong>Hotel:</strong> {{ $hotelName }}</p>
                <p><strong>Local:</strong> {{ $location }}</p>
                <p><strong>Período:</strong> {{ $periodText }}</p>
                <p><strong>Proprietário:</strong> {{ $ownerName }}</p>
                <p><strong>Interessado:</strong> {{ $renterName }}</p>
            </div>
            <p>Em anexo está o termo de autorização de hospedagem assinado.</p>
            <p>Atenciosamente,<br><strong>Cota Brasilis</strong></p>
        </div>
        <!-- Footer (padrão e-mail de boas-vindas) -->
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
