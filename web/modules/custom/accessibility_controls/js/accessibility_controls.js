(function (Drupal, once) {
  Drupal.behaviors.accessibilityControls = {
    attach(context) {

      once('a11y-controls', 'body', context).forEach(() => {

        /* ===============================
         * Create toolbar
         * =============================== */
        const toolbar = document.createElement('div');
        toolbar.id = 'a11y-toolbar';
        toolbar.innerHTML = `
          <button id="a11y-increase" aria-label="Aumentar tamaño del texto">A+</button>
          <button id="a11y-decrease" aria-label="Disminuir tamaño del texto">A−</button>
          <button id="a11y-contrast" aria-label="Activar alto contraste">◐</button>
          <button id="a11y-reset" aria-label="Restablecer accesibilidad">⟳</button>
        `;
        document.body.appendChild(toolbar);

        /* ===============================
         * Restore saved state
         * =============================== */
        let fontSize = localStorage.getItem('a11y-font-size');
        if (!fontSize) {
          fontSize = 100;
        }
        document.documentElement.style.fontSize = fontSize + '%';

        if (localStorage.getItem('a11y-contrast') === 'true') {
          document.body.classList.add('a11y-contrast');
        }

        /* ===============================
         * Events
         * =============================== */

        // Increase font
        document.getElementById('a11y-increase').addEventListener('click', () => {
          if (fontSize < 160) {
            fontSize = parseInt(fontSize) + 10;
            document.documentElement.style.fontSize = fontSize + '%';
            localStorage.setItem('a11y-font-size', fontSize);
          }
        });

        // Decrease font
        document.getElementById('a11y-decrease').addEventListener('click', () => {
          if (fontSize > 70) {
            fontSize = parseInt(fontSize) - 10;
            document.documentElement.style.fontSize = fontSize + '%';
            localStorage.setItem('a11y-font-size', fontSize);
          }
        });

        // Toggle contrast
        document.getElementById('a11y-contrast').addEventListener('click', () => {
          const enabled = document.body.classList.toggle('a11y-contrast');
          localStorage.setItem('a11y-contrast', enabled);
        });

        // Reset accessibility
        document.getElementById('a11y-reset').addEventListener('click', () => {
          localStorage.removeItem('a11y-contrast');
          localStorage.removeItem('a11y-font-size');

          document.body.classList.remove('a11y-contrast');
          document.documentElement.style.fontSize = '100%';

          fontSize = 100;
        });

      });
    }
  };
})(Drupal, once);
