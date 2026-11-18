<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administración | Horus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/horus.css') }}?v={{ filemtime(public_path('css/horus.css')) }}">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
    <a class="brand admin-brand" href="{{ route('admin.dashboard') }}"><span>HORUS<span class="brand-light">.</span></span></a>
    <p class="sidebar-caption">GESTIÓN</p>
    <nav class="sidebar-nav">
        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>▦</span> Resumen</a>
        <a class="{{ request()->routeIs('admin.presupuestos.*') ? 'active' : '' }}" href="{{ route('admin.presupuestos.index') }}"><span>▤</span> Presupuestos</a>
        <a class="{{ request()->routeIs('admin.clientes.*') ? 'active' : '' }}" href="{{ route('admin.clientes.index') }}"><span>♙</span> Clientes</a>
    </nav>
    <div class="sidebar-bottom">
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">↗ Ver sitio público</a>
        <form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit">↪ Cerrar sesión</button></form>
        <div class="admin-user"><span class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>Administrador</small></div></div>
    </div>
</aside>
<main class="admin-main">
    <header class="admin-topbar"><span>HORUS <span>/</span> @yield('breadcrumb', 'Resumen')</span><span class="topbar-date">{{ now()->locale('es')->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</span></header>
    <div class="admin-content">
        @if (session('success'))<div class="alert-success" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="alert-error" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @yield('content')
    </div>
</main>
</body>
</html>
