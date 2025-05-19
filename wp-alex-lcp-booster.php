<?php
/**
 * Plugin Name: WP Alex LCP Booster
 * Description: Optimise automatiquement le LCP (img ou background-image) sur chaque page, compatible Blocksy Pro, Elementor Pro, Gutenberg, mutualisé O2Switch/Infomaniak. Interface simple dans Outils > WPA LCP Booster.
 * Version: 3.2.1
 * Author: Alexandre Trocmé
 * License: GPL2+
 */

if (!defined('ABSPATH')) exit;

// --- Injection du script JS LCP Booster ---
add_action('wp_footer', function() { ?>
<script id="wpalex-lcp-booster-auto">
(function(){
  let lcpUrl = null;
  new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
      if (entry.element) {
        if (entry.element.tagName === 'IMG') lcpUrl = entry.element.currentSrc || entry.element.src;
        else {
          const bg = getComputedStyle(entry.element).backgroundImage;
          if (bg && bg !== 'none') {
            const match = bg.match(/url\(["']?(.*?)["']?\)/i);
            if (match && match[1]) lcpUrl = match[1];
          }
        }
        // Ajoute fetchpriority/loading si possible
        if(entry.element.tagName === 'IMG') {
          entry.element.setAttribute('loading', 'eager');
          entry.element.setAttribute('fetchpriority', 'high');
        }
      }
    }
    // Précharge dynamique
    if (lcpUrl && !document.querySelector('link[rel="preload"][href="'+lcpUrl+'"]')) {
      let link = document.createElement('link');
      link.rel = 'preload'; link.as = 'image'; link.href = lcpUrl;
      document.head.appendChild(link);
    }
    // Pour rapport JS -> admin (diagnostic)
    if(window.localStorage) localStorage.setItem('wpalex_lcp_last', lcpUrl || '');
  }).observe({type: 'largest-contentful-paint', buffered: true});
})();
</script>
<?php
});

// --- ADMIN MENU : Outils > WPA LCP Booster ---
add_action('admin_menu', function() {
    add_management_page(
        'LCP Booster',           // Page title (en haut de l’admin)
        'WPA LCP Booster',       // Label affiché dans Outils (ce que tu veux)
        'manage_options',        // Capability
        'wpalex-lcp-booster',    // Slug
        'wpalex_lcp_booster_admin_page'
    );
});

// --- PAGE ADMIN ---
function wpalex_lcp_booster_admin_page() {
    ?>
    <div class="wrap">
        <h1>WP Alex LCP Booster</h1>
        <p>
            Ce plugin optimise automatiquement l’image ou le background LCP (Largest Contentful Paint) sur toutes vos pages.<br>
            <strong>Compatibilité :</strong> Blocksy Pro, Elementor Pro, Gutenberg, O2Switch, Infomaniak, etc.<br>
            <strong>Utilisation :</strong> <em>aucun réglage n’est nécessaire !</em>
        </p>
        <hr>
        <h2>Diagnostic instantané (navigateur actuel)</h2>
        <div id="wpalex-lcp-detect">
            <em>Détection de l’image LCP en cours…</em>
        </div>
        <button id="wpalex-lcp-clear" class="button">Vider la détection locale</button>
        <script>
        // Affichage de l’URL LCP détectée par le JS sur la page visitée (via localStorage)
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('wpalex-lcp-detect');
            var lastLCP = localStorage.getItem('wpalex_lcp_last');
            if(lastLCP) {
                el.innerHTML = "<strong>LCP détecté :</strong><br><code>"+lastLCP+"</code>";
            } else {
                el.innerHTML = "<em>Aucune détection LCP dans ce navigateur.</em>";
            }
            document.getElementById('wpalex-lcp-clear').onclick = function(){
                localStorage.removeItem('wpalex_lcp_last');
                el.innerHTML = "<em>LCP local effacé. Visitez une page du site pour le regénérer.</em>";
            };
        });
        </script>
        <hr>
        <h2>Aide & Support</h2>
        <ul>
            <li>Visitez une page publique de votre site : l’URL de l’image LCP détectée s’affichera ici.</li>
            <li>Pour un audit complet, utilisez Google PageSpeed Insights.</li>
            <li>Besoin de forcer une image ou de logs avancés ? Contactez Alexandre pour les prochaines évolutions !</li>
        </ul>
        <div style="margin:1.8em 0 0.2em 0;font-size:1.09em;text-align:left;">
            <span style="font-size:1.15em;">👤</span>
            <a href="https://wpalex.fr" target="_blank" rel="noopener noreferrer">Alexandre Trocmé – wpalex.fr</a>
        </div>
    </div>
    <?php
}
