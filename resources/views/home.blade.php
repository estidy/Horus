@extends('layouts.public')

@section('title', 'Horus | Construcción, reformas y mantenimiento')

@section('content')
<header class="site-header">
    <a class="brand" href="{{ route('home') }}" aria-label="Horus, inicio">
        <img class="brand-logo" src="{{ asset('images/home/logo.png') }}" alt="Logo Horus" loading="lazy"><span class="brand-mark"><span>CONSTRUCCIONES<span class="brand-dark">.</span></span>
    </a>
    <nav class="main-nav" aria-label="Navegación principal">
        <a href="#servicios">Servicios</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#proyectos">Proyectos</a>
        <a href="#contacto">Contacto</a>
    </nav>
    <a class="button button-dark header-cta" href="#consulta">Solicitar presupuesto <span>↗</span></a>
    <button class="menu-toggle" type="button" aria-label="Abrir menú" onclick="document.querySelector('.main-nav').classList.toggle('nav-open')">☰</button>
</header>

<main>
    <section class="hero">
        <div class="hero-shade"></div>
        <div class="hero-content">
            <p class="eyebrow eyebrow-light"><span class="eyebrow-line"></span> CONSTRUCCIÓN · REFORMAS · MANTENIMIENTO</p>
            <h1>Construimos espacios.<br><span>Cuidamos cada detalle.</span></h1>
            <p class="hero-copy">Soluciones profesionales para tu hogar, comercio o empresa. Planificamos, construimos y mantenemos con compromiso de principio a fin.</p>
            <div class="hero-actions">
                <a class="button button-gold" href="#consulta">Contanos tu proyecto <span>↗</span></a>
                <a class="text-link text-link-light" href="#proyectos">Conocé nuestros trabajos <span>↓</span></a>
            </div>
        </div>
        <div class="hero-caption"><span class="caption-dot"></span> La Plata y alrededores</div>
        <div class="hero-index">01 <span>/</span> 03</div>
    </section>

    <section class="intro section-wrap" id="nosotros">
        <div class="intro-label"><p class="eyebrow"><span class="eyebrow-line"></span> SOBRE HORUS</p><span class="intro-number">01 —</span></div>
        <div class="intro-copy">
            <h2>Tu proyecto merece<br>un equipo <em>que responde.</em></h2>
            <p>En Horus hacemos que las ideas se conviertan en espacios funcionales, duraderos y bien hechos. Somos un equipo de construcción y mantenimiento profesional que trabaja con transparencia, atención personalizada y cuidado por cada terminación.</p>
            <a class="text-link" href="#contacto">Conocé a nuestro equipo <span>↗</span></a>
        </div>
        <div class="intro-note"><strong>01.</strong><p>Un mismo equipo<br>de principio a fin.</p></div>
    </section>

    <section class="services section-wrap" id="servicios">
        <div class="section-heading">
            <div><p class="eyebrow"><span class="eyebrow-line"></span> QUÉ HACEMOS</p><h2>Soluciones pensadas<br><em>para durar.</em></h2></div>
            <p>Desde una reparación puntual hasta una obra integral: nos ocupamos de que todo funcione como debe.</p>
        </div>
        <div class="service-grid">
            <article class="service-card"><span class="service-num">01</span><span class="service-icon">↗</span><h3>Construcción</h3><p>Obras nuevas y ampliaciones con planificación, materiales adecuados y ejecución responsable.</p><a href="#consulta">Consultar servicio <span>↗</span></a></article>
            <article class="service-card"><span class="service-num">02</span><span class="service-icon">⌂</span><h3>Reformas</h3><p>Renovamos ambientes, cocinas, baños y locales para que cada espacio se adapte a vos.</p><a href="#consulta">Consultar servicio <span>↗</span></a></article>
            <article class="service-card"><span class="service-num">03</span><span class="service-icon">✳</span><h3>Mantenimiento</h3><p>Soluciones preventivas y correctivas para hogares, consorcios, comercios y empresas.</p><a href="#consulta">Consultar servicio <span>↗</span></a></article>
            <article class="service-card"><span class="service-num">04</span><span class="service-icon">▤</span><h3>Instalaciones</h3><p>Trabajos de electricidad, plomería, pintura y terminaciones con mano de obra profesional.</p><a href="#consulta">Consultar servicio <span>↗</span></a></article>
        </div>
    </section>

    <section class="projects" id="proyectos">
        <div class="section-wrap">
            <div class="section-heading projects-heading">
                <div><p class="eyebrow eyebrow-light"><span class="eyebrow-line"></span> TRABAJOS REALIZADOS</p><h2>Hechos con oficio.<br><em>Hechos para vos.</em></h2></div>
                <p>Una muestra de espacios que cobraron nueva vida gracias a un trabajo bien hecho.</p>
            </div>
            <div class="project-grid">
                <article class="project-card project-large"><img src="{{ asset('images/home/proyecto-vivienda.jpg') }}" alt="Reforma integral de un ambiente residencial" loading="lazy"><div class="project-overlay"><span>REFORMA INTEGRAL</span><h3>Renovación de vivienda</h3><p>La Plata · Interiorismo y terminaciones</p></div></article>
                <article class="project-card"><img src="{{ asset('images/home/proyecto-ampliacion.jpg') }}" alt="Espacio residencial renovado con materiales cálidos" loading="lazy"><div class="project-overlay"><span>CONSTRUCCIÓN</span><h3>Ampliación residencial</h3><p>La Plata · Obra y acabados</p></div></article>
                <article class="project-card"><img src="{{ asset('images/home/proyecto-interiores.jpg') }}" alt="Cocina renovada con terminaciones profesionales" loading="lazy"><div class="project-overlay"><span>REFACCIONES</span><h3>Renovación de interiores</h3><p>La Plata · Proyecto a medida</p></div></article>
            </div>
            <p class="gallery-note">Imágenes ilustrativas de referencia. Reemplazalas por fotos propias de Horus para mostrar obras verificables.</p>
        </div>
    </section>

    <section class="process section-wrap">
        <div class="process-title"><p class="eyebrow"><span class="eyebrow-line"></span> CÓMO TRABAJAMOS</p><h2>Claro desde<br>el <em>primer día.</em></h2></div>
        <div class="process-steps">
            <article><span>01</span><div><h3>Nos contás</h3><p>Recibimos tu consulta y entendemos qué necesitás hacer.</p></div></article>
            <article><span>02</span><div><h3>Lo planificamos</h3><p>Visitamos, evaluamos y preparamos una propuesta clara.</p></div></article>
            <article><span>03</span><div><h3>Lo hacemos</h3><p>Coordinamos el trabajo y cuidamos cada detalle de la obra.</p></div></article>
        </div>
    </section>

    <section class="contact-section" id="consulta">
        <div class="contact-inner section-wrap">
            <div class="contact-copy">
                <p class="eyebrow eyebrow-light"><span class="eyebrow-line"></span> EMPECEMOS</p>
                <h2>¿Qué tenés<br>en <em>mente?</em></h2>
                <p>Contanos qué necesitás. Te contactamos para conocer el proyecto y preparar una cotización a medida, sin compromiso.</p>
                <div class="contact-detail"><span>⌖</span><div><strong>Estamos en La Plata</strong><p>Calle 4 entre 614 y 615 · CP 1900</p></div></div>
            </div>
            <form class="inquiry-form" method="post" action="{{ route('inquiries.store') }}">
                @csrf
                @if (session('success'))<div class="form-success" role="status">{{ session('success') }}</div>@endif
                @if ($errors->any())<div class="form-error" role="alert">Revisá los campos del formulario e intentá nuevamente.</div>@endif
                <div class="form-row"><label>Nombre y apellido<input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" placeholder="Tu nombre"></label><label>Correo electrónico<input name="email" type="email" value="{{ old('email') }}" required maxlength="180" autocomplete="email" placeholder="tu@email.com"></label></div>
                <div class="form-row"><label>Teléfono <span>(opcional)</span><input name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" placeholder="+54 221 ..."></label><label>¿Qué necesitás?<select name="service" required><option value="" disabled @selected(!old('service'))>Seleccioná un servicio</option>@foreach (['Construcción', 'Reforma', 'Mantenimiento', 'Instalaciones', 'Otro'] as $service)<option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>@endforeach</select></label></div>
                <label>Contanos sobre tu proyecto<textarea name="message" rows="4" required maxlength="3000" placeholder="Qué trabajo necesitás, dónde y cuándo te gustaría hacerlo...">{{ old('message') }}</textarea></label>
                <button class="button button-gold form-submit" type="submit">Enviar consulta <span>↗</span></button>
                <p class="privacy-note">Usaremos tus datos únicamente para responder esta consulta.</p>
            </form>
        </div>
    </section>

    <section class="location section-wrap" id="contacto">
        <div class="location-copy"><p class="eyebrow"><span class="eyebrow-line"></span> ENCONTRANOS</p><h2>De La Plata,<br><em>para La Plata.</em></h2><p>Atendemos La Plata y alrededores. Escribinos para coordinar una visita y evaluar tu proyecto en persona.</p><a class="text-link" href="https://www.google.com/maps/search/?api=1&amp;query=Calle+4+entre+614+y+615%2C+La+Plata%2C+Buenos+Aires" target="_blank" rel="noopener noreferrer">Abrir en Google Maps <span>↗</span></a><address><strong>Horus Construcción y Mantenimiento</strong><br>Calle 4 entre 614 y 615<br>La Plata, Buenos Aires · CP 1900 · Argentina</address></div>
        <div class="map-frame"><iframe title="Mapa de ubicación de Horus en La Plata" src="https://maps.google.com/maps?q=Calle%204%20entre%20614%20y%20615%2C%20La%20Plata%2C%20Argentina&amp;z=15&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><span class="map-label"><span>⌖</span> Horus · La Plata</span></div>
    </section>
</main>

<footer class="site-footer">
    <a class="brand brand-footer" href="{{ route('home') }}"><span>HORUS<span class="brand-light">.</span></span></a>
    <p>Construcción y mantenimiento profesional<br>La Plata, Buenos Aires · Argentina</p>
    <div><a href="#servicios">Servicios</a><a href="#proyectos">Proyectos</a><a href="{{ route('login') }}">Acceso administrador</a></div>
    <small>© {{ date('Y') }} Horus. Todos los derechos reservados.</small>
</footer>
@endsection
