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
    selectSeat(id) {
      const modal = window.$('#exampleModal');
      modal.modal('hide');
      this.$router.push('/seat/' + id);
    },
    async movieGet() {
      try {
        const response = await this.$axios.get('movie/' + this.$route.params.id);
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
        // `https://img.youtube.com/vi/${this.codeYoutube}/3.jpg`,
        // `https://img.youtube.com/vi/${this.codeYoutube}/4.jpg`,
        // `https://img.youtube.com/vi/${this.codeYoutube}/5.jpg`,
        // `https://img.youtube.com/vi/${this.codeYoutube}/6.jpg`,
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
<!--        <a href="#0" class="custom-button">book tickets</a>-->
<!--        espanol-->
        <a class="custom-button" data-toggle="modal" data-target="#exampleModal" style="cursor: pointer" >
          <i class="fas fa-ticket-alt"></i>
          comprar entradas
        </a>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" >
            <div class="modal-content" style="background: #040360">
              <div class="modal-body">
                <div style="color: white; font-size: 20px; display: flex; justify-content: space-between; align-items: center;">
                  <span><strong>Selecciona la función</strong></span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; width: 30px; height: 30px;">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="mt-3">
                  <template v-for="(horarios, fecha) in pelicula.programaActivas" :key="fecha">
                    <div style="color: white; font-size: 15px; margin-bottom: 10px; margin-top: 20px;">
                      {{ $filters.timeFormatLarge2(fecha) }}
                    </div>
                    <div v-for="programa in horarios" :key="programa.id" style="margin-bottom: 10px;">
                      <button class="btn btn-outline-info btn-block bold" style="text-align: left;"
                              @click="selectSeat(programa.id)"
                      >
                        {{ $filters.formatHora(programa.horaInicio) }}
                        (Subtitulada: {{ programa.subtitulada === 'SI' ? 'Sí' : 'No' }}) -
                        Sala {{ programa.sala_id }}
                        Precio {{ programa.precio }} Bs
                      </button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>

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
            <h3 class="title">Ofertas Activas</h3>
            <div class="offer-body">
              <div class="offer-item" v-for="oferta in ofertas" :key="oferta.id">
                <div class="thumb">
                  <img :src="`${$url}../../imagen/${oferta.imagen}`" alt="sidebar" v-if="oferta.imagen">
                </div>
                <div class="content">
                  <h6>
                    <a href="#0">{{ oferta.nombre }}</a>
                  </h6>
                  <p>{{ oferta.descripcion }}</p>
                </div>
              </div>
            </div>
          </div>
<!--          <div class="widget-1 widget-banner">-->
<!--            <div class="widget-1-body">-->
<!--              <a href="#0">-->
<!--                <img src="/assets/images/sidebar/banner/banner01.jpg" alt="banner">-->
<!--              </a>-->
<!--            </div>-->
<!--          </div>-->
        </div>
        <div class="col-lg-9 mb-50">
          <div class="movie-details">
            <h3 class="title">photos</h3>
            <div class="details-photos owl-carousel">
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details01.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details01.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details02.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details02.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details03.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details03.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details01.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details01.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details02.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details02.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
<!--              <div class="thumb">-->
<!--                <a href="/assets/images/movie/movie-details03.jpg" class="img-pop">-->
<!--                  <img src="/assets/images/movie/movie-details03.jpg" alt="movie">-->
<!--                </a>-->
<!--              </div>-->
              <div class="thumb" v-for="(img, index) in arrayImegenes" :key="index">
                <a :href="img" class="img-pop">
                  <img :src="img" alt="movie">
                </a>
              </div>
            </div>
            <div class="tab summery-review">
              <ul class="tab-menu">
                <li class="active">
                  summery
                </li>
<!--                <li>-->
<!--                  user review <span>147</span>-->
<!--                </li>-->
              </ul>
              <div class="tab-area">
                <div class="tab-item active">
                  <div class="item">
                    <h5 class="sub-title">Sinopsis</h5>
                    <p>
                      {{ $filters.textCapitalize(pelicula.sipnosis) }}
                    </p>
                  </div>
<!--                  <div class="item">-->
<!--                    <div class="header">-->
<!--                      <h5 class="sub-title">cast</h5>-->
<!--                      <div class="navigation">-->
<!--                        <div class="cast-prev"><i class="flaticon-double-right-arrows-angles"></i></div>-->
<!--                        <div class="cast-next"><i class="flaticon-double-right-arrows-angles"></i></div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                    <div class="casting-slider owl-carousel">-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast01.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">Bill Hader</a></h6>-->
<!--                          <span class="cate">actor</span>-->
<!--                          <p>As Richie Tozier</p>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast02.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">nora hardy</a></h6>-->
<!--                          <span class="cate">actor</span>-->
<!--                          <p>As raven</p>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast03.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">alvin peters</a></h6>-->
<!--                          <span class="cate">actor</span>-->
<!--                          <p>As magneto</p>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast04.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">josh potter</a></h6>-->
<!--                          <span class="cate">actor</span>-->
<!--                          <p>As quicksilver</p>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                  </div>-->
<!--                  <div class="item">-->
<!--                    <div class="header">-->
<!--                      <h5 class="sub-title">crew</h5>-->
<!--                      <div class="navigation">-->
<!--                        <div class="cast-prev-2"><i class="flaticon-double-right-arrows-angles"></i></div>-->
<!--                        <div class="cast-next-2"><i class="flaticon-double-right-arrows-angles"></i></div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                    <div class="casting-slider-two owl-carousel">-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast05.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">pete warren</a></h6>-->
<!--                          <span class="cate">actor</span>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast06.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">howard bass</a></h6>-->
<!--                          <span class="cate">executive producer</span>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast07.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">naomi smith</a></h6>-->
<!--                          <span class="cate">producer</span>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                      <div class="cast-item">-->
<!--                        <div class="cast-thumb">-->
<!--                          <a href="#0">-->
<!--                            <img src="/assets/images/cast/cast08.jpg" alt="cast">-->
<!--                          </a>-->
<!--                        </div>-->
<!--                        <div class="cast-content">-->
<!--                          <h6 class="cast-title"><a href="#0">tom martinez</a></h6>-->
<!--                          <span class="cate">producer</span>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                  </div>-->
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