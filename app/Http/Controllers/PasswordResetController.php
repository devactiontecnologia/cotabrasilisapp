<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset email
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Por favor, informe um e-mail válido.',
            'email.exists' => 'Não encontramos uma conta com este e-mail.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['email' => 'Não encontramos uma conta com este e-mail.'])
                ->withInput();
        }

        // Generate reset token
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes(60); // Token válido por 60 minutos

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Send email with PHPMailer
        try {
            $resetUrl = url('/password/reset?token=' . $token . '&email=' . urlencode($request->email));
            
            // Verificar configurações antes de tentar enviar
            $this->validateMailConfiguration();
            
            $this->sendPasswordResetEmail($user->email, $user->name, $resetUrl);

            return redirect()->back()
                ->with('success', 'Enviamos um link de recuperação de senha para o seu e-mail. Verifique sua caixa de entrada.');
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de recuperação de senha: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorMessage = 'Erro ao enviar o e-mail. ';
            
            // Mensagens de erro mais específicas
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'Connection timed out') !== false) {
                $errorMessage .= 'O servidor de e-mail não está respondendo. Verifique as configurações SMTP ou tente novamente mais tarde.';
            } elseif (strpos($e->getMessage(), 'authentication') !== false || strpos($e->getMessage(), 'SMTPAuth') !== false) {
                $errorMessage .= 'Erro de autenticação. Verifique o usuário e senha SMTP.';
            } elseif (strpos($e->getMessage(), 'Could not connect') !== false) {
                $errorMessage .= 'Não foi possível conectar ao servidor SMTP. Verifique o host e porta configurados.';
            } else {
                $errorMessage .= 'Por favor, tente novamente mais tarde ou entre em contato com o suporte.';
            }
            
            return redirect()->back()
                ->withErrors(['email' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Show the reset password form
     */
    public function showResetPassword(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Link inválido ou expirado.']);
        }

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Link inválido ou expirado.']);
        }

        // Check if token is expired (60 minutes)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->copy()->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
            
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Este link expirou. Por favor, solicite um novo link.']);
        }

        // Verify token hash
        if (!Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Link inválido ou expirado.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the password
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:8|confirmed',
            ], [
                'token.required' => 'Token inválido.',
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.email' => 'Por favor, informe um e-mail válido.',
                'email.exists' => 'Não encontramos uma conta com este e-mail.',
                'password.required' => 'O campo senha é obrigatório.',
                'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
                'password.confirmed' => 'As senhas não coincidem.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Erro na validação de redefinição de senha: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['password' => 'Erro ao validar os dados. Por favor, tente novamente.'])
                ->withInput();
        }

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Link inválido ou expirado.']);
        }

        // Check if token is expired
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->copy()->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Este link expirou. Por favor, solicite um novo link.']);
        }

        // Verify token hash
        if (!Hash::check($request->token, $resetRecord->token)) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'Link inválido ou expirado.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            \Log::error('Tentativa de redefinir senha para usuário inexistente: ' . $request->email);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Usuário não encontrado.']);
        }

        try {
            // Atualizar senha
            // Como o modelo User tem cast 'hashed', precisamos desabilitar temporariamente
            // ou usar update() diretamente para evitar double hash
            $hashedPassword = Hash::make($request->password);
            
            // Usar update() diretamente para evitar o cast 'hashed' fazer double hash
            $user->update(['password' => $hashedPassword]);
            
            // Verificar se há campos obrigatórios que precisam ser preenchidos
            if (empty($user->name)) {
                \Log::warning('Usuário sem nome ao redefinir senha: ' . $request->email);
            }

            // Delete the token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            \Log::info('Senha redefinida com sucesso para: ' . $request->email);

            return redirect()->route('login')
                ->with('success', 'Sua senha foi alterada com sucesso! Você já pode fazer login.');
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar senha: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Request data: ' . json_encode($request->except(['password', 'password_confirmation'])));
            
            // Mensagem de erro mais específica
            $errorMessage = 'Erro ao atualizar a senha. ';
            if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage .= 'Erro no banco de dados. Verifique os logs para mais detalhes.';
            } elseif (strpos($e->getMessage(), 'Integrity constraint') !== false) {
                $errorMessage .= 'Violação de integridade. Verifique os dados do usuário.';
            } else {
                $errorMessage .= 'Por favor, tente novamente ou entre em contato com o suporte.';
            }
            
            return redirect()->back()
                ->withErrors(['password' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Validate mail configuration
     */
    private function validateMailConfiguration()
    {
        $required = ['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'];
        $missing = [];
        
        foreach ($required as $key) {
            if (empty(env($key))) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('Configurações de e-mail incompletas. Faltam: ' . implode(', ', $missing));
        }
    }

    /**
     * Send password reset email using PHPMailer
     */
    private function sendPasswordResetEmail($email, $name, $resetUrl)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings com timeouts reduzidos
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port = env('MAIL_PORT', 587);
            $mail->CharSet = 'UTF-8';
            
            // Configurações de timeout para evitar o erro de "Maximum execution time exceeded"
            $mail->Timeout = 15; // Timeout de conexão em segundos (15 segundos - reduzido)
            $mail->SMTPKeepAlive = false; // Não manter conexão aberta
            $mail->SMTPDebug = 0; // Desabilitar debug (0 = off, 2 = client, 3 = client + server)
            
            // Configurações específicas para Hostinger
            // Hostinger geralmente usa porta 587 com TLS ou 465 com SSL
            $mailHost = env('MAIL_HOST', '');
            if (strpos($mailHost, 'hostinger') !== false) {
                // Ajustes específicos para Hostinger
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            } elseif (env('APP_DEBUG', false)) {
                // Desabilitar verificação SSL apenas em desenvolvimento
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            }

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'noreply@cotabrasilis.com.br'), env('MAIL_FROM_NAME', 'Cota Brasilis'));
            $mail->addAddress($email, $name);

            // Embed logo image
            $logoPath = public_path('images/logo/logo.png');
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo', 'logo.png', 'base64', 'image/png');
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - Cota Brasilis';
            
            // Get email template
            $mail->Body = view('emails.password-reset', [
                'name' => $name,
                'resetUrl' => $resetUrl,
                'expiresIn' => 60, // minutes
            ])->render();

            $mail->AltBody = "Olá {$name},\n\nVocê solicitou a recuperação de senha.\n\nClique no link abaixo para redefinir sua senha:\n{$resetUrl}\n\nEste link expira em 60 minutos.\n\nSe você não solicitou esta recuperação, ignore este e-mail.\n\nAtenciosamente,\nEquipe Cota Brasilis";

            // Enviar com timeout adicional
            set_time_limit(60); // Aumentar limite de execução para 60 segundos apenas para este processo
            $mail->send();
            
        } catch (Exception $e) {
            \Log::error('Erro PHPMailer: ' . $mail->ErrorInfo);
            \Log::error('Exceção: ' . $e->getMessage());
            throw new \Exception('Erro ao enviar e-mail. Verifique as configurações SMTP ou tente novamente mais tarde.');
        } finally {
            // Restaurar limite de execução padrão
            set_time_limit(ini_get('max_execution_time'));
        }
    }
}
