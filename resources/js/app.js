/**
 * jQuery UMD bundle: when no CommonJS `module` object is present (browser ESM
 * context), jQuery's UMD falls through to its browser-globals branch and sets
 * window.$ / window.jQuery automatically.  A side-effect import is therefore
 * sufficient — no default re-export needed.
 *
 * Plugin imports are listed after jQuery so Rollup inlines them in that order.
 * In the bundled output all code runs synchronously: jQuery globals are set
 * before any plugin IIFE executes.
 */

/* eslint-disable import/no-unresolved */

import '../../public/frontend/js/jquery-3.6.0.min.js';
import '../../public/frontend/js/bootstrap.bundle.min.js';
import '../../public/frontend/js/Font-Awesome.js';
import '../../public/frontend/js/select2.min.js';
import '../../public/frontend/js/slick.min.js';
import '../../public/frontend/js/simplyCountdown.js';
import '../../public/frontend/js/jquery.exzoom.js';
import '../../public/frontend/js/jquery.nice-number.min.js';
import '../../public/frontend/js/jquery.waypoints.min.js';
import '../../public/frontend/js/jquery.countup.min.js';
import '../../public/frontend/js/add_row_custon.js';
import '../../public/frontend/js/multiple-image-video.js';
import '../../public/frontend/js/sticky_sidebar.js';
import '../../public/frontend/js/ranger_jquery-ui.min.js';
import '../../public/frontend/js/ranger_slider.js';
import '../../public/frontend/js/isotope.pkgd.min.js';
import '../../public/frontend/js/venobox.min.js';
import '../../public/frontend/js/jquery.classycountdown.js';
import '../../public/frontend/js/main.js';
