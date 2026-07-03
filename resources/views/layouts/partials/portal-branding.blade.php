@php
    $colors = \App\Support\Branding::colors();
@endphp
<style id="portal-branding">
:root {
    --primary: {{ $colors['primary'] }};
    --primary-hover: {{ $colors['primary_hover'] }};
    --sidebar-bg: {{ $colors['sidebar_bg'] }};
    --sidebar-bg-hover: {{ $colors['sidebar_bg_hover'] }};
    --sidebar-border: {{ $colors['sidebar_border'] }};
    --sidebar-text: {{ $colors['sidebar_text'] }};
    --sidebar-text-muted: {{ $colors['sidebar_text_muted'] }};
    --sidebar-active: {{ $colors['primary'] }};
    --sidebar-active-bg: {{ \App\Support\Branding::primaryRgba(0.18) }};
    --sidebar-active-text: {{ \App\Support\Branding::primaryLight() }};
    --body-bg: {{ $colors['body_bg'] }};
    --surface: {{ $colors['surface'] }};
    --text: {{ $colors['text'] }};
    --text-muted: {{ $colors['text_muted'] }};
    --border: {{ $colors['border'] }};
    --success: {{ $colors['success'] }};
    --warning: {{ $colors['warning'] }};
    --danger: {{ $colors['danger'] }};
}
</style>
@if ($brandingCss = \App\Support\Branding::stylesheetUrl())
    <link rel="stylesheet" href="{{ $brandingCss }}">
@endif
