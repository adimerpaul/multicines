<template>
  <q-page class="bg-grey-10 text-white">
    <q-inner-loading :showing="loading">
      <q-spinner-dots size="64px" color="primary" />
    </q-inner-loading>

    <q-carousel
      v-if="heroSlides.length"
      v-model="heroSlide"
      animated
      arrows
      navigation
      infinite
      control-color="pink-5"
      class="hero-carousel"
      :height="$q.screen.lt.md ? '56vh' : '70vh'"
    >
      <q-carousel-slide
        v-for="item in heroSlides"
        :key="item.id"
        :name="item.id"
        :img-src="resolveBackdrop(item)"
      >
        <div class="absolute-full hero-overlay">
          <div class="row fit items-center q-px-lg q-px-md-xl">
            <div class="col-12 col-md-7">
              <div class="text-h3 text-weight-bold">{{ item.titulo }}</div>
              <div class="q-mt-sm text-subtitle1 text-grey-4">{{ item.genero || 'Sin genero' }}</div>
              <div class="q-mt-md text-body1 ellipsis-3-lines" style="max-width: 720px">
                {{ item.descripcion || 'Sin descripcion' }}
              </div>
              <div class="q-mt-lg row q-gutter-sm">
                <q-chip color="red-6" text-color="white" icon="star">{{ score(item.puntaje_web) }}/10</q-chip>
                <q-chip color="yellow-7" text-color="black" icon="schedule">{{ item.duracion || '--' }}</q-chip>
                <q-chip color="blue-grey-8" text-color="white" icon="calendar_month">{{ item.anio || '--' }}</q-chip>
              </div>
              <div class="q-mt-md row q-gutter-sm">
                <q-btn color="pink-6" no-caps label="Ver horarios" @click="openShowtimes(item)" />
                <q-btn outline color="white" no-caps :href="item.url_video_youtube || item.trailer_youtube || '#'" target="_blank" label="Trailer" />
              </div>
            </div>
          </div>
        </div>
      </q-carousel-slide>
    </q-carousel>

    <div class="q-pa-md q-pa-lg-xl">
      <SectionRow title="Peliculas recomendadas" :items="peliculas" />
      <q-carousel
        v-if="carousel2.length"
        v-model="midSlide"
        animated
        arrows
        navigation
        infinite
        control-color="pink-5"
        class="q-mt-lg"
        :height="$q.screen.lt.md ? '220px' : '300px'"
      >
        <q-carousel-slide v-for="item in carousel2" :key="item.id" :name="item.id" :img-src="resolveBackdrop(item)">
          <div class="absolute-bottom text-white q-pa-md" style="background: linear-gradient(transparent, rgba(0,0,0,.85));">
            <div class="text-h5 text-weight-bold">{{ item.titulo }}</div>
            <div class="text-caption">{{ item.genero }}</div>
          </div>
        </q-carousel-slide>
      </q-carousel>
      <SectionRow class="q-mt-lg" title="Proximos estrenos" :items="proximos" />
      <SectionRow class="q-mt-lg q-pb-xl" title="Accion y aventura" :items="accion" />
    </div>

    <q-dialog v-model="showtimesDialog">
      <q-card style="min-width: 320px; max-width: 90vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ selectedMovie?.titulo || 'Horarios' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section v-if="selectedMoviePrograms.length">
          <q-list bordered separator>
            <q-item v-for="programa in selectedMoviePrograms" :key="programa.id">
              <q-item-section>
                <q-item-label>{{ formatProgramDate(programa.fecha, programa.horaInicio) }}</q-item-label>
                <q-item-label caption>
                  Sala: {{ programa.sala?.nombre || '--' }} | Tarifa: Bs {{ programa.price?.precio ?? '--' }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
        <q-card-section v-else class="text-body1">
          Esta pelicula aun no tiene programacion disponible. Te esperamos pronto en Multicines Plaza.
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';
import SectionRow from 'components/SectionRow.vue';

export default defineComponent({
  name: 'IndexPage',
  components: { SectionRow },
  data() {
    return {
      loading: false,
      heroSlide: null,
      midSlide: null,
      heroSlides: [],
      peliculas: [],
      proximos: [],
      carousel2: [],
      accion: [],
      showtimesDialog: false,
      selectedMovie: null,
      selectedMoviePrograms: [],
    };
  },
  mounted() {
    this.loadHome();
  },
  methods: {
    async loadHome() {
      this.loading = true;
      try {
        const { data } = await this.$axios.get('web/home');
        this.heroSlides = data.hero || [];
        this.peliculas = data.peliculas || [];
        this.proximos = data.proximos || [];
        this.carousel2 = data.carousel2 || [];
        this.accion = data.accion || [];
        if (this.heroSlides.length) this.heroSlide = this.heroSlides[0].id;
        if (this.carousel2.length) this.midSlide = this.carousel2[0].id;
      } finally {
        this.loading = false;
      }
    },
    resolveBackdrop(item) {
      const base = (this.$url || '').replace(/\/api\/?$/i, '/');
      if (item?.backdrop_imagen) {
        if (/^https?:\/\//i.test(item.backdrop_imagen)) return item.backdrop_imagen;
        return base + 'imagen/' + item.backdrop_imagen;
      }
      if (item?.imagen) {
        if (/^https?:\/\//i.test(item.imagen)) return item.imagen;
        return base + 'imagen/' + item.imagen;
      }
      return '';
    },
    score(value) {
      if (value === null || value === undefined || value === '') return '--';
      return Number(value).toFixed(1);
    },
    openShowtimes(item) {
      this.selectedMovie = item;
      this.selectedMoviePrograms = (item?.programas || []).slice().sort((a, b) => {
        const aTime = new Date(a.horaInicio).getTime();
        const bTime = new Date(b.horaInicio).getTime();
        return aTime - bTime;
      });
      this.showtimesDialog = true;
    },
    formatProgramDate(fecha, horaInicio) {
      if (!fecha) return '--';
      const date = new Date(`${fecha}T00:00:00`);
      const dayName = date.toLocaleDateString('es-ES', { weekday: 'long' });
      const formatted = date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
      let time = '';
      if (horaInicio) {
        const hourDate = new Date(horaInicio);
        if (!Number.isNaN(hourDate.getTime())) {
          time = hourDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }
      }
      return `${dayName.charAt(0).toUpperCase()}${dayName.slice(1)} ${formatted}${time ? ` ${time}` : ''}`;
    },
  },
});
</script>

<style scoped>
.hero-carousel {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.hero-overlay {
  background: linear-gradient(90deg, rgba(0, 0, 0, 0.88) 0%, rgba(0, 0, 0, 0.45) 45%, rgba(0, 0, 0, 0.2) 100%);
}
</style>
