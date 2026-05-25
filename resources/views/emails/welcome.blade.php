<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Cota Brasilis</title>
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
        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #009739;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .credentials-box h3 {
            color: #009739;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .credentials-box p {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .credentials-box strong {
            color: #333;
            display: inline-block;
            min-width: 120px;
        }
        .quota-info-box {
            background-color: #e6f5eb;
            border: 1px solid #009739;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }
        .quota-info-box h3 {
            color: #046143;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .quota-info-box p {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .quota-info-box .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #009739;
            color: #ffffff;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
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
            <h1>Bem-vindo ao Cota Brasilis</h1>
            <p>Sua plataforma de hospedagem por cotas de multipropriedade hoteleira</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Olá, {{ $userName }}</h2>
            
            <p>
                É um prazer tê-lo(a) conosco. Seu cadastro foi realizado com sucesso na plataforma <strong>Cota Brasilis</strong>.
            </p>

            <div class="credentials-box">
                <h3>📧 Suas Credenciais de Acesso</h3>
                <p><strong>E-mail:</strong> {{ $userEmail }}</p>
                <p><strong>Senha:</strong> {{ $userPassword }}</p>
                <p style="margin-top: 15px; color: #d9534f; font-size: 13px;">
                    <strong>⚠️ Importante:</strong> Guarde estas informações em local seguro. Recomendamos alterar sua senha após o primeiro acesso.
                </p>
            </div>

            @if($quotaInfo)
            <div class="quota-info-box">
                <h3>🏨 Informações da Sua Cota</h3>
                
                @if($quotaInfo['is_gestor'])
                    <p><span class="badge">Gestor</span> Você cadastrou como <strong>Gestor de Cotas</strong></p>
                @else
                    <p><span class="badge">Proprietário</span> Você cadastrou como <strong>Proprietário de Cota</strong></p>
                @endif

                @if($quotaInfo['is_fractioned'])
                    <p><span class="badge">Fracionada</span> Sua cota foi cadastrada como <strong>Fracionada</strong></p>
                @else
                    <p> </p>
                @endif

                @if(isset($quotaInfo['hotel_name']))
                    <p><strong>Hotel:</strong> {{ $quotaInfo['hotel_name'] }}</p>
                @endif

                @if(isset($quotaInfo['profile_type']))
                    <p><strong>Perfil:</strong> {{ ucfirst($quotaInfo['profile_type']) }}</p>
                @endif

                @if(isset($quotaInfo['allowed_uses']) && count($quotaInfo['allowed_uses']) > 0)
                    <p><strong>Usos Permitidos:</strong> 
                        @foreach($quotaInfo['allowed_uses'] as $use)
                            @if($use == 'rent') Aluguel @endif
                            @if($use == 'exchange') Troca @endif
                            @if($use == 'sell') Venda @endif
                            @if($use == 'buy') Compra @endif
                            @if(!$loop->last), @endif
                        @endforeach
                    </p>
                @endif
            </div>
            @endif

            <p>
                Agora você pode começar a usar as funcionalidades de acordo com o <b>perfil escolhido</b> na plataforma:
            </p>

            <ul style="color: #666; font-size: 16px; line-height: 2; margin-left: 20px;">
                <li>Publicar suas <b>Cotas</b> ou <b>Frações</b> para aluguel ou troca</li>
                <li>Publicar suas <b>Cotas</b> para aluguel, troca ou venda</li>
                <li>Solicitar <b>Cotas ou Frações</b> disponíveis em diversos destinos e hotéis, para alugar, trocar ou comprar</li>
                <li><b>Assessoria</b> da equipe de profissionais do Cota Brasilis para vender ou adquirir Cotas Hoteleiras </li>
                <li>Gerenciar suas <b>Reservas</b> ou <b>Ofertas</b></li>
                <li>Aproveitar Descontos, Promoções e <b>Ofertas exclusivas</b> no Cota Brasilis</li>
                <li>Avaliar a sua experiência no Cota Brasilis e sugerir a inclusão de Hotéis e serviços</li>
            </ul>
            <p>
                <h3>Vem com a gente. Boralá Brasil. Cota Brasilis.</h5>
            </p>

            <div class="button-container">
                <a href="{{ route('dashboard') }}" class="action-button">
                    Acessar Minha Conta
                </a>
            </div>

            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Se você tiver alguma dúvida, nossa equipe de suporte está pronta para ajudar.
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
