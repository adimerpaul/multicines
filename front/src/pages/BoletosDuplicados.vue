<template>
  <q-page padding>
    <div class="row q-col-gutter-sm items-center">
      <div class="col-12 col-sm-3">
        <q-input dense outlined v-model="fecha" label="Fecha" type="date" @update:model-value="consultar"/>
      </div>
      <div class="col-12 col-sm-3">
        <q-btn icon="search" color="primary" label="Consultar" :loading="loading" @click="consultar"/>
      </div>
      <div class="col-12 col-sm-6">
        <q-toggle v-model="soloDuplicados" label="Mostrar solo funciones con duplicados"/>
      </div>
    </div>

    <q-banner v-if="consultado" dense rounded class="q-my-sm text-white" :class="totalDuplicados ? 'bg-red' : 'bg-green'">
      <template v-slot:avatar>
        <q-icon :name="totalDuplicados ? 'warning' : 'check_circle'"/>
      </template>
      <span v-if="totalDuplicados">
        {{ funcionesConDuplicados }} funcion(es) con {{ totalDuplicados }} boleto(s) de mas vendidos en butacas repetidas
      </span>
      <span v-else>No hay butacas vendidas dos veces el {{ fecha }}</span>
    </q-banner>

    <div class="row q-col-gutter-sm">
      <div class="col-12 col-md-4" v-for="p in peliculas" :key="p.movie_id">
        <q-card flat bordered>
          <q-card-section class="row no-wrap items-center q-gutter-sm q-pb-xs">
            <div class="poster">
              <img v-if="p.imagen && !sinImagen[p.movie_id]" :src="`${url}../imagen/${p.imagen}`" :alt="p.nombre"
                   @error="sinImagen[p.movie_id] = true"/>
              <q-icon v-else name="movie" size="28px" color="grey-5"/>
            </div>
            <div>
              <div class="text-subtitle1 text-bold">{{ p.nombre }}</div>
              <q-badge color="blue-8" :label="p.formato"/>
              <q-badge v-if="p.duplicados" color="red" class="q-ml-xs" :label="p.duplicados + ' duplicado(s)'"/>
            </div>
          </q-card-section>
          <q-list dense separator>
            <q-item v-for="f in p.funciones" :key="f.id" clickable
                    :active="funcion && funcion.id === f.id" active-class="bg-blue-1"
                    @click="verFuncion(f)">
              <q-item-section avatar>
                <q-icon :name="f.duplicados ? 'warning' : 'event_seat'" :color="f.duplicados ? 'red' : 'grey'"/>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ hora(f.horaInicio) }} · SALA {{ f.sala }}</q-item-label>
                <q-item-label caption>{{ f.vendidos }} boletos vendidos</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge v-if="f.duplicados" color="red" :label="f.duplicados + ' duplicado(s)'"/>
                <q-badge v-else color="green" label="OK"/>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>
      <div class="col-12 text-grey q-pa-md" v-if="consultado && peliculas.length === 0">
        No hay funciones para mostrar
      </div>
    </div>

    <q-dialog v-model="dialog" maximized>
      <q-card v-if="funcion">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ funcion.nombre }} {{ funcion.formato }} · {{ hora(funcion.horaInicio) }} · SALA {{ funcion.sala }}
          </div>
          <q-space/>
          <q-btn icon="close" flat round dense v-close-popup/>
        </q-card-section>
        <q-card-section v-if="sala" class="q-pb-none">
          <div class="row items-center q-gutter-sm q-mb-sm">
            <div class="text-subtitle2">{{ sala.nombre }} · Capacidad {{ sala.capacidad }}</div>
            <q-space/>
            <q-badge color="green-6" label="Libre"/>
            <q-badge color="blue-6" label="Vendido"/>
            <q-badge color="red-6" label="Duplicado"/>
            <q-badge color="grey-5" label="Inactivo"/>
          </div>
          <div class="plano">
            <table class="plano-tabla">
              <thead>
              <tr>
                <th :colspan="sala.columnas + 1" class="bg-blue-10 text-bold text-white">PANTALLA</th>
              </tr>
              <tr>
                <th></th>
                <th v-for="col in columnasPlano" :key="col">{{ col }}</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="f in sala.filas" :key="f">
                <th>{{ letraFila(f) }}</th>
                <td v-for="col in columnasPlano" :key="col">
                  <q-btn v-if="butaca(f, col)" dense unelevated size="sm" class="full-width"
                         :color="colorButaca(butaca(f, col))"
                         :label="butaca(f, col).estado === 'INACTIVO' ? '' : butaca(f, col).letra + '-' + col"
                         :disable="butaca(f, col).estado === 'INACTIVO' || butaca(f, col).estado === 'LIBRE'"
                         @click="filtro = butaca(f, col).letra + '-' + col; soloBoletosDuplicados = false">
                    <q-tooltip v-if="butaca(f, col).compradores.length">
                      <div v-for="(c, k) in butaca(f, col).compradores" :key="k">{{ c }}</div>
                    </q-tooltip>
                  </q-btn>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
          <div class="text-caption text-grey q-mt-xs">Pasa el cursor sobre una butaca para ver quién la compró o haz clic para filtrar la tabla.</div>
        </q-card-section>
        <q-card-section>
          <q-toggle v-model="soloBoletosDuplicados" label="Mostrar solo butacas duplicadas"/>
          <q-table dense flat bordered :rows="boletosFiltrados" :columns="columns" row-key="id"
                   :loading="loadingBoletos" :rows-per-page-options="[0]" :filter="filtro"
                   :row-class="r => r.duplicado ? 'bg-red-1' : ''">
            <template v-slot:top-right>
              <q-input outlined dense debounce="300" v-model="filtro" placeholder="Buscar">
                <template v-slot:append><q-icon name="search"/></template>
              </q-input>
            </template>
            <template v-slot:body-cell-butaca="props">
              <q-td :props="props">
                <q-badge :color="props.row.duplicado ? 'red' : 'grey-7'" :label="props.value"/>
                <span v-if="props.row.duplicado" class="text-red text-bold q-ml-xs">x{{ props.row.veces }}</span>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import {date} from "quasar";

export default {
  name: `BoletosDuplicados`,
  data() {
    return {
      fecha: date.formatDate(new Date(), "YYYY-MM-DD"),
      url: process.env.API,
      sinImagen: {},
      sala: null,
      seats: [],
      funciones: [],
      loading: false,
      consultado: false,
      soloDuplicados: false,
      dialog: false,
      funcion: null,
      boletos: [],
      loadingBoletos: false,
      soloBoletosDuplicados: false,
      filtro: '',
      columns: [
        {label: 'BUTACA', name: 'butaca', field: r => r.letra + '-' + r.columna, align: 'left', sortable: true},
        {label: 'COMPRADOR', name: 'nombre', field: 'nombreRazonSocial', align: 'left', sortable: true},
        {label: 'CI/NIT', name: 'documento', field: r => (r.numeroDocumento || '') + (r.complemento ? '-' + r.complemento : ''), align: 'left'},
        {label: 'VENTA', name: 'sale_id', field: 'sale_id', align: 'left', sortable: true},
        {label: 'TIPO', name: 'venta', field: r => r.venta === 'F' ? 'FACTURA' : 'RECIBO', align: 'left'},
        {label: 'N° FACT.', name: 'numeroFactura', field: 'numeroFactura', align: 'left'},
        {label: 'COSTO', name: 'costo', field: 'costo', align: 'right'},
        {label: 'PAGO', name: 'pago', field: r => this.pago(r), align: 'left'},
        {label: 'CAJERO', name: 'usuario', field: 'usuario', align: 'left', sortable: true},
        {label: 'FECHA VENTA', name: 'fechaEmision', field: 'fechaEmision', align: 'left', sortable: true},
      ],
    }
  },
  created() {
    this.consultar()
  },
  methods: {
    consultar() {
      if (!this.fecha) return
      this.loading = true
      this.$api.post('boletosDuplicados/funciones', {fecha: this.fecha}).then(res => {
        this.funciones = res.data.map(f => ({...f, duplicados: f.vendidos - f.butacas}))
        this.consultado = true
      }).catch(e => {
        this.$q.notify({type: 'negative', message: e.response?.data?.message || 'Error al consultar'})
      }).finally(() => {
        this.loading = false
      })
    },
    verFuncion(f) {
      this.funcion = f
      this.boletos = []
      this.sala = null
      this.seats = []
      this.filtro = ''
      this.soloBoletosDuplicados = f.duplicados > 0
      this.dialog = true
      this.loadingBoletos = true
      this.$api.post('boletosDuplicados/boletos', {id: f.id}).then(res => {
        this.boletos = res.data.boletos
        this.sala = res.data.sala
        this.seats = res.data.seats
      }).catch(e => {
        this.$q.notify({type: 'negative', message: e.response?.data?.message || 'Error al cargar boletos'})
      }).finally(() => {
        this.loadingBoletos = false
      })
    },
    butaca(fila, columna) {
      return this.plano[fila + '-' + columna]
    },
    letraFila(fila) {
      const s = this.seats.find(x => x.fila == fila)
      return s ? s.letra : ''
    },
    colorButaca(b) {
      if (b.estado === 'DUPLICADO') return 'red-6'
      if (b.estado === 'VENDIDO') return 'blue-6'
      if (b.estado === 'LIBRE') return 'green-6'
      return 'grey-4'
    },
    hora(h) {
      // horaInicio llega como datetime (YYYY-MM-DD HH:mm:ss)
      return h ? h.substring(11, 16) : ''
    },
    pago(r) {
      if (r.cortesia === 'SI') return 'CORTESIA'
      if (r.vip === 'SI') return 'VIP'
      if (r.pagoQr) return 'QR'
      if (r.credito === 'SI') return 'TARJETA'
      return 'EFECTIVO'
    },
  },
  computed: {
    peliculas() {
      const grupos = {}
      this.funciones
        .filter(f => !this.soloDuplicados || f.duplicados > 0)
        .forEach(f => {
          if (!grupos[f.movie_id]) grupos[f.movie_id] = {movie_id: f.movie_id, nombre: f.nombre, formato: f.formato, imagen: f.imagen, duplicados: 0, funciones: []}
          grupos[f.movie_id].funciones.push(f)
          grupos[f.movie_id].duplicados += f.duplicados
        })
      return Object.values(grupos)
    },
    // Columnas en el mismo orden que la pantalla de venta (de mayor a menor)
    columnasPlano() {
      if (!this.sala) return []
      return Array.from({length: this.sala.columnas}, (_, i) => this.sala.columnas - i)
    },
    // Butacas por "fila-columna" con su estado y quienes las compraron
    plano() {
      const plano = {}
      this.seats.forEach(s => {
        plano[s.fila + '-' + s.columna] = {
          letra: s.letra,
          estado: s.activo === 'ACTIVO' ? 'LIBRE' : 'INACTIVO',
          compradores: [],
        }
      })
      this.boletos.forEach(b => {
        const s = plano[b.fila + '-' + b.columna]
        if (!s) return
        s.estado = b.duplicado ? 'DUPLICADO' : 'VENDIDO'
        s.compradores.push(`${b.nombreRazonSocial || 'S/N'} · venta ${b.sale_id} · ${b.usuario || ''}`)
      })
      return plano
    },
    totalDuplicados() {
      return this.funciones.reduce((s, f) => s + f.duplicados, 0)
    },
    funcionesConDuplicados() {
      return this.funciones.filter(f => f.duplicados > 0).length
    },
    boletosFiltrados() {
      return this.soloBoletosDuplicados ? this.boletos.filter(b => b.duplicado) : this.boletos
    },
  },
}
</script>

<style scoped>
.poster {
  width: 48px;
  height: 68px;
  flex: 0 0 48px;
  border-radius: 6px;
  overflow: hidden;
  background: #263238;
  display: flex;
  align-items: center;
  justify-content: center;
}

.poster img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.plano {
  overflow-x: auto;
}

.plano-tabla, .plano-tabla th, .plano-tabla td {
  border-collapse: collapse;
  border: 1px solid #ddd;
  padding: 1px;
  text-align: center;
}

.plano-tabla td {
  min-width: 44px;
}
</style>
