<template>
  <div>
    <div class="text-h5 text-weight-bold q-mb-md">{{ title }}</div>
    <div class="row no-wrap overflow-auto q-gutter-md">
      <q-card
        v-for="item in items"
        :key="item.id"
        flat
        bordered
        class="bg-grey-9 text-white section-card"
      >
        <q-img :src="resolvePoster(item)" ratio="2/3" spinner-color="pink-5">
          <template v-slot:error>
            <div class="absolute-full flex flex-center bg-grey-8 text-grey-4">
              Sin imagen
            </div>
          </template>
          <div class="absolute-bottom text-subtitle2 bg-transparent text-weight-bold">
            {{ item.titulo }}
          </div>
        </q-img>
        <q-card-section class="q-pa-sm">
          <div class="row items-center justify-between">
            <q-chip dense color="yellow-7" text-color="black" icon="star">{{ score(item.puntaje_web) }}</q-chip>
            <q-chip dense color="blue-grey-8" text-color="white" icon="schedule">{{ item.duracion || '--' }}</q-chip>
          </div>
          <div class="text-caption text-grey-4 ellipsis">{{ item.genero || 'Sin genero' }}</div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SectionRow',
  props: {
    title: { type: String, required: true },
    items: { type: Array, default: () => [] },
  },
  methods: {
    publicBase() {
      const raw = this.$url || '';
      return raw.replace(/\/api\/?$/i, '/');
    },
    resolvePoster(item) {
      if (!item) return '/favicon.ico';
      if (item.imagen && /^https?:\/\//i.test(item.imagen)) return item.imagen;
      if (item.backdrop_imagen && /^https?:\/\//i.test(item.backdrop_imagen)) return item.backdrop_imagen;
      if (item.imagen && item.imagen.includes('/imagen/')) return item.imagen;
      if (item.imagen) return this.publicBase() + 'imagen/' + item.imagen;
      if (item.backdrop_imagen) return this.publicBase() + 'imagen/' + item.backdrop_imagen;
      return '/favicon.ico';
    },
    score(value) {
      if (value === null || value === undefined || value === '') return '--';
      return Number(value).toFixed(1);
    },
  },
});
</script>

<style scoped>
.section-card {
  min-width: 220px;
  width: 220px;
}
@media (max-width: 600px) {
  .section-card {
    min-width: 160px;
    width: 160px;
  }
}
</style>
