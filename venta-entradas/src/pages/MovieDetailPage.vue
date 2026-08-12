<template>
  <q-page class="bg-grey-10 text-white">
    <q-inner-loading :showing="loading">
      <q-spinner-dots size="64px" color="pink-5" />
    </q-inner-loading>

    <div v-if="movie" class="detail-hero" :style="{ backgroundImage: `url('${backdropUrl(movie)}')` }">
      <div class="detail-overlay">
        <div class="row q-col-gutter-lg items-start">
          <div class="col-12 col-md-8">
            <div class="row items-center q-gutter-sm">
              <q-btn flat round color="white" icon="arrow_back" @click="$router.back()" />
              <div class="text-h3 text-weight-bold">{{ movie.titulo }}</div>
              <q-btn
                round
                :color="isFavorite ? 'pink-6' : 'grey-7'"
                text-color="white"
                :icon="isFavorite ? 'favorite' : 'favorite_border'"
                @click="toggleFavorite"
              />
            </div>

            <div class="q-mt-md row q-gutter-sm">
              <q-chip color="pink-6" text-color="white" icon="star">{{ score(movie.puntaje_web) }}/10 Web</q-chip>
              <q-chip color="yellow-7" text-color="black" icon="star">{{ score(movie.puntaje_ibm) }}/10 IMDb</q-chip>
              <q-chip color="blue-grey-7" text-color="white" icon="schedule">{{ movie.duracion || '--' }}</q-chip>
              <q-chip color="deep-orange-7" text-color="white" icon="event">{{ movie.anio || '--' }}</q-chip>
            </div>

            <div class="q-mt-md text-body1" style="max-width: 900px">
              {{ movie.descripcion || 'Sin descripcion disponible.' }}
            </div>

            <div class="q-mt-lg row q-col-gutter-md">
              <div class="col-12 col-sm-6 col-md-4"><b>Genero:</b> {{ movie.genero || '--' }}</div>
              <div class="col-12 col-sm-6 col-md-4"><b>Pais:</b> {{ movie.pais || '--' }}</div>
              <div class="col-12 col-sm-6 col-md-4"><b>Clasificacion:</b> {{ movie.clasificacion || '--' }}</div>
              <div class="col-12 col-sm-6 col-md-4"><b>Calidad:</b> {{ movie.calidad || '--' }}</div>
              <div class="col-12 col-sm-6 col-md-4"><b>Idioma:</b> {{ movie.idioma || '--' }}</div>
              <div class="col-12 col-sm-6 col-md-4"><b>Estudio:</b> {{ movie.studio?.nombre || '--' }}</div>
            </div>

            <div class="q-mt-lg row q-gutter-sm">
              <q-btn color="pink-6" no-caps label="Pedir entradas" @click="scrollToHorarios" />
              <q-btn outline color="white" no-caps label="Ver trailer" :href="movie.url_video_youtube || movie.trailer_youtube || '#'" target="_blank" />
            </div>
          </div>

          <div class="col-12 col-md-4">
            <q-card class="bg-grey-9 detail-poster-card">
              <q-img :src="posterUrl(movie)" fit="cover" style="height: 520px;">
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-8 text-grey-4">Sin imagen</div>
                </template>
              </q-img>
            </q-card>
          </div>
        </div>
      </div>
    </div>

    <div class="q-px-md q-pb-xl" ref="horariosSection">
      <div class="text-h5 text-weight-bold q-mt-lg q-mb-md">Horarios disponibles</div>
      <q-card flat bordered class="bg-grey-9 text-white">
        <q-card-section v-if="programacion.length">
          <div v-for="group in programacion" :key="group.fecha" class="q-mb-md">
            <div class="text-subtitle1 text-weight-bold q-mb-sm row items-center q-gutter-xs">
              <q-icon name="calendar_month" color="amber-6" />
              <span>{{ formatDay(group.fecha) }}</span>
            </div>
            <div class="row q-gutter-sm">
              <q-chip
                v-for="f in group.funciones"
                :key="f.id"
                clickable
                color="blue-grey-7"
                text-color="white"
                icon="schedule"
                @click="openSeatsDialog(f)"
              >
                {{ hourAmPm(f.horaInicio) }} | Sala {{ f.sala?.nombre || '--' }} | Bs {{ f.price?.precio ?? '--' }}
              </q-chip>
            </div>
          </div>
        </q-card-section>
        <q-card-section v-else class="text-body1">
          Esta pelicula aun no tiene programacion disponible. Te esperamos pronto en Multicines Plaza.
        </q-card-section>
      </q-card>

      <div v-if="relacionadas.length" class="q-mt-xl">
        <div class="text-h5 text-weight-bold q-mb-md">Mas para ti</div>
        <div class="row no-wrap overflow-auto q-gutter-md">
          <q-card
            v-for="item in relacionadas"
            :key="item.id"
            class="bg-grey-9 text-white"
            flat
            bordered
            style="min-width: 180px; width: 180px;"
            clickable
            @click="$router.push(`/pelicula/${item.id}`)"
          >
            <q-img :src="posterUrl(item)" style="height: 260px" fit="cover" />
            <q-card-section class="q-pa-sm">
              <div class="ellipsis text-subtitle2">{{ item.titulo }}</div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <q-dialog v-model="seatsDialog" maximized>
      <q-card class="bg-grey-10 text-white">
        <q-card-section class="row items-center">
          <div class="text-h6">Seleccionar asientos</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator dark />
        <q-card-section class="q-pb-none">
          <div class="text-subtitle2 q-mb-sm">
            Funcion: {{ selectedFuncion ? hourAmPm(selectedFuncion.horaInicio) : '--' }} | Sala {{ selectedFuncion?.sala?.nombre || '--' }} | Fecha {{ selectedFuncion ? formatDay(selectedFuncion.fecha) : '--' }}
          </div>
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-12 col-sm-6 col-md-3">
              <q-chip square color="negative" text-color="white" icon="block" class="full-width justify-center">Bloqueado</q-chip>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <q-chip square color="amber-7" text-color="black" icon="schedule" class="full-width justify-center">Reservado</q-chip>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <q-chip square color="grey-6" text-color="black" icon="event_seat" class="full-width justify-center">Libre</q-chip>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <q-chip square color="positive" text-color="white" icon="check_circle" class="full-width justify-center">Seleccionado</q-chip>
            </div>
          </div>
        </q-card-section>
        <q-card-section class="q-pt-sm">
          <div class="seats-wrapper">
            <div class="screen-indicator">PANTALLA</div>
            <div class="seats-grid">
              <div v-for="row in seatRows" :key="row.key" class="seat-row">
                <div class="seat-row-label">{{ row.label }}</div>
                <div class="seat-row-seats">
                  <q-btn
                    v-for="seat in row.seats"
                    :key="seatKey(seat)"
                    unelevated
                    no-caps
                    class="seat-btn"
                    :disable="seat.activo !== 'LIBRE'"
                    :color="seatColor(seat)"
                    :text-color="seatTextColor(seat)"
                    :label="seatLabel(seat)"
                    @click="toggleSeat(seat)"
                  />
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-separator dark />
        <q-card-section class="row items-center q-col-gutter-sm">
          <div class="col-12 col-md-auto">
            <q-chip color="positive" text-color="white" icon="check_circle">Seleccionados: {{ selectedSeats.length }}</q-chip>
          </div>
          <div class="col-12 col-md-auto">
            <q-chip color="primary" text-color="white" icon="payments">Total: Bs {{ totalAmount.toFixed(2) }}</q-chip>
          </div>
          <div class="col" />
          <div class="col-12 col-md-auto">
            <q-btn
              class="full-width whatsapp-btn"
              color="green-7"
              icon="fa-brands fa-whatsapp"
              no-caps
              label="Enviar seleccion por WhatsApp"
              @click="sendWhatsApp"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'MovieDetailPage',
  data() {
    return {
      loading: false,
      movie: null,
      programacion: [],
      relacionadas: [],
      isFavorite: false,
      whatsappNumero: '',
      seatsDialog: false,
      selectedFuncion: null,
      seats: [],
      selectedSeats: [],
    };
  },
  mounted() {
    this.loadDetail();
  },
  watch: {
    '$route.params.id'() {
      this.loadDetail();
    },
  },
  methods: {
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
    backdropUrl(item) {
      if (!item) return '';
      if (item.backdrop_imagen && /^https?:\/\//i.test(item.backdrop_imagen)) return item.backdrop_imagen;
      if (item.backdrop_imagen) return this.baseUrl() + 'imagen/' + item.backdrop_imagen;
      return this.posterUrl(item);
    },
    score(v) {
      if (v === null || v === undefined || v === '') return '--';
      return Number(v).toFixed(1);
    },
    hourAmPm(datetime) {
      if (!datetime) return '--';
      const d = new Date(datetime);
      if (Number.isNaN(d.getTime())) return '--';
      return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    },
    formatDay(fecha) {
      if (!fecha) return '--';
      const d = new Date(`${fecha}T00:00:00`);
      if (Number.isNaN(d.getTime())) return fecha;
      const dayName = d.toLocaleDateString('es-ES', { weekday: 'long' });
      const f = d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
      return `${dayName.charAt(0).toUpperCase()}${dayName.slice(1)} ${f}`;
    },
    favoriteKey() {
      return 'multicines_plaza_favorites';
    },
    syncFavoriteState() {
      const list = JSON.parse(localStorage.getItem(this.favoriteKey()) || '[]');
      this.isFavorite = !!this.movie && list.some((item) => item.id === this.movie.id);
    },
    toggleFavorite() {
      if (!this.movie) return;
      const key = this.favoriteKey();
      const list = JSON.parse(localStorage.getItem(key) || '[]');
      const has = list.some((item) => item.id === this.movie.id);
      const fullMovie = {
        ...this.movie,
        programacion: this.programacion,
        relacionadas: this.relacionadas,
        savedAt: new Date().toISOString(),
      };
      const next = has ? list.filter((item) => item.id !== this.movie.id) : [...list, fullMovie];
      localStorage.setItem(key, JSON.stringify(next));
      this.isFavorite = !has;
      this.$q.notify({
        color: this.isFavorite ? 'positive' : 'grey-7',
        message: this.isFavorite ? 'Agregado a favoritos' : 'Quitado de favoritos',
      });
    },
    scrollToHorarios() {
      const el = this.$refs.horariosSection;
      if (el && typeof el.scrollIntoView === 'function') {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },
    seatKey(seat) {
      return `${seat.letra}-${seat.fila}-${seat.columna}`;
    },
    seatColor(seat) {
      if (seat.activo === 'OCUPADO' || seat.activo === 'INACTIVO') return 'negative';
      if (seat.activo === 'RESERVADO') return 'amber-7';
      if (this.selectedSeats.some((s) => this.seatKey(s) === this.seatKey(seat))) return 'positive';
      return 'grey-6';
    },
    seatTextColor(seat) {
      if (this.selectedSeats.some((s) => this.seatKey(s) === this.seatKey(seat))) return 'white';
      if (seat.activo === 'RESERVADO' || seat.activo === 'LIBRE') return 'black';
      return 'white';
    },
    seatLabel(seat) {
      const isSelected = this.selectedSeats.some((s) => this.seatKey(s) === this.seatKey(seat));
      if (seat.activo !== 'LIBRE' && !isSelected) return '';
      return String(seat.columna || '');
    },
    compareMixed(a, b) {
      const na = Number(a);
      const nb = Number(b);
      const aIsNum = !Number.isNaN(na);
      const bIsNum = !Number.isNaN(nb);
      if (aIsNum && bIsNum) return na - nb;
      return String(a).localeCompare(String(b), 'es', { numeric: true, sensitivity: 'base' });
    },
    toggleSeat(seat) {
      if (seat.activo !== 'LIBRE') return;
      const key = this.seatKey(seat);
      const exists = this.selectedSeats.some((s) => this.seatKey(s) === key);
      this.selectedSeats = exists
        ? this.selectedSeats.filter((s) => this.seatKey(s) !== key)
        : [...this.selectedSeats, seat];
    },
    async openSeatsDialog(funcion) {
      this.selectedFuncion = funcion;
      this.selectedSeats = [];
      try {
        this.loading = true;
        const { data } = await this.$axios.post('web/mySeats', { id: funcion.id });
        this.seats = data.seat || [];
        this.seatsDialog = true;
      } catch (e) {
        this.$q.notify({ color: 'negative', message: 'No se pudo cargar los asientos' });
      } finally {
        this.loading = false;
      }
    },
    sendWhatsApp() {
      if (!this.selectedFuncion || this.selectedSeats.length === 0) {
        this.$q.notify({ color: 'warning', message: 'Selecciona al menos un asiento' });
        return;
      }
      const phone = (this.whatsappNumero || '').replace(/\D/g, '');
      if (!phone) {
        this.$q.notify({ color: 'negative', message: 'No hay numero de WhatsApp de pedidos configurado' });
        return;
      }
      const seatsText = this.selectedSeats.map((s) => `${s.letra}${s.fila}-${s.columna}`).join(', ');
      const message = [
        'Hola Multicines Plaza, quiero reservar entradas:',
        `Pelicula: ${this.movie.titulo}`,
        `Fecha: ${this.formatDay(this.selectedFuncion.fecha)}`,
        `Hora: ${this.hourAmPm(this.selectedFuncion.horaInicio)}`,
        `Sala: ${this.selectedFuncion.sala?.nombre || '--'}`,
        `Asientos: ${seatsText}`,
        `Total estimado: Bs ${this.totalAmount.toFixed(2)}`,
      ].join('\n');
      const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank');
    },
    async loadDetail() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const { data } = await this.$axios.get(`web/webMovie/${id}`);
        this.movie = data.movie || null;
        this.programacion = data.programacion || [];
        this.relacionadas = data.relacionadas || [];
        this.whatsappNumero = data.whatsapp_numero || '';
        this.syncFavoriteState();
      } catch (e) {
        this.$q.notify({ color: 'negative', message: 'No se pudo cargar la pelicula' });
        this.$router.push('/');
      } finally {
        this.loading = false;
      }
    },
  },
  computed: {
    totalAmount() {
      const unit = Number(this.selectedFuncion?.price?.precio || 0);
      return unit * this.selectedSeats.length;
    },
    seatRows() {
      const grouped = {};
      for (const seat of this.seats) {
        const rowKey = `${seat.letra || ''}${seat.fila || ''}`;
        if (!grouped[rowKey]) {
          grouped[rowKey] = {
            key: rowKey,
            letra: seat.letra || '',
            fila: seat.fila || '',
            seats: [],
          };
        }
        grouped[rowKey].seats.push(seat);
      }

      return Object.values(grouped)
        .sort((a, b) => {
          const byLetra = this.compareMixed(a.letra, b.letra);
          if (byLetra !== 0) return byLetra;
          return this.compareMixed(a.fila, b.fila);
        })
        .map((row) => ({
          key: row.key,
          label: `${row.letra || ''}${row.fila || ''}`,
          seats: row.seats.sort((a, b) => this.compareMixed(a.columna, b.columna)),
        }));
    },
  },
});
</script>

<style scoped>
.detail-hero {
  min-height: 78vh;
  background-size: cover;
  background-position: center;
}
.detail-overlay {
  min-height: 78vh;
  padding: 24px;
  background: linear-gradient(90deg, rgba(7, 7, 7, 0.92) 0%, rgba(7, 7, 7, 0.65) 45%, rgba(7, 7, 7, 0.5) 100%);
}
.detail-poster-card {
  border: 2px solid rgba(255, 58, 120, 0.75);
  border-radius: 26px;
  overflow: hidden;
}
.seats-wrapper {
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 16px;
}
.screen-indicator {
  text-align: center;
  font-size: 12px;
  letter-spacing: 2px;
  color: #ffd9e5;
  background: linear-gradient(90deg, rgba(255, 47, 109, 0.18), rgba(255, 47, 109, 0.05));
  border: 1px solid rgba(255, 47, 109, 0.35);
  border-radius: 10px;
  padding: 8px;
  margin-bottom: 16px;
}
.seats-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 4px;
}
.seat-row {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: max-content;
}
.seat-row-label {
  width: 42px;
  text-align: center;
  font-weight: 700;
  color: #e5e5e5;
}
.seat-row-seats {
  display: flex;
  align-items: center;
  gap: 6px;
}
.seat-btn {
  width: 42px;
  min-height: 42px;
  border-radius: 10px;
  font-weight: 700;
}
.whatsapp-btn {
  min-height: 46px;
  font-weight: 700;
}
@media (max-width: 768px) {
  .detail-overlay {
    padding: 14px;
  }
  .detail-hero {
    min-height: 56vh;
  }
  .seat-btn {
    width: 38px;
    min-height: 38px;
    border-radius: 8px;
    font-size: 12px;
  }
  .seat-row-label {
    width: 34px;
    font-size: 12px;
  }
}
</style>
