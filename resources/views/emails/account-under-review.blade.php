<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conta em análise - Cota Brasilis</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f4f4f4; color: #334155; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; }
        .header { background: linear-gradient(135deg, #009739 0%, #046143 100%); color: #fff; padding: 32px 24px; text-align: center; }
        .header img { max-width: 180px; height: auto; margin-bottom: 14px; }
        .body { padding: 30px 24px; line-height: 1.7; }
        .title { color: #009739; font-size: 24px; margin: 0 0 14px; }
        .box { background: #f8fafc; border-left: 4px solid #009739; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .button-wrap { text-align: center; margin: 28px 0 10px; }
        .button { display: inline-block; background: linear-gradient(135deg, #009739 0%, #046143 100%); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; }
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
    <div class="container">
        <div class="header">
            <img src="cid:logo" alt="Cota Brasilis">
            <h1 style="margin: 0;">Cadastro recebido com sucesso</h1>
        </div>

        <div class="body">
            <h2 class="title">Olá, {{ $userName }}</h2>
            <p>Seu cadastro foi concluído e sua conta está <strong>sob análise</strong> pela nossa equipe.</p>

            <div class="box">
                <p style="margin: 0 0 8px;"><strong>Próximo passo:</strong></p>
                <p style="margin: 0;">Vamos validar seus documentos e informações enviadas. Se estiver tudo correto, sua conta será aprovada e você receberá um novo e-mail de confirmação.</p>
            </div>

            <p>Esse processo é importante para manter a segurança e a qualidade da plataforma para todos os usuários.</p>

            <div class="button-wrap">
                <a href="{{ route('dashboard') }}" class="button">Acompanhar minha conta</a>
            </div>
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
