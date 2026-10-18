{{-- Etiquetas de analitica/publicidad (Google Ads, GTM, GA4, Meta Pixel), extraidas de layouts/master.
     Dos posiciones en master, mismo HTML de siempre:
       @include('frontend.partials.analytics-tags')                      (part = head: despues del </head> de los estilos)
       @include('frontend.partials.analytics-tags', ['part' => 'body'])  (noscript de GTM, justo despues de <body>)

     Con el modo prueba de atribucion activo (AttributionTestMode::active(): cookie de prueba valida emitida
     por el Laboratorio de atribucion del admin) NO se carga ninguna de estas etiquetas ni se imprime el stack
     'Google-Ads' (conversiones de compra); en su lugar un stub que evita errores en el JS del sitio y no envia
     nada a Google/Meta. Sin modo prueba el HTML es el de siempre. --}}
@php
    $mdnAnalyticsPart = $part ?? 'head';
    $mdnAnalyticsTestMode = \App\Support\Attribution\AttributionTestMode::active();
@endphp
@if($mdnAnalyticsPart === 'body')
@unless($mdnAnalyticsTestMode)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PX9LXWGR"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
@endunless
@elseif($mdnAnalyticsTestMode)
    <script>window.dataLayer=window.dataLayer||[];window.gtag=function(){};window.fbq=function(){};</script>
@else
    <!-- Google Ads -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16512201966"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-16512201966');
    </script>
    @stack('Google-Ads')

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PX9LXWGR');</script>
    <!-- End Google Tag Manager -->

    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-K8M2ZT5HYK"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-K8M2ZT5HYK');
    </script>
    <!-- End Google Analytics -->

    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1912650302805252');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1912650302805252&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
@endif
