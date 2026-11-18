@extends('layouts.public')

@section('title', 'Acceso administrador | Horus')

@section('content')
<main class="login-page">
    <a class="brand login-brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('images/home/logo.png') }}" alt="Horus, inicio"> <span class="brand-mark"><span>CONSTRUCCIONES<span class="brand-dark">.</span></span></a>
    <section class="login-card">
        <p class="eyebrow"><span class="eyebrow-line"></span> ÁREA DE ADMINISTRACIÓN</p>
        <h1>Bienvenido.</h1>
        <p class="login-intro">Ingresá para administrar clientes y presupuestos.</p>
        @if ($errors->any())<div class="form-error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <label>Correo electrónico<input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
            <label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label>
            <label class="remember-label"><input type="checkbox" name="remember" value="1"> Recordarme</label>
            <button class="button button-dark form-submit" type="submit">Ingresar <span>↗</span></button>
        </form>
        <a class="back-link" href="{{ route('home') }}">← Volver al sitio</a>
    </section>
</main>
@endsection
