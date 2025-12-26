(function (Drupal, once) {
  Drupal.behaviors.accessibilityControls = {
    attach(context) {

      once('a11y-controls', 'body', context).forEach(() => {

        /* Create toolbar */
        const toolbar = document.createElement('div');
        toolbar.id = 'a11y-toolbar';
        toolbar.innerHTML = `
          <button id="a11y-increase" aria-label="Aumentar tamaño del texto">A+</button>
          <button id="a11y-decrease" aria-label="Disminuir tamaño del texto">A−</button>
          <button id="a11y-contrast" aria-label="Activar alto contraste">◐</button>
        `;
        document.body.appendChild(toolbar);

        /* Font size */
        let fontSize = localStorage.getItem('a11y-font-size') || 100;
        document.documentElement.style.fontSize = fontSize + '%';

        document.getElementById('a11y-increase').addEventListener('click', () => {
          if (fontSize < 150) {
            fontSize = parseInt(fontSize) + 10;
            document.documentElement.style.fontSize = fontSize + '%';
            localStorage.setItem('a11y-font-size', fontSize);
          }
        });

        document.getElementById('a11y-decrease').addEventListener('click', () => {
          if (fontSize > 70) {
            fontSize = parseInt(fontSize) - 10;
            document.documentElement.style.fontSize = fontSize + '%';
            localStorage.setItem('a11y-font-size', fontSize);
          }
        });

        /* Contrast */
        const contrastEnabled = localStorage.getItem('a11y-contrast');
        if (contrastEnabled === 'true') {
          document.body.classList.add('a11y-contrast');
        }

        document.getElementById('a11y-contrast').addEventListener('click', () => {
          document.body.classList.toggle('a11y-contrast');
          localStorage.setItem(
            'a11y-contrast',
            document.body.classList.contains('a11y-contrast')
          );
        });

      });
    }
  };
})(Drupal, once);
