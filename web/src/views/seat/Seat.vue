<template>
  <!-- ==========Movie-Section========== -->
  <div class="seat-plan-section padding-bottom padding-top">
    <div class="container">
      <div class="screen-area">
        <h4 class="screen">
          {{ sala.nombre }}
        </h4>
        <div class="screen-thumb">
          <img src="/assets/images/movie/screen-thumb.png" alt="movie">
        </div>
        <h5 class="subtitle">
          {{ programa.movie.nombre }} {{ programa.movie.formato }}
        </h5>

        <div class="screen-wrapper">
          <ul class="seat-area">
            <li
                v-for="fila in letrasFilas"
                :key="fila"
                class="seat-line"
            >
              <span>{{ fila }}</span>
              <ul class="seat--area">
                <li
                    v-for="columna in columnasInversas"
                    :key="fila + columna"
                    class="single-seat"
                    @click="toggleSeat(fila, columna)"
                >
                  <img
                      :src="getSeatImage(fila, columna)"
                      alt="seat"
                  />
                  <span
                      class="sit-num"
                      v-if="showLibre(fila, columna)"
                  >{{ fila + columna }}</span>
                </li>
              </ul>
              <span>{{ fila }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="proceed-book bg_img" data-background="/assets/images/movie/movie-bg-proceed.jpg">
        <div class="proceed-to-book">
          <div class="book-item">
            <span>Has elegido:</span>
            <h3 class="title">{{ seleccionados.join(', ') }}</h3>
          </div>
          <div class="book-item" style="padding-left: 10px">
            <span>Total a pagar</span>
            <h3 class="title">{{ seleccionados.length * programa.price?.precio }} Bs</h3>
          </div>
          <div class="book-item">
<!--            <a href="movie-checkout.html" class="custom-button">Proceder</a>-->
            <div class="book-item">
              <a
                  :href="whatsappLink"
                  target="_blank"
                  class="custom-button"
              >
                Reservar por WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Seat',
  data() {
    return {
      sala: {},
      programa: {
        price: { precio: 0 }, // fallback por si no llega aún
        movie: {}
      },
      seat: [],
      seleccionados: [],
      letrasFilas: [],
    };
  },
  computed: {
    columnasInversas() {
      const cols = parseInt(this.sala.columnas) || 0;
      return Array.from({ length: cols }, (_, i) => cols - i);
    },
    whatsappLink() {
      const mensaje = `*Reserva de Entradas*
Película: ${this.programa.movie?.nombre} (${this.programa.movie?.formato})
Hora: ${this.programa.horaInicio?.substring(0, 16)}
Asientos: ${this.seleccionados.join(', ')}
Total: ${this.seleccionados.length * this.programa.price?.precio} Bs.

Por favor, confirmar disponibilidad.`;

      const telefono = "59169603027"; // ← pon el número con código de país sin el +
      return `https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`;
    }
  },
  mounted() {
    this.seatGet();
  },
  methods: {
    showLibre(fila, columna) {
      const asiento = this.seat.find(s => s.letra === fila && s.columna === columna);
      return asiento && asiento.activo === 'LIBRE';
    },
    seatGet() {
      this.$axios.post('mySeats', {
        id: this.$route.params.id,
      }).then(response => {
        this.sala = response.data.sala;
        this.programa = response.data.programa;
        this.seat = response.data.seat;
        this.letrasFilas = [...new Set(this.seat.map(s => s.letra))];
      }).catch(error => {
        console.error('Error fetching seat data:', error);
      });
    },
    getSeatImage(letra, columna) {
      const asiento = this.seat.find(s => s.letra === letra && s.columna === columna);
      if (!asiento) return '/assets/images/movie/seat01.png';

      switch (asiento.activo) {
        case 'LIBRE':
          return this.seleccionados.includes(`${letra}${columna}`)
              ? '/assets/images/movie/seat01-selected.png'
              : '/assets/images/movie/seat01.png';
        case 'OCUPADO':
          // return '/assets/images/movie/seat01.png';
          return '/assets/images/movie/seat01-ocupado.png';
        case 'RESERVADO':
          return '/assets/images/movie/seat01-reserved.png';
        case 'INACTIVO':
        default:
          return '/assets/images/movie/seat01-disabled.png';
      }
    },
    toggleSeat(letra, columna) {
      const key = `${letra}${columna}`;
      const asiento = this.seat.find(s => s.letra === letra && s.columna === columna);
      if (!asiento || asiento.activo !== 'LIBRE') return;

      const index = this.seleccionados.indexOf(key);
      if (index >= 0) {
        this.seleccionados.splice(index, 1);
      } else {
        this.seleccionados.push(key);
      }
    }
  }
};
</script>
