# Configuração do Módulo de Recuperação de Senha

## 📋 Visão Geral

O módulo de recuperação de senha foi implementado com sucesso usando PHPMailer para envio de e-mails. Este documento descreve como configurar e usar o sistema.

## 🔧 Configuração do E-mail (PHPMailer)

Para que o sistema funcione corretamente, você precisa configurar as variáveis de ambiente no arquivo `.env`:

```env
# Configurações de E-mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-de-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cotabrasilis.com.br
MAIL_FROM_NAME="Cota Brasilis"
```

### 📧 Configuração para Gmail

Se você estiver usando Gmail, siga estes passos:

1. **Ative a verificação em duas etapas** na sua conta Google
2. **Gere uma senha de app**:
   - Acesse: https://myaccount.google.com/apppasswords
   - Selecione "App" e "Outro (nome personalizado)"
   - Digite "Cota Brasilis" e clique em "Gerar"
   - Copie a senha gerada (16 caracteres)
   - Use essa senha no `MAIL_PASSWORD` do `.env`

### 📧 Configuração para outros provedores

#### Outlook/Hotmail:
```env
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

#### Yahoo:
```env
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

#### Hostinger:
```env
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=seu-email@seudominio.com.br
MAIL_PASSWORD=sua-senha-de-email
MAIL_FROM_ADDRESS=seu-email@seudominio.com.br
MAIL_FROM_NAME="Cota Brasilis"
```

**Nota importante para Hostinger:**
- Use o e-mail completo (com domínio) no `MAIL_USERNAME` e `MAIL_FROM_ADDRESS`
- A senha deve ser a senha do e-mail criado no painel da Hostinger
- Porta 587 com TLS é a configuração padrão
- Se a porta 587 não funcionar, tente 465 com SSL

#### Servidor SMTP próprio:
```env
MAIL_HOST=seu-servidor-smtp.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
# ou
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

## 🛣️ Rotas Implementadas

- `GET /password/forgot` - Exibe o formulário de solicitação de recuperação
- `POST /password/email` - Envia o e-mail de recuperação
- `GET /password/reset` - Exibe o formulário de redefinição de senha
- `POST /password/reset` - Processa a redefinição de senha

## 📁 Arquivos Criados

1. **Controller**: `app/Http/Controllers/PasswordResetController.php`
   - Gerencia todo o fluxo de recuperação de senha
   - Usa PHPMailer para envio de e-mails

2. **Views**:
   - `resources/views/auth/forgot-password.blade.php` - Formulário de solicitação
   - `resources/views/auth/reset-password.blade.php` - Formulário de redefinição
   - `resources/views/emails/password-reset.blade.php` - Template do e-mail

## ✨ Funcionalidades

- ✅ Solicitação de recuperação via e-mail
- ✅ Token seguro com expiração de 60 minutos
- ✅ Template de e-mail responsivo e bonito
- ✅ Validação completa de formulários
- ✅ Mensagens de erro e sucesso amigáveis
- ✅ Integração com PHPMailer
- ✅ Link funcional no e-mail de recuperação

## 🔒 Segurança

- Tokens são hasheados antes de serem armazenados no banco
- Tokens expiram automaticamente após 60 minutos
- Tokens são deletados após uso bem-sucedido
- Validação de senha com mínimo de 8 caracteres
- Confirmação de senha obrigatória

## 🧪 Testando

1. Acesse `/password/forgot`
2. Digite um e-mail cadastrado no sistema
3. Verifique sua caixa de entrada (e spam)
4. Clique no link do e-mail
5. Defina uma nova senha
6. Faça login com a nova senha

## ⚠️ Troubleshooting

### Erro "Maximum execution time of 120 seconds exceeded"

Este erro ocorre quando o PHPMailer não consegue conectar ao servidor SMTP dentro do tempo limite. **Soluções:**

1. **Verifique as configurações SMTP no `.env`:**
   ```env
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=seu-email-completo@seudominio.com.br
   MAIL_PASSWORD=sua-senha-correta
   ```

2. **Teste a conectividade:**
   - Verifique se o servidor SMTP está acessível
   - Teste se a porta não está bloqueada pelo firewall
   - Para Hostinger, certifique-se de usar o e-mail completo (com domínio)

3. **Verifique as credenciais:**
   - O `MAIL_USERNAME` deve ser o e-mail completo (ex: `contato@cotabrasilis.com.br`)
   - O `MAIL_PASSWORD` deve ser a senha do e-mail criado no painel da Hostinger
   - Não use senhas de app ou tokens, use a senha real do e-mail

4. **Tente portas alternativas:**
   - Porta 587 com TLS (recomendado)
   - Porta 465 com SSL (alternativa)

5. **Verifique os logs:**
   - Consulte `storage/logs/laravel.log` para detalhes do erro
   - Procure por mensagens relacionadas a "SMTP", "Connection", "timeout"

### E-mail não está sendo enviado

1. Verifique as credenciais no `.env`
2. Verifique os logs em `storage/logs/laravel.log`
3. Teste a conexão SMTP manualmente
4. Verifique se o firewall não está bloqueando a porta SMTP
5. Certifique-se de que todas as variáveis estão preenchidas corretamente

### Token inválido ou expirado

- Tokens expiram em 60 minutos
- Cada token só pode ser usado uma vez
- Solicite um novo link se necessário

### Erro "Call to undefined method"

- Execute `composer dump-autoload`
- Verifique se o PHPMailer foi instalado corretamente

## 📝 Notas Importantes

- O sistema usa a tabela `password_reset_tokens` que já existe no banco de dados
- O template de e-mail é totalmente responsivo
- O link de recuperação contém o token e o e-mail como parâmetros
- Após redefinir a senha, o token é automaticamente deletado
