<script>
export default {
  name: 'Home',
  data() {
    return {
      peliculas: [
        {
          id: 1,
          title: 'Alone',
          image: '/assets/images/movie/movie01.jpg',
          rating: 88,
          ratingPublic: 89,
          genre: 'Action'
        },
        {
          id: 2,
          title: 'Mars',
          image: '/assets/images/movie/movie02.jpg',
          rating: 56,
          ratingPublic: 62,
          genre: 'Adventure'
        },
        {
          id: 3,
          title: 'Venus',
          image: '/assets/images/movie/movie03.jpg',
          rating: 78,
          ratingPublic: 80,
          genre: 'Drama'
        },
        {
          id: 4,
          title: 'Horror Night',
          image: '/assets/images/movie/movie04.jpg',
          rating: 90,
          ratingPublic: 95,
          genre: 'Horror'
        }
      ]
    };
  },
  mounted() {
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
  }
}
</script>

<template>
  <section class="movie-section padding-top padding-bottom">
    <div class="container">
      <div class="tab">
        <div class="section-header-2">
          <div class="left">
            <h2 class="title">movies</h2>
            <p>Be sure not to miss these Movies today.</p>
          </div>
          <ul class="tab-menu">
            <li class="active">
              now showing
            </li>
            <li>
              coming soon
            </li>
            <li>
              exclusive
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
                      <img :src="pelicula.image" :alt="pelicula.title">
                    </router-link>
                  </div>
                  <div class="movie-content bg-one">
                    <h5 class="title m-0">
                      <a :href="`#${pelicula.title}`">{{ pelicula.title }}</a>
                    </h5>
                    <ul class="movie-rating-percent">
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/tomato.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.rating }}%</span>
                      </li>
                      <li>
                        <div class="thumb">
                          <img src="/assets/images/movie/cake.png" alt="movie">
                        </div>
                        <span class="content">{{ pelicula.ratingPublic }}%</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
<!--              <pre>{{peliculas}}</pre>-->
            </div>
          </div>
          <div class="tab-item">
            <div class="owl-carousel owl-theme tab-slider">
              aaaaaaaaaaaaaaa
            </div>
          </div>
          <div class="tab-item">
            <div class="owl-carousel owl-theme tab-slider">
              bbbbbbbbbbbbbbbbbb
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>