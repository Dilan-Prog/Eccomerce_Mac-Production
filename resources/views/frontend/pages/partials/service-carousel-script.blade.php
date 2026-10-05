{{--
    JS de los carruseles de servicio. Se incluye UNA vez por página dentro de
    @push('scripts'); recorre todos los .cps-carousel--svc que haya.

    Es el mismo scrollBy del carrusel de productos del inicio, más tres cosas
    que allá no hacen falta: avance automático (el negocio quiere que las fotos
    vayan pasando solas), vuelta al principio al llegar al final, y los puntos
    de posición. El avance se detiene al pasar el ratón, al tocar o al usar el
    teclado, para no pelearse con el usuario.
--}}
<script>
(function () {
    var INTERVALO = 5000;

    document.querySelectorAll('.cps-carousel--svc').forEach(function (carrusel) {
        var pista = carrusel.querySelector('.cps-track');
        if (!pista) return;

        var laminas = pista.querySelectorAll('.cps-slide');
        var puntos = carrusel.querySelectorAll('.cps-dot');
        var anterior = carrusel.querySelector('.cps-arrow--prev');
        var siguiente = carrusel.querySelector('.cps-arrow--next');
        if (laminas.length < 2) return;

        function paso() {
            return laminas[0].getBoundingClientRect().width;
        }

        function indice() {
            return Math.round(pista.scrollLeft / Math.max(paso(), 1));
        }

        function irA(i) {
            pista.scrollTo({ left: i * paso(), behavior: 'smooth' });
        }

        function mover(dir) {
            var i = indice() + dir;
            // Cicla en los dos sentidos: el carrusel nunca se queda topado.
            if (i >= laminas.length) i = 0;
            if (i < 0) i = laminas.length - 1;
            irA(i);
        }

        function pintarPuntos() {
            var i = indice();
            puntos.forEach(function (p, j) { p.classList.toggle('is-active', j === i); });
        }

        if (anterior) anterior.addEventListener('click', function () { mover(-1); reiniciar(); });
        if (siguiente) siguiente.addEventListener('click', function () { mover(1); reiniciar(); });
        pista.addEventListener('scroll', pintarPuntos, { passive: true });

        // ── Avance automático ──
        var reloj = null;
        function arrancar() {
            if (reloj) return;
            reloj = setInterval(function () { mover(1); }, INTERVALO);
        }
        function parar() {
            clearInterval(reloj);
            reloj = null;
        }
        function reiniciar() { parar(); arrancar(); }

        ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (evento) {
            carrusel.addEventListener(evento, parar, { passive: true });
        });
        ['mouseleave', 'focusout'].forEach(function (evento) {
            carrusel.addEventListener(evento, arrancar);
        });

        // Con la pestaña en segundo plano no tiene sentido seguir girando.
        document.addEventListener('visibilitychange', function () {
            document.hidden ? parar() : arrancar();
        });

        // Respeta a quien pidió menos movimiento en su sistema.
        var quietud = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (!quietud.matches) arrancar();
    });
})();
</script>
