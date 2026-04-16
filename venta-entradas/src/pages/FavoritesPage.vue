<template>
  <q-page class="bg-grey-10 text-white q-pa-md q-pa-lg-xl">
    <div class="row items-center q-mb-md">
      <div class="text-h4 text-weight-bold">Mis favoritos</div>
      <q-space />
      <q-btn flat color="pink-5" icon="refresh" label="Actualizar" @click="loadFavorites" />
    </div>

    <q-banner v-if="favorites.length === 0" class="bg-grey-9 text-white">
      Aun no tienes favoritos guardados. Agrega peliculas desde el detalle con el icono de corazon.
    </q-banner>

    <div v-else class="row q-col-gutter-md">
      <div v-for="item in favorites" :key="item.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
        <q-card class="bg-grey-9 text-white" flat bordered clickable @click="$router.push(`/pelicula/${item.id}`)">
          <q-img :src="posterUrl(item)" style="height: 320px" fit="cover" />
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold ellipsis">{{ item.titulo }}</div>
            <div class="text-caption text-grey-4 ellipsis">{{ item.genero || 'Sin genero' }}</div>
            <div class="q-mt-sm row q-gutter-xs">
              <q-chip dense color="yellow-7" text-color="black" icon="star">{{ score(item.puntaje_web) }}</q-chip>
              <q-chip dense color="blue-grey-7" text-color="white" icon="schedule">{{ item.duracion || '--' }}</q-chip>
            </div>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat color="negative" icon="delete" label="Quitar" @click.stop="removeFavorite(item.id)" />
          </q-card-actions>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'FavoritesPage',
  data() {
    return {
      favorites: [],
    };
  },
  mounted() {
    this.loadFavorites();
  },
  methods: {
    key() {
      return 'multicines_plaza_favorites';
    },
    loadFavorites() {
      this.favorites = JSON.parse(localStorage.getItem(this.key()) || '[]');
    },
    removeFavorite(id) {
      this.favorites = this.favorites.filter((f) => f.id !== id);
      localStorage.setItem(this.key(), JSON.stringify(this.favorites));
      this.$q.notify({ color: 'positive', message: 'Quitado de favoritos' });
    },
    baseUrl() {
      return (this.$url || '').replace(/\/api\/?$/i, '/');
    },
    posterUrl(item) {
      if (!item) return '/favicon.ico';
      if (item.imagen && /^https?:\/\//i.test(item.imagen)) return item.imagen;
      if (item.imagen) return this.baseUrl() + 'imagen/' + item.imagen;
      if (item.backdrop_imagen && /^https?:\/\//i.test(item.backdrop_imagen)) return item.backdrop_imagen;
      if (item.backdrop_imagen) return this.baseUrl() + 'imagen/' + item.backdrop_imagen;
      return '/favicon.ico';
    },
    score(v) {
      if (v === null || v === undefined || v === '') return '--';
      return Number(v).toFixed(1);
    },
  },
});
</script>

