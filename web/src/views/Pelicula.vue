<script>
export default {
  name: 'Pelicula',
  data() {
    return {
      pelicula: {},
      ofertas: [],
    };
  },
  mounted() {
    this.movieGet()
    window.scrollTo(0, 0);
    // console.log(this.$route.params.id)
  },
  methods: {
    async movieGet() {
      try {
        const response = await this.$axios.get('movie/' + this.$route.params.id);
        // {
        //   movie: {
        //     id: 357,
        //         nombre: "EL SEÑOR DE LOS ANILLOS: EL RETORNO DEL REY",
        //         duracion: 215,
        //         paisOrigen: "EEUU",
        //         genero: "Accion",
        //         sipnosis: "Su historia se desarrolla en la Tercera Edad del Sol de la Tierra Media, un lugar ficticio poblado por hombres y otras razas antropomorfas, como los hobbits, los elfos o los enanos, así como por muchas otras criaturas reales y fantásticas. La novela narra",
        //         formato: "2D",
        //         urlTrailer: "https://www.youtube.com/watch?v=mZYms2vsm2Y&ab_channel=xeofain",
        //         imagen: "1745655578.jpg",
        //         clasificacion: "+13",
        //         fechaEstreno: "2025-03-27",
        //         distributor_id: 1,
        //         ratingCritica: 89,
        //         ratingPublic: 90,
        //         programas: [
        //       {
        //         id: 41360,
        //         fecha: "2025-04-27",
        //         horaInicio: "2025-04-27 10:00:00",
        //         horaFin: "2025-04-27 13:35:00",
        //         subtitulada: "NO",
        //         activo: "ACTIVO",
        //         nroFuncion: "1",
        //         user_id: null,
        //         movie_id: 357,
        //         sala_id: 1,
        //         price_id: 1
        //       },
        //       {
        //         id: 41361,
        //         fecha: "2025-04-28",
        //         horaInicio: "2025-04-28 10:00:00",
        //         horaFin: "2025-04-28 13:35:00",
        //         subtitulada: "NO",
        //         activo: "ACTIVO",
        //         nroFuncion: "1",
        //         user_id: null,
        //         movie_id: 357,
        //         sala_id: 1,
        //         price_id: 1
        //       },
        //       {
        //         id: 41362,
        //         fecha: "2025-04-29",
        //         horaInicio: "2025-04-29 10:00:00",
        //         horaFin: "2025-04-29 13:35:00",
        //         subtitulada: "NO",
        //         activo: "ACTIVO",
        //         nroFuncion: "1",
        //         user_id: null,
        //         movie_id: 357,
        //         sala_id: 1,
        //         price_id: 1
        //       },
        //       {
        //         id: 41363,
        //         fecha: "2025-04-30",
        //         horaInicio: "2025-04-30 10:00:00",
        //         horaFin: "2025-04-30 13:35:00",
        //         subtitulada: "NO",
        //         activo: "ACTIVO",
        //         nroFuncion: "1",
        //         user_id: null,
        //         movie_id: 357,
        //         sala_id: 1,
        //         price_id: 1
        //       },
        //       {
        //         id: 41364,
        //         fecha: "2025-05-01",
        //         horaInicio: "2025-05-01 10:00:00",
        //         horaFin: "2025-05-01 13:35:00",
        //         subtitulada: "NO",
        //         activo: "ACTIVO",
        //         nroFuncion: "1",
        //         user_id: null,
        //         movie_id: 357,
        //         sala_id: 1,
        //         price_id: 1
        //       }
        //     ]
        //   },
        //   ofertas: [
        //     {
        //       id: 1,
        //       nombre: "Miercoles 2x1",
        //       descripcion: "2x1 en todas las funciones",
        //       imagen: "offer01.png"
        //     },
        //     {
        //       id: 2,
        //       nombre: "Martes duo",
        //       descripcion: "Martes cada funcion con pipocas gratis",
        //       imagen: "offer02.png"
        //     },
        //     {
        //       id: 3,
        //       nombre: "Jueves de Estreno",
        //       descripcion: "Estrenos en todas las funciones",
        //       imagen: "offer03.png"
        //     }
        //   ]
        // }
        this.pelicula = response.data.movie;
        this.ofertas = response.data.ofertas;
        this.$nextTick(() => {
          if (window.$ && typeof window.$('.casting-slider').owlCarousel === 'function') {
            window.$('.casting-slider').owlCarousel({ /* config */ })
          }
          if (window.$ && typeof window.$('.casting-slider-two').owlCarousel === 'function') {
            window.$('.casting-slider-two').owlCarousel({ /* config */ })
          }
          if (window.$ && typeof window.$('.details-photos').owlCarousel === 'function') {
            window.$('.details-photos').owlCarousel({ /* config */ })
          }
          const $img = window.$('.bg_img');
          $img.css('background-image', function () {
            return 'url(' + $img.data('background') + ')';
          });
        })
      } catch (error) {
        console.error('Error fetching movie data:', error);
      }
    },
  },
  computed: {
    codeYoutube() {
      if (this.pelicula.urlTrailer=== null || this.pelicula.urlTrailer === undefined) {
        return 'V0wIA60ujK4';
      }
      return this.pelicula.urlTrailer.split('v=')[1].split('&')[0];
    },
    urlYoutubeImg() {
      if (this.pelicula.urlTrailer === null || this.pelicula.urlTrailer === undefined) {
        return 'https://img.youtube.com/vi/V0wIA60ujK4/hqdefault.jpg';
      }
      return `https://img.youtube.com/vi/${this.codeYoutube}/hqdefault.jpg`;
    },
    arrayImegenes() {
      return [
        `https://img.youtube.com/vi/${this.codeYoutube}/0.jpg`,
        `https://img.youtube.com/vi/${this.codeYoutube}/1.jpg`,
        `https://img.youtube.com/vi/${this.codeYoutube}/2.jpg`,
      ];
    },
    puntucion5Critica() {
      if (this.pelicula.ratingCritica === null || this.pelicula.ratingCritica === undefined) {
        return 0;
      }
      return this.pelicula.ratingCritica / 20;
    },
    puntucion5Publico() {
      if (this.pelicula.ratingPublic === null || this.pelicula.ratingPublic === undefined) {
        return 0;
      }
      return this.pelicula.ratingPublic / 20;
    },
  },

};
</script>
<template>
  <!-- ==========Banner-Section========== -->
  <section class="details-banner bg_img" :data-background="urlYoutubeImg">
    <div class="container">
      <div class="details-banner-wrapper">
        <div class="details-banner-thumb">
          <img :src="`${$url}../../imagen/${pelicula.imagen}`" alt="movie" v-if="pelicula.imagen">
          <a :href="`https://www.youtube.com/embed/${codeYoutube}`" class="video-popup">
            <img src="/assets/images/movie/video-button.png" alt="movie">
          </a>
        </div>
        <div class="details-banner-content offset-lg-3">
          <h3 class="title">{{ $filters.textCapitalize(pelicula.nombre) }}</h3>
          <div class="tags">
            <a href="#">Estreno</a>
            <a href="#">Latino</a>
<!--            <a href="#0">Telegu</a>-->
<!--            <a href="#0">Tamil</a>-->
          </div>
          <a href="#0" class="button">
            {{ $filters.textCapitalize(pelicula.genero) }}
          </a>
          <a href="#0" class="button">
            {{ $filters.textCapitalize(pelicula.clasificacion) }}
          </a>
          <div class="social-and-duration">
            <div class="duration-area">
              <div class="item">
                <i class="fas fa-calendar-alt"></i>
                <span>
                  {{ $filters.dateFormat(pelicula.fechaEstreno) }}
                </span>
              </div>
              <div class="item">
                <i class="far fa-clock"></i>
                <span>
                  {{ $filters.timeFormat(pelicula.duracion) }}
                </span>
              </div>
            </div>
            <ul class="social-share">
              <li><a href="#0"><i class="fab fa-facebook-f"></i></a></li>
              <li><a href="#0"><i class="fab fa-twitter"></i></a></li>
              <li><a href="#0"><i class="fab fa-pinterest-p"></i></a></li>
              <li><a href="#0"><i class="fab fa-linkedin-in"></i></a></li>
              <li><a href="#0"><i class="fab fa-google-plus-g"></i></a></li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </section>
  <!-- ==========Banner-Section========== -->

  <!-- ==========Book-Section========== -->
  <section class="book-section bg-one">
    <div class="container">
      <div class="book-wrapper offset-lg-3">
        <div class="left-side">
          <div class="item">
            <div class="item-header">
              <div class="thumb">
                <img src="/assets/images/movie/tomato2.png" alt="movie">
              </div>
              <div class="counter-area">
                <span class="counter-item odometer" data-odometer-final="88">{{ pelicula.ratingCritica }}</span>
              </div>
            </div>
            <p>tomatometer</p>
          </div>
          <div class="item">
            <div class="item-header">
              <div class="thumb">
                <img src="/assets/images/movie/cake2.png" alt="movie">
              </div>
              <div class="counter-area">
                <span class="counter-item odometer" data-odometer-final="88">{{ pelicula.ratingPublic }}</span>
              </div>
            </div>
            <p>Audiencia</p>
          </div>
          <div class="item">
            <div class="item-header">
              <h5 class="title">{{ puntucion5Critica.toFixed(1) }}</h5>
              <div class="rated">
<!--                <i class="fas fa-heart"></i>-->
                <i class="fas fa-heart" v-for="i in parseInt(puntucion5Critica)" :key="i"></i>
              </div>
            </div>
            <p>Critica</p>
          </div>
          <div class="item">
            <div class="item-header">
              <div class="rated rate-it">
                <i class="fas fa-heart" v-for="i in parseInt(puntucion5Publico)" :key="i"></i>
              </div>
              <h5 class="title">{{ puntucion5Publico.toFixed(1) }}</h5>
            </div>
            <p><a href="#0">Publico</a></p>
          </div>
        </div>
        <a href="#0" class="custom-button">book tickets</a>
      </div>
    </div>
  </section>
  <!-- ==========Book-Section========== -->

  <!-- ==========Movie-Section========== -->
  <section class="movie-details-section padding-top padding-bottom">
    <div class="container">
      <div class="row justify-content-center flex-wrap-reverse mb--50">
        <div class="col-lg-3 col-sm-10 col-md-6 mb-50">
          <div class="widget-1 widget-tags">
            <ul>
              <li>
                <a href="#0">2D</a>
              </li>
              <li>
                <a href="#0">imax 2D</a>
              </li>
              <li>
                <a href="#0">4DX</a>
              </li>
            </ul>
          </div>
          <div class="widget-1 widget-offer">
            <h3 class="title">Applicable offer</h3>
            <div class="offer-body">
              <div class="offer-item">
                <div class="thumb">
                  <img src="/assets/images/sidebar/offer01.png" alt="sidebar">
                </div>
                <div class="content">
                  <h6>
                    <a href="#0">Amazon Pay Cashback Offer</a>
                  </h6>
                  <p>Win Cashback Upto Rs 300*</p>
                </div>
              </div>
              <div class="offer-item">
                <div class="thumb">
                  <img src="/assets/images/sidebar/offer02.png" alt="sidebar">
                </div>
                <div class="content">
                  <h6>
                    <a href="#0">PayPal Offer</a>
                  </h6>
                  <p>Transact first time with Paypal and
                    get 100% cashback up to Rs. 500</p>
                </div>
              </div>
              <div class="offer-item">
                <div class="thumb">
                  <img src="/assets/images/sidebar/offer03.png" alt="sidebar">
                </div>
                <div class="content">
                  <h6>
                    <a href="#0">HDFC Bank Offer</a>
                  </h6>
                  <p>Get 15% discount up to INR 100*
                    and INR 50* off on F&B T&C apply</p>
                </div>
              </div>
            </div>
          </div>
          <div class="widget-1 widget-banner">
            <div class="widget-1-body">
              <a href="#0">
                <img src="/assets/images/sidebar/banner/banner01.jpg" alt="banner">
              </a>
            </div>
          </div>
        </div>
        <div class="col-lg-9 mb-50">
          <div class="movie-details">
            <h3 class="title">photos</h3>
            <div class="details-photos owl-carousel">
              <div class="thumb">
                <a href="/assets/images/movie/movie-details01.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details01.jpg" alt="movie">
                </a>
              </div>
              <div class="thumb">
                <a href="/assets/images/movie/movie-details02.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details02.jpg" alt="movie">
                </a>
              </div>
              <div class="thumb">
                <a href="/assets/images/movie/movie-details03.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details03.jpg" alt="movie">
                </a>
              </div>
              <div class="thumb">
                <a href="/assets/images/movie/movie-details01.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details01.jpg" alt="movie">
                </a>
              </div>
              <div class="thumb">
                <a href="/assets/images/movie/movie-details02.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details02.jpg" alt="movie">
                </a>
              </div>
              <div class="thumb">
                <a href="/assets/images/movie/movie-details03.jpg" class="img-pop">
                  <img src="/assets/images/movie/movie-details03.jpg" alt="movie">
                </a>
              </div>
            </div>
            <div class="tab summery-review">
              <ul class="tab-menu">
                <li class="active">
                  summery
                </li>
                <li>
                  user review <span>147</span>
                </li>
              </ul>
              <div class="tab-area">
                <div class="tab-item active">
                  <div class="item">
                    <h5 class="sub-title">Synopsis</h5>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vehicula eros sit amet est tincidunt aliquet. Fusce laoreet ligula ac ultrices eleifend. Donec hendrerit fringilla odio, ut feugiat mi convallis nec. Fusce elit ex, blandit vitae mattis sit amet, iaculis ac elit. Ut diam mauris, viverra sit amet dictum vel, aliquam ac quam. Ut mi nisl, fringilla sit amet erat et, convallis porttitor ligula. Sed auctor, orci id luctus venenatis, dui dolor euismod risus, et pharetra orci lectus quis sapien. Duis blandit ipsum ac consectetur scelerisque. </p>
                  </div>
                  <div class="item">
                    <div class="header">
                      <h5 class="sub-title">cast</h5>
                      <div class="navigation">
                        <div class="cast-prev"><i class="flaticon-double-right-arrows-angles"></i></div>
                        <div class="cast-next"><i class="flaticon-double-right-arrows-angles"></i></div>
                      </div>
                    </div>
                    <div class="casting-slider owl-carousel">
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast01.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">Bill Hader</a></h6>
                          <span class="cate">actor</span>
                          <p>As Richie Tozier</p>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast02.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">nora hardy</a></h6>
                          <span class="cate">actor</span>
                          <p>As raven</p>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast03.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">alvin peters</a></h6>
                          <span class="cate">actor</span>
                          <p>As magneto</p>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast04.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">josh potter</a></h6>
                          <span class="cate">actor</span>
                          <p>As quicksilver</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="item">
                    <div class="header">
                      <h5 class="sub-title">crew</h5>
                      <div class="navigation">
                        <div class="cast-prev-2"><i class="flaticon-double-right-arrows-angles"></i></div>
                        <div class="cast-next-2"><i class="flaticon-double-right-arrows-angles"></i></div>
                      </div>
                    </div>
                    <div class="casting-slider-two owl-carousel">
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast05.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">pete warren</a></h6>
                          <span class="cate">actor</span>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast06.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">howard bass</a></h6>
                          <span class="cate">executive producer</span>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast07.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">naomi smith</a></h6>
                          <span class="cate">producer</span>
                        </div>
                      </div>
                      <div class="cast-item">
                        <div class="cast-thumb">
                          <a href="#0">
                            <img src="/assets/images/cast/cast08.jpg" alt="cast">
                          </a>
                        </div>
                        <div class="cast-content">
                          <h6 class="cast-title"><a href="#0">tom martinez</a></h6>
                          <span class="cate">producer</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-item">
                  <div class="movie-review-item">
                    <div class="author">
                      <div class="thumb">
                        <a href="#0">
                          <img src="/assets/images/cast/cast02.jpg" alt="cast">
                        </a>
                      </div>
                      <div class="movie-review-info">
                        <span class="reply-date">13 Days Ago</span>
                        <h6 class="subtitle"><a href="#0">minkuk seo</a></h6>
                        <span><i class="fas fa-check"></i> verified review</span>
                      </div>
                    </div>
                    <div class="movie-review-content">
                      <div class="review">
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                      </div>
                      <h6 class="cont-title">Awesome Movie</h6>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer volutpat enim non ante egestas vehicula. Suspendisse potenti. Fusce malesuada fringilla lectus venenatis porttitor. </p>
                      <div class="review-meta">
                        <a href="#0">
                          <i class="flaticon-hand"></i><span>8</span>
                        </a>
                        <a href="#0" class="dislike">
                          <i class="flaticon-dont-like-symbol"></i><span>0</span>
                        </a>
                        <a href="#0">
                          Report Abuse
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="movie-review-item">
                    <div class="author">
                      <div class="thumb">
                        <a href="#0">
                          <img src="/assets/images/cast/cast04.jpg" alt="cast">
                        </a>
                      </div>
                      <div class="movie-review-info">
                        <span class="reply-date">13 Days Ago</span>
                        <h6 class="subtitle"><a href="#0">rudra rai</a></h6>
                        <span><i class="fas fa-check"></i> verified review</span>
                      </div>
                    </div>
                    <div class="movie-review-content">
                      <div class="review">
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                      </div>
                      <h6 class="cont-title">Awesome Movie</h6>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer volutpat enim non ante egestas vehicula. Suspendisse potenti. Fusce malesuada fringilla lectus venenatis porttitor. </p>
                      <div class="review-meta">
                        <a href="#0">
                          <i class="flaticon-hand"></i><span>8</span>
                        </a>
                        <a href="#0" class="dislike">
                          <i class="flaticon-dont-like-symbol"></i><span>0</span>
                        </a>
                        <a href="#0">
                          Report Abuse
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="movie-review-item">
                    <div class="author">
                      <div class="thumb">
                        <a href="#0">
                          <img src="/assets/images/cast/cast01.jpg" alt="cast">
                        </a>
                      </div>
                      <div class="movie-review-info">
                        <span class="reply-date">13 Days Ago</span>
                        <h6 class="subtitle"><a href="#0">rafuj</a></h6>
                        <span><i class="fas fa-check"></i> verified review</span>
                      </div>
                    </div>
                    <div class="movie-review-content">
                      <div class="review">
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                      </div>
                      <h6 class="cont-title">Awesome Movie</h6>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer volutpat enim non ante egestas vehicula. Suspendisse potenti. Fusce malesuada fringilla lectus venenatis porttitor. </p>
                      <div class="review-meta">
                        <a href="#0">
                          <i class="flaticon-hand"></i><span>8</span>
                        </a>
                        <a href="#0" class="dislike">
                          <i class="flaticon-dont-like-symbol"></i><span>0</span>
                        </a>
                        <a href="#0">
                          Report Abuse
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="movie-review-item">
                    <div class="author">
                      <div class="thumb">
                        <a href="#0">
                          <img src="/assets/images/cast/cast03.jpg" alt="cast">
                        </a>
                      </div>
                      <div class="movie-review-info">
                        <span class="reply-date">13 Days Ago</span>
                        <h6 class="subtitle"><a href="#0">bela bose</a></h6>
                        <span><i class="fas fa-check"></i> verified review</span>
                      </div>
                    </div>
                    <div class="movie-review-content">
                      <div class="review">
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                        <i class="flaticon-favorite-heart-button"></i>
                      </div>
                      <h6 class="cont-title">Awesome Movie</h6>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer volutpat enim non ante egestas vehicula. Suspendisse potenti. Fusce malesuada fringilla lectus venenatis porttitor. </p>
                      <div class="review-meta">
                        <a href="#0">
                          <i class="flaticon-hand"></i><span>8</span>
                        </a>
                        <a href="#0" class="dislike">
                          <i class="flaticon-dont-like-symbol"></i><span>0</span>
                        </a>
                        <a href="#0">
                          Report Abuse
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="load-more text-center">
                    <a href="#0" class="custom-button transparent">load more</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ==========Movie-Section========== -->
</template>