{{-- JS compartido por las landings de Brasil: galería y carrusel. --}}
<script>
    // Galeria: la miniatura pulsada pasa a ser la imagen principal.
    (function () {
        var principal = document.getElementById('br-img-principal');
        var miniaturas = document.querySelectorAll('.br-thumb');
        if (!principal || !miniaturas.length) return;
        miniaturas.forEach(function (m) {
            m.addEventListener('click', function () {
                principal.src = m.getAttribute('data-src');
                miniaturas.forEach(function (x) { x.classList.remove('is-active'); });
                m.classList.add('is-active');
            });
        });
    })();

    // Carrusel de relacionados: los botones desplazan una tarjeta.
    (function () {
        document.querySelectorAll('.br-rel').forEach(function (rel) {
            var pista = rel.querySelector('.br-rel-track');
            if (!pista) return;
            var paso = function () { var c = pista.querySelector('.br-rel-card'); return c ? c.getBoundingClientRect().width + 16 : 300; };
            rel.querySelector('[data-dir="prev"]').addEventListener('click', function () { pista.scrollBy({ left: -paso() }); });
            rel.querySelector('[data-dir="next"]').addEventListener('click', function () { pista.scrollBy({ left: paso() }); });
        });
    })();
</script>
