<?php

return [
    'fontDir' => storage_path('fonts/'), // Ruta donde se encuentran las fuentes convertidas
    'fontCache' => storage_path('fonts/'), // Puedes usar la misma carpeta para caché de fuentes
    'defaultPaperSize' => 'letter',
    'enable_fontsubsetting' => false,
    'pdf_backend' => 'CPDF',
    'defaultMediaType' => 'screen',
    'default_font' => 'sans-serif',
    'dpi' => 96,
    'defaultPaperOrientation' => 'portrait',
    'fontHeightRatio' => 1.1,
    'chroot' => realpath(base_path()),
];

