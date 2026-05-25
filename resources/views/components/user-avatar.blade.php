@props([
    'profile' => null,
    'user' => null,
    'size' => 88,
    'class' => '',
    'alt' => null,
    'rounded' => 'circle',
])

@php
    $profile = $profile ?? $user?->profile;
    $displayName = $user?->name ?? $profile?->full_name ?? auth()->user()?->name ?? 'Usuário';
    $photoUrl = $profile?->userPhotoDisplayUrl() ?? asset('images/placeholders/user-avatar.svg');
    $altText = $alt ?? ('Foto de ' . $displayName);
    $initials = strtoupper(\Illuminate\Support\Str::substr($displayName, 0, 2));
    $radius = $rounded === 'circle' ? '50%' : ($rounded === 'rounded-4' ? '1rem' : '1rem');
    $sizePx = (int) $size;
@endphp

<div class="user-avatar-wrap d-inline-flex align-items-center justify-content-center {{ $class }}" style="width: {{ $sizePx }}px; height: {{ $sizePx }}px; flex-shrink: 0;">
    <img src="{{ $photoUrl }}" alt="{{ $altText }}"
         class="user-avatar-img"
         style="width: {{ $sizePx }}px; height: {{ $sizePx }}px; object-fit: cover; border-radius: {{ $radius }};"
         loading="lazy"
         onerror="this.classList.add('d-none'); this.nextElementSibling?.classList.remove('d-none');">
    <div class="user-avatar-fallback d-none bg-success-subtle text-success fw-bold d-flex align-items-center justify-content-center"
         style="width: {{ $sizePx }}px; height: {{ $sizePx }}px; border-radius: {{ $radius }};">
        {{ $initials }}
    </div>
</div>
