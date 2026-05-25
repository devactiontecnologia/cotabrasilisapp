<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anúncio de Venda Criado - Cota Brasilis</title>
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
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #009739;
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
        .offer-info-box {
            background-color: #e6f5eb;
            border: 1px solid #009739;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }
        .offer-info-box h3 {
            color: #046143;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .offer-info-box p {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .offer-info-box strong {
            color: #046143;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .action-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #009739 0%, #046143 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 151, 57, 0.4);
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
        .email-footer img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .email-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="cid:logo" alt="Cota Brasilis" style="max-width: 200px; height: auto;">
            <h1>Anúncio de Venda Criado!</h1>
            <p>Seu anúncio está disponível na plataforma</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Olá, {{ $userName }}!</h2>
            
            <p>
                Seu anúncio de <strong>venda</strong> foi criado com sucesso na plataforma <strong>Cota Brasilis</strong>!
            </p>

            <div class="offer-info-box">
                <h3>💰 Detalhes do Anúncio</h3>
                <p><strong>Título:</strong> {{ $offerTitle }}</p>
                @if(isset($hotelName))
                    <p><strong>Hotel:</strong> {{ $hotelName }}</p>
                @endif
                @if(isset($location))
                    <p><strong>Localização:</strong> {{ $location }}</p>
                @endif
                @if(isset($price))
                    <p><strong>Preço:</strong> R$ {{ number_format($price, 2, ',', '.') }}</p>
                @endif
            </div>

            <p>
                Seu anúncio já está visível para outros usuários da plataforma interessados em comprar cotas. Você pode gerenciá-lo a qualquer momento através do seu painel.
            </p>

            <div class="button-container">
                <a href="{{ route('sales.index') }}" class="action-button">
                    Ver Meus Anúncios de Venda
                </a>
            </div>

            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Boa sorte com sua venda! 🎉
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
