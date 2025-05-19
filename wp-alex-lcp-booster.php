<?php
/**
 * Plugin Name: WP Alex LCP Booster
 * Description: Détection automatique et optimisation du LCP (img ou background-image) sur chaque page, multisite, compatible tous thèmes et builders (Elementor, Blocksy, etc).
 * Version: 3.0
 * Author: Alexandre Trocmé
 * License: GPL2+
 */

if (!defined('ABSPATH')) exit;

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
  }).observe({type: 'largest-contentful-paint', buffered: true});
})();
</script>
<?php
});
