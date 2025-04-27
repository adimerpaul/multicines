<template>
  <!-- ==========Sección de Boletín========== -->
  <footer class="footer-section">
    <div class="newslater-section padding-bottom">
      <div class="container">
        <div class="newslater-container bg_img" data-background="/assets/images/newslater/newslater-bg01.jpg">
          <div class="newslater-wrapper">
            <h5 class="cate">Suscríbete a Multicines PLAZA</h5>
            <h3 class="title">y obtén beneficios exclusivos</h3>
            <form class="newslater-form">
              <input type="text" placeholder="Tu correo electrónico">
              <button type="submit">Suscribirse</button>
            </form>
            <p>Respetamos tu privacidad, nunca compartiremos tu información.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="footer-top">
        <div class="logo">
          <a href="/">
            <img src="/assets/images/footer/footer-logo.png" alt="footer">
          </a>
        </div>
        <ul class="social-icons">
          <li>
            <a href="https://www.facebook.com/MultiCinesPLAZA" target="_blank" rel="noopener noreferrer">
              <i class="fab fa-facebook-f"></i>
            </a>
          </li>
          <li>
            <a href="#" class="disabled">
              <i class="fab fa-twitter"></i>
            </a>
          </li>
          <li>
            <a href="#" class="disabled">
              <i class="fab fa-pinterest-p"></i>
            </a>
          </li>
          <li>
            <a href="#" class="disabled">
              <i class="fab fa-google"></i>
            </a>
          </li>
          <li>
            <a href="#" class="disabled">
              <i class="fab fa-instagram"></i>
            </a>
          </li>
        </ul>
      </div>
      <div class="footer-bottom">
        <div class="footer-bottom-area">
          <div class="left">
            <p>Copyright © {{ new Date().getFullYear() }}. Todos los derechos reservados por <a href="/">Multicines PLAZA</a></p>
          </div>
          <ul class="links">
            <li>
              <a href="#0">Acerca de</a>
            </li>
            <li>
              <a href="#0">Términos de uso</a>
            </li>
            <li>
              <a href="#0">Política de privacidad</a>
            </li>
            <li>
              <a href="#0">Preguntas frecuentes</a>
            </li>
            <li>
              <a href="#0">Sugerencias</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>
  <!-- ==========Fin Sección de Boletín========== -->
</template>

<script>
export default {
  name: 'Footer'
};
</script>
