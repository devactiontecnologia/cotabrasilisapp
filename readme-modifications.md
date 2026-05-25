# Histórico de modificações — Cota Brasilis

Documento de referência com as alterações feitas no painel administrativo, páginas institucionais, rodapé e área pública. Última atualização: **abril de 2026**.

---

## 1. Perguntas frequentes (FAQ) — módulo dedicado

### Banco de dados
- Tabela **`faqs`**: `question`, `answer` (HTML), `sort_order`, `is_active`, timestamps.
- Migração que **remove** o registro `SitePage` com slug `perguntas-frequentes` (conteúdo institucional antigo).

### Backend
- Modelo **`App\Models\Faq`** com escopos `active()` e `ordered()`.
- **`AdminFaqController`**: CRUD (sem `show`), validação de campos.
- **`WelcomeController::faq()`**: carrega FAQs ativas e ordenadas para a view pública.
- **`SitePageController`**: redirecionamento **301** de `/institucional/perguntas-frequentes` para a rota nomeada **`faq`**.

### Rotas
- Público: `GET /perguntas-frequentes` → `faq` (inalterado conceitualmente).
- Admin: `Route::resource('faqs', AdminFaqController::class)->except(['show']);` sob prefixo `admin`.

### Painel admin
- Item na sidebar: **Perguntas frequentes**.
- Lista, **Cadastrar nova**, edição e exclusão.
- Campo **resposta** com editor rico (**TinyMCE** em modo GPL, carregado via CDN), configurado em `resources/views/admin/partials/tinymce-init.blade.php` (reutilizado também em Informações do site).

### Informações do site (lista)
- Slug `perguntas-frequentes` **excluído** da listagem em `AdminSiteInformationController` (não aparece mais como página editável nesse hub).

### Front público
- View **`resources/views/faq.blade.php`**: acordeão Bootstrap, estilo com verde da marca (`--primary-green` / `#009739`) no cabeçalho ativo.
- Partial **`faq/partials/item.blade.php`**: renderiza HTML da resposta com `{!! !!}`.

### Rodapé / links
- Link “Perguntas Frequentes…” no layout principal aponta para **`route('faq')`** em vez de `site.page` com slug antigo.

---

## 2. Informações do site — novo layout e editor

### Lista (hub)
- Substituído o layout em **abas/lista lateral** por **cards por categoria** (Plataforma, Recursos, Painel de controle, Legal).
- Botões verdes com ícones (**Bootstrap Icons**), mapeados em **`SitePage::adminIconForSlug()`** em `app/Models/SitePage.php`.

### Edição por página
- Nova rota: **`GET admin/site-information/{sitePage}/edit`**.
- View **`resources/views/admin/site-information/edit.blade.php`**: formulário com título + corpo HTML via **TinyMCE** (mesmo partial `tinymce-init`), `form` com `id="site_info_form"`.
- Após salvar, redirect para a **própria edição** com mensagem de sucesso.

### Arquivos principais
- `resources/views/admin/site-information/index.blade.php` — cards e botões.
- `resources/views/admin/site-information/edit.blade.php` — edição.
- `app/Http/Controllers/AdminSiteInformationController.php` — método `edit()` e redirect do `update`.
- `routes/web.php` — rota `edit`.

---

## 3. Páginas institucionais públicas (`/institucional/{slug}`)

### View
- **`resources/views/site/page.blade.php`**: estrutura com `section.site-page-institutional`, card, breadcrumb, título e corpo.

### Estilos (`public/css/custom.css`)
- Bloco **“Páginas institucionais do rodapé”**: fundo em gradiente, card com faixa superior verde, tipografia do corpo, títulos e links em verde da marca, blockquote, tabelas, imagens.
- FAQ (`/perguntas-frequentes`) usa **outro** template — não depende dessas classes.

### Títulos globais no site (layout principal)
- Em **`:root`**: `--bs-heading-color` alinhado ao verde da marca.
- Regras para **`main`** `h1`–`h6` e `.display-*` em verde, com exceções (alertas, sidebar do cliente, etc.).
- **`.btn-success`** ajustado para o mesmo verde de marca (`#009739`).

---

## 4. Rodapé — bloco “Painel de controle”

### Removido (4 páginas institucionais antigas)
- Visão geral do painel (`painel-visao-geral`)
- Minhas cotas (`painel-minhas-cotas`)
- Reservas e transações (`painel-transacoes`)
- Favoritos e desejados (`painel-favoritos-desejados`)

### Incluído (12 páginas novas), mesma URL padrão `/institucional/{slug}`
| Slug | Título |
|------|--------|
| `painel-de-controle` | Painel de controle |
| `cadastrar-nova-cota` | Cadastrar nova cota |
| `aluguel` | Aluguel |
| `troca` | Troca |
| `compra` | Compra |
| `venda` | Venda |
| `favoritos` | Favoritos |
| `desejados` | Desejados |
| `bora-la-cota-brasilis` | Bora lá! Cota Brasilis |
| `conteudo-educativo` | Conteúdo educativo |
| `termo-de-autorizacao` | Termo de autorização |
| `meu-perfil` | Meu perfil |

### Implementação
- Migração **`2026_04_03_120000_replace_painel_site_pages.php`**: remove os 4 slugs antigos e cria os 12 novos (com corpo placeholder).
- **`database/seeders/SitePageSeeder.php`**: mesma lista para instalações novas.
- **`SitePage::adminIconForSlug()`**: ícones para cada novo slug.
- **`resources/views/layouts/app.blade.php`**: coluna **PAINEL DE CONTROLE** atualizada; ajuste de grid (ex.: RECURSOS `col-lg-2`, PAINEL `col-lg-2`, LEGAL `col-lg-3`) para somar 12 colunas.

**Nota:** “Termo de autorização” (Painel) é página distinta dos **Termos de Autorização** do bloco Legal (`termos-autorizacao`).

---

## 5. Rodapé — espaçamento entre colunas PAINEL e LEGAL

- Classes utilitárias no HTML: `footer-col-painel-legal-gap` e `footer-col-legal-painel-gap`.
- Em **`layouts/app.blade.php`** (CSS inline da página): em `min-width: 992px`, `padding` lateral reduzido entre essas duas colunas (**0,4rem** cada lado), alinhado ao par RECURSOS ↔ PAINEL.
- Larguras de coluna ajustadas para reduzir “vazio” visual entre PAINEL e LEGAL (PAINEL mais estreito, LEGAL um pouco mais largo).

---

## 6. Layout administrativo — classe no `<body>`

- **`resources/views/admin/layout.blade.php`**: `<body class="@yield('body-class')">`.
- Permite estilizar título da página e conteúdo por tela sem afetar todo o admin.

---

## 7. Conteúdo educativo — hub (`/admin/educational`)

- **`resources/views/admin/educational/index.blade.php`**: layout redesenhado (hero, cards com hover, badges de contagem, botões em gradiente, suporte a tema escuro, AOS).
- **`@section('body-class', 'admin-educational-hub')`** + CSS em `@push('styles')`: título **“Conteúdo educativo”** no header em verde **`#009739`**.

---

## Arquivos-chave (referência rápida)

| Área | Arquivos |
|------|-----------|
| FAQ | `app/Models/Faq.php`, `AdminFaqController`, `routes/web.php`, `resources/views/faq.blade.php`, `admin/faq/*`, `database/migrations/*faqs*` |
| Site pages | `SitePage`, `SitePageController`, `resources/views/site/page.blade.php`, `AdminSiteInformationController`, `admin/site-information/*` |
| CSS global / institucional | `public/css/custom.css` |
| Rodapé | `resources/views/layouts/app.blade.php` |
| Painel SitePage slugs | `database/migrations/2026_04_03_120000_replace_painel_site_pages.php`, `SitePageSeeder.php` |
| TinyMCE compartilhado | `resources/views/admin/partials/tinymce-init.blade.php` |
| Admin educational hub | `resources/views/admin/educational/index.blade.php`, `admin/layout.blade.php` |

---

## Comandos úteis após deploy

```bash
php artisan migrate --force
# Se necessário repopular só páginas (cuidado em produção):
# php artisan db:seed --class=SitePageSeeder
```

---

## Observações

- URLs antigas `/institucional/painel-visao-geral` (e demais slugs removidos) **deixam de existir**; configure redirecionamentos no servidor se necessário.
- Conteúdo antigo da FAQ estava em view estática; migração para BD exige **cadastro no admin** (ou importação manual).
- Cache de views/CSS: após alterações, use hard refresh (**Ctrl+F5**) ou `php artisan view:clear` quando aplicável.
