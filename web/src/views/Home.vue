<script>
export default {
  name: 'Home',
  data() {
    return {
      peliculas: [
        // {
        //   id: 1,
        //   title: 'Alone',
        //   image: '/assets/images/movie/movie01.jpg',
        //   rating: 88,
        //   ratingPublic: 89,
        //   genre: 'Action'
        // },
        // {
        //   id: 2,
        //   title: 'Mars',
        //   image: '/assets/images/movie/movie02.jpg',
        //   rating: 56,
        //   ratingPublic: 62,
        //   genre: 'Adventure'
        // },
        // {
        //   id: 3,
        //   title: 'Venus',
        //   image: '/assets/images/movie/movie03.jpg',
        //   rating: 78,
        //   ratingPublic: 80,
        //   genre: 'Drama'
        // },
        // {
        //   id: 4,
        //   title: 'Horror Night',
        //   image: '/assets/images/movie/movie04.jpg',
        //   rating: 90,
        //   ratingPublic: 95,
        //   genre: 'Horror'
        // }
      ]
    };
  },
  mounted() {
    window.scrollTo(0, 0);
    this.$axios.get('movies')
      .then(res => {
        // console.log('Movies:', res.data);
        this.peliculas = res.data
        this.$nextTick(() => {
          // Esperamos a que Vue termine de renderizar el DOM
          if (window.$ && typeof window.$('.tab-slider').owlCarousel === 'function') {
            window.$('.tab-slider').owlCarousel({
              loop: true,
              responsiveClass: true,
              nav: false,
              dots: false,
              margin: 30,
              autoplay: true,
              autoplayTimeout: 2000,
              autoplayHoverPause: true,
              responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 2 },
                992: { items: 3 },
                1200: { items: 4 }
              }
            });
          } else {
            console.warn('owlCarousel not found');
          }
        });
      })
      .catch(error => {
        console.error('Error fetching movies:', error);
      });
    this.$nextTick(() => {
      this.initTabs();
    });
  },
  methods: {
    initTabs() {
      if (window.$) {
        window.$('.tab ul.tab-menu li').off('click').on('click', function (g) {
          var tab = window.$(this).closest('.tab');
          var index = window.$(this).closest('li').index();
          tab.find('li').siblings('li').removeClass('active');
          window.$(this).closest('li').addClass('active');
          tab.find('.tab-area').find('div.tab-item').not('div.tab-item:eq(' + index + ')').fadeOut(500);
          tab.find('.tab-area').find('div.tab-item:eq(' + index + ')').fadeIn(500);
          g.preventDefault();
        });
      }
    }
  }
}
</script>

<template>
  <section class="movie-section padding-top padding-bottom">
    <div class="container">
      <div class="tab">
        <div class="section-header-2">
          <div class="left">
            <h2 class="title">Peliculas</h2>
            <p>
              <span class="text-danger">¡Bienvenido!</span> Aquí puedes encontrar las mejores películas de la semana.
            </p>
          </div>
          <ul class="tab-menu">
            <li class="active">
              Cartelera
            </li>
            <li>
              Proximamente
            </li>
            <li>
              Todos
            </li>
          </ul>
        </div>
        <div class="tab-area mb-30-none">
          <div class="tab-item active">
            <div class="owl-carousel owl-theme tab-slider">
              <div class="item" v-for="pelicula in peliculas" :key="pelicula.id">
                <div class="movie-grid">
                  <div class="movie-thumb c-thumb">
                    <router-link :to="`/pelicula/${pelicula.id}`">
                      <img
                          :src="`${$url}../../imagen/${pelicula.imagen}`"
                          :alt="pelicula.nombre"
                          style="width: 100%; height:450px; object-fit: cover;"
                      >
                    </router-link>
                  </div>
                  <div class="movie-content bg-one">
                    <h5 class="title m-0">
                      <a :href="`#${pelicula.nombre}`">
                        {{ $filters.textCapitalize(pelicula.nombre) }}
                        {{pelicula.formato}}
                      </a>
                    </h5>
                    <ul class="movie-rating-percent">
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/tomato.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.ratingCritica? pelicula.ratingCritica : 0 }}%</span>
                      </li>
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/cake.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.ratingPublic? pelicula.ratingPublic : 0 }}%</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-item">
<!--            <div class="owl-carousel owl-theme tab-slider">-->
              <p>
<!--                texto de rrelno que iran las proximas peliculas-->
                <span class="text-danger">¡Bienvenido!</span> Aquí puedes encontrar las mejores películas de la semana.
              </p>
<!--            </div>-->
          </div>
          <div class="tab-item">
            <div class="row">
              <div class="col-md-4 item" v-for="pelicula in peliculas" :key="pelicula.id">
                <div class="movie-grid">
                  <div class="movie-thumb c-thumb">
                    <router-link :to="`/pelicula/${pelicula.id}`">
                      <img
                          :src="`${$url}../../imagen/${pelicula.imagen}`"
                          :alt="pelicula.nombre"
                          style="width: 100%; height:450px; object-fit: cover;"
                      >
                    </router-link>
                  </div>
                  <div class="movie-content bg-one">
                    <h5 class="title m-0">
                      <a :href="`#${pelicula.nombre}`">
                        {{ $filters.textCapitalize(pelicula.nombre) }}
                        {{pelicula.formato}}
                      </a>
                    </h5>
                    <ul class="movie-rating-percent">
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/tomato.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.ratingCritica? pelicula.ratingCritica : 0 }}%</span>
                      </li>
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/cake.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.ratingPublic? pelicula.ratingPublic : 0 }}%</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>