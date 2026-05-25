<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send email notification
     */
    public function sendEmail(User $user, string $subject, string $message, array $data = [])
    {
        try {
            // Simulação de envio de email
            // Em produção, usar Mail::send() ou Mail::queue()
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject);
            });
            
            // Criar notificação no banco
            Notification::create([
                'user_id' => $user->id,
                'type' => 'email',
                'title' => $subject,
                'message' => $message,
                'data' => $data,
                'channel' => 'email',
                'sent' => true,
                'sent_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send WhatsApp notification (simulado)
     */
    public function sendWhatsApp(User $user, string $message, array $data = [])
    {
        try {
            // Simulação de envio WhatsApp
            // Em produção, integrar com API do WhatsApp Business
            $phone = $user->whatsapp ?? $user->profile->phone ?? null;
            
            if (!$phone) {
                return false;
            }
            
            // Criar notificação no banco
            Notification::create([
                'user_id' => $user->id,
                'type' => 'whatsapp',
                'title' => 'Notificação WhatsApp',
                'message' => $message,
                'data' => array_merge($data, ['phone' => $phone]),
                'channel' => 'whatsapp',
                'sent' => true,
                'sent_at' => now(),
            ]);
            
            Log::info("WhatsApp enviado para {$phone}: {$message}");
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send both email and WhatsApp
     */
    public function sendBoth(User $user, string $subject, string $message, array $data = [])
    {
        $emailSent = $this->sendEmail($user, $subject, $message, $data);
        $whatsappSent = $this->sendWhatsApp($user, $message, $data);
        
        return $emailSent || $whatsappSent;
    }
    
    /**
     * Notify offer owner about new interest
     */
    public function notifyOfferOwner(User $owner, $offer, User $interested)
    {
        $subject = "Novo interesse na sua oferta";
        $message = "O usuário {$interested->name} demonstrou interesse na sua oferta: {$offer->title}";
        
        return $this->sendBoth($owner, $subject, $message, [
            'offer_id' => $offer->id,
            'interested_user_id' => $interested->id,
        ]);
    }
    
    /**
     * Notify about payment due
     */
    public function notifyPaymentDue(User $user, $transaction, $hoursRemaining = 12)
    {
        $subject = "Pagamento pendente - {$hoursRemaining}h restantes";
        $message = "Você tem {$hoursRemaining} horas para completar o pagamento da transação #{$transaction->id}";
        
        return $this->sendBoth($user, $subject, $message, [
            'transaction_id' => $transaction->id,
            'hours_remaining' => $hoursRemaining,
        ]);
    }
    
    /**
     * Notify about authorization needed
     */
    public function notifyAuthorizationNeeded(User $user, $transaction)
    {
        $subject = "Autorização necessária - Prazo de 12h";
        $message = "Você precisa enviar a autorização e vídeo selfie em até 12 horas para a transação #{$transaction->id}";
        
        return $this->sendBoth($user, $subject, $message, [
            'transaction_id' => $transaction->id,
        ]);
    }
    
    /**
     * Notify admin about new offer matching wishlist
     */
    public function notifyAdminAboutMatch($wishlistRequest, $offer)
    {
        $admin = User::where('is_admin', true)->first();
        
        if (!$admin) {
            return false;
        }
        
        $subject = "Nova oferta corresponde a solicitação";
        $message = "Uma nova oferta corresponde à solicitação do usuário {$wishlistRequest->user->name}";
        
        return $this->sendEmail($admin, $subject, $message, [
            'wishlist_request_id' => $wishlistRequest->id,
            'offer_id' => $offer->id,
        ]);
    }
    
    /**
     * Enviar alertas para usuários sobre nova oferta de troca
     */
    public function sendExchangeAlerts($exchangeOffer)
    {
        try {
            // Buscar usuários que podem estar interessados na oferta
            $query = User::whereHas('quotas', function($q) use ($exchangeOffer) {
                $q->where('status', 'available')
                  ->where('is_exchange', true);
                
                $cities = $exchangeOffer->getCitiesForExchangeAlerts();
                $hotels = $exchangeOffer->getDesiredHotelsList();
                if ($cities !== [] || $hotels !== []) {
                    $q->where(function ($sub) use ($cities, $hotels) {
                        foreach ($cities as $city) {
                            $sub->orWhere('location', 'like', '%' . $city . '%');
                        }
                        foreach ($hotels as $hotelName) {
                            $sub->orWhere('hotel_name', 'like', '%' . $hotelName . '%');
                        }
                    });
                }
            })
            ->where('id', '!=', $exchangeOffer->user_id)
            ->whereHas('profile', function($q) {
                $q->whereNotNull('email');
            });
            
            $interestedUsers = $query->limit(50)->get();
            
            $alertCount = 0;
            foreach ($interestedUsers as $user) {
                $subject = "Nova oferta de troca disponível";
                $message = "Uma nova oferta de troca foi criada que pode interessar você. Verifique na plataforma!";
                
                // Criar notificação
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'type' => 'exchange_alert',
                    'title' => $subject,
                    'message' => $message,
                    'data' => ['exchange_offer_id' => $exchangeOffer->id],
                    'channel' => 'in_app',
                    'sent' => true,
                    'sent_at' => now(),
                ]);
                
                $alertCount++;
            }
            
            // Registrar notificação para o criador da oferta
            Notification::create([
                'user_id' => $exchangeOffer->user_id,
                'type' => 'exchange_alert',
                'title' => 'Alertas enviados',
                'message' => "Foram enviados {$alertCount} alertas sobre sua oferta de troca.",
                'data' => ['exchange_offer_id' => $exchangeOffer->id, 'alerts_sent' => $alertCount],
                'channel' => 'in_app',
                'sent' => true,
                'sent_at' => now(),
            ]);
            
            return $alertCount;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar alertas de troca: ' . $e->getMessage());
            return 0;
        }
    }
}

