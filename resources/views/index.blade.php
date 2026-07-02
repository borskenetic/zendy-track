<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/d.png') }}">
    <title>Zendy Portal — JIB</title>
    <link rel="stylesheet" href="{{ asset('css/zendy-app.css') }}?v=4">
</head>
<body class="landing-page">

<nav class="landing-nav">
    <img src="{{ asset('images/d.png') }}" alt="JIB Logo">
    <div class="landing-nav-links">
        <a href="{{ route('patron.register') }}" class="ghost">Register</a>
        <a href="{{ route('login') }}" class="solid">Sign in</a>
    </div>
</nav>

<main class="landing-main">
    <section class="landing-hero">
        <div class="landing-hero-content">
            <p class="landing-eyebrow">Joji Ilagan International Schools</p>
            <h1>Research smarter with Zendy</h1>
            <p class="landing-lead">
                Access scholarly articles, journals, and e-books through your institution's portal.
                Sign in to launch Zendy and track your usage.
            </p>
            <div class="landing-cta">
                <a href="{{ route('login') }}" class="btn-app btn-primary-app landing-btn-primary">Sign in to Zendy</a>
                <a href="{{ route('patron.register') }}" class="btn-app btn-outline-app landing-btn-secondary">Create account</a>
            </div>
        </div>

        <div class="landing-video-panel">
            <video autoplay muted loop playsinline>
                <source src="{{ asset('videos/library-bg.mp4') }}" type="video/mp4">
            </video>
        </div>
    </section>

    <div class="landing-features">
            <div class="landing-feature">
                <span class="landing-feature-icon">📚</span>
                <h3>Millions of publications</h3>
                <p>Search journals, articles, and e-books in one place.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-icon">🔐</span>
                <h3>Institutional access</h3>
                <p>Log in with your school account to get started.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-icon">📊</span>
                <h3>Library reports</h3>
                <p>Helps your library understand how research resources are used on campus.</p>
            </div>
        </div>
</main>

<footer class="landing-footer">
    <p>&copy; {{ date('Y') }} Joji Ilagan International Schools</p>
</footer>

</body>
</html>
