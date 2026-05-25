<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send email using PHPMailer
     * @param string|null $attachmentPath Full path to file to attach
     */
    private function sendEmail($to, $toName, $subject, $htmlBody, $attachmentPath = null)
    {
        // Não tentar enviar se SMTP não estiver configurado (evita travamento no servidor)
        if (!env('MAIL_HOST') || !env('MAIL_USERNAME')) {
            Log::info('Email não enviado: MAIL_HOST ou MAIL_USERNAME não configurados no .env');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port = env('MAIL_PORT', 587);
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 5;
            $mail->SMTPKeepAlive = false;
            $mail->SMTPDebug = 0;

            // Configurações específicas para Hostinger
            $mailHost = env('MAIL_HOST', '');
            if (strpos($mailHost, 'hostinger') !== false) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            }

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Cota Brasilis'));
            $mail->addAddress($to, $toName);

            // Embed logo image
            $logoPath = public_path('images/logo/logo.png');
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo', 'logo.png', 'base64', 'image/png');
            }

            if ($attachmentPath && is_string($attachmentPath) && file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath, basename($attachmentPath));
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::error('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            Log::error('Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($user, $password, $quotaInfo = null)
    {
        $quotaData = null;
        if ($quotaInfo) {
            $quotaData = [
                'is_gestor' => $quotaInfo['is_gestor'] ?? false,
                'is_fractioned' => $quotaInfo['is_fractioned'] ?? false,
                'hotel_name' => $quotaInfo['hotel_name'] ?? null,
                'profile_type' => $quotaInfo['profile_type'] ?? null,
                'allowed_uses' => $quotaInfo['allowed_uses'] ?? [],
            ];
        }

        $html = view('emails.welcome', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'userPassword' => $password,
            'quotaInfo' => $quotaData,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Bem-vindo ao Cota Brasilis',
            $html
        );
    }

    /**
     * Send account under review email after registration.
     */
    public function sendAccountUnderReviewEmail($user)
    {
        $html = view('emails.account-under-review', [
            'userName' => $user->name,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Recebemos seu cadastro: conta em análise - Cota Brasilis',
            $html
        );
    }

    /**
     * Send account approved email after admin approval.
     */
    public function sendAccountApprovedEmail($user)
    {
        $html = view('emails.account-approved', [
            'userName' => $user->name,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Sua conta foi aprovada - Cota Brasilis',
            $html
        );
    }

    /**
     * Send rental offer created notification
     */
    public function sendRentalOfferCreatedEmail($user, $offer)
    {
        $html = view('emails.rental-offer-created', [
            'userName' => $user->name,
            'offerTitle' => $offer->title ?? $offer->display_title ?? 'Anúncio de Aluguel',
            'hotelName' => $offer->hotel->name ?? $offer->hotel_name ?? null,
            'location' => ($offer->city ?? '') . ', ' . ($offer->state ?? ''),
            'startDate' => $offer->start_date ? \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') : null,
            'endDate' => $offer->end_date ? \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') : null,
            'price' => $offer->price ?? $offer->rental_price ?? null,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Anúncio de Aluguel Criado - Cota Brasilis',
            $html
        );
    }

    /**
     * Send exchange offer created notification
     */
    public function sendExchangeOfferCreatedEmail($user, $offer)
    {
        $quota = $offer->quota ?? null;
        $html = view('emails.exchange-offer-created', [
            'userName' => $user->name,
            'offerTitle' => 'Anúncio de Troca',
            'hotelName' => $quota ? ($quota->hotel->name ?? $quota->hotel_name ?? null) : null,
            'location' => $quota ? $quota->location : null,
            'startDate' => $quota && $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') : null,
            'endDate' => $quota && $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') : null,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Anúncio de Troca Criado - Cota Brasilis',
            $html
        );
    }

    /**
     * Send sale offer created notification
     */
    public function sendSaleOfferCreatedEmail($user, $offer)
    {
        $quota = $offer->quota ?? null;
        $hotel = $offer->hotel ?? null;
        $html = view('emails.sale-offer-created', [
            'userName' => $user->name,
            'offerTitle' => 'Anúncio de Venda',
            'hotelName' => $hotel ? $hotel->name : ($quota ? ($quota->hotel->name ?? $quota->hotel_name ?? null) : null),
            'location' => $offer->city ?? ($quota ? $quota->location : null),
            'price' => $offer->desired_price ?? $offer->acceptable_price ?? $offer->minimum_price ?? null,
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            'Anúncio de Venda Criado - Cota Brasilis',
            $html
        );
    }

    /**
     * Send rental success notification (same template + document attachment) to hotel, owner and renter.
     */
    public function sendRentalSuccessEmails(\App\Models\QuotaTransaction $transaction)
    {
        $quota = $transaction->quota;
        $owner = $transaction->owner;
        $renter = $transaction->renter;
        $hotel = $quota && $quota->hotel_name
            ? \App\Models\Hotel::where('name', $quota->hotel_name)->first()
            : null;

        $hotelName = $quota->hotel_name ?? '—';
        $location = $quota->location ?? '—';
        $periodLines = $quota ? $quota->getPeriodDisplayLines() : [];
        $periodText = !empty($periodLines)
            ? implode(' | ', array_map(fn ($l) => $l['formatted'], $periodLines))
            : ($quota && $quota->start_date && $quota->end_date
                ? $quota->start_date->format('d/m/Y') . ' - ' . $quota->end_date->format('d/m/Y')
                : '—');
        $ownerName = $owner ? $owner->name : '—';
        $renterName = $renter ? $renter->name : '—';

        $docPath = null;
        if ($transaction->owner_signed_document_path) {
            $full = storage_path('app/public/' . $transaction->owner_signed_document_path);
            if (file_exists($full)) {
                $docPath = $full;
            }
        }
        if (!$docPath && $transaction->renter_signed_document_path) {
            $full = storage_path('app/public/' . $transaction->renter_signed_document_path);
            if (file_exists($full)) {
                $docPath = $full;
            }
        }

        $html = view('emails.rental-success', [
            'recipientName' => '', // filled per recipient
            'hotelName' => $hotelName,
            'location' => $location,
            'periodText' => $periodText,
            'ownerName' => $ownerName,
            'renterName' => $renterName,
        ])->render();

        $subject = 'Aluguel da cota concluído com sucesso - Cota Brasilis';

        if ($hotel && !empty($hotel->email)) {
            $htmlHotel = view('emails.rental-success', [
                'recipientName' => $hotel->name ?? 'Hotel',
                'hotelName' => $hotelName,
                'location' => $location,
                'periodText' => $periodText,
                'ownerName' => $ownerName,
                'renterName' => $renterName,
            ])->render();
            $this->sendEmail($hotel->email, $hotel->name ?? 'Hotel', $subject, $htmlHotel, $docPath);
        }

        if ($owner && $owner->email) {
            $htmlOwner = view('emails.rental-success', [
                'recipientName' => $owner->name,
                'hotelName' => $hotelName,
                'location' => $location,
                'periodText' => $periodText,
                'ownerName' => $ownerName,
                'renterName' => $renterName,
            ])->render();
            $this->sendEmail($owner->email, $owner->name, $subject, $htmlOwner, $docPath);
        }

        if ($renter && $renter->email) {
            $htmlRenter = view('emails.rental-success', [
                'recipientName' => $renter->name,
                'hotelName' => $hotelName,
                'location' => $location,
                'periodText' => $periodText,
                'ownerName' => $ownerName,
                'renterName' => $renterName,
            ])->render();
            $this->sendEmail($renter->email, $renter->name, $subject, $htmlRenter, $docPath);
        }
    }
}
