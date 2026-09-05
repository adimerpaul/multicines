<template>
  <q-page>
    <q-card>
      <q-card-section class="q-py-xs bg-green-7 text-white text-bold">
        <div class="row">
          <div class="col-2 flex flex-center">
            <q-icon name="live_tv" left/>
            PANEL DE VENTAS
          </div>
          <div class="col-2">
            <q-input label="fecha" @update:model-value="myMovies" :min="now" type="date" label-color="white" outlined
                     dense v-model="fecha"/>
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-py-none">
        <div class="row">
          <div class="col-6">
            <div class="text-h6">
              <q-icon name="o_confirmation_number"/>
              PELICULAS
            </div>
          </div>
          <div class="col-6 text-right">
            <div class="text-subtitle2">Venta de boletos vendidos: {{ totalventa }}</div>
          </div>
        </div>
      </q-card-section>
      <q-separator/>
      <q-card-section>
        <div class="row">
          <div class="col-2" v-for="m in movies" :key="m.id">
            <q-card @click="myHours(m)" class="q-ma-xs cursor-pointer movie-card"
                    :class="{'movie-card--activa': movie.id === m.id}"
                    :style="posterStyle(m)">
              <q-icon v-if="!m.imagen" name="movie" size="44px" class="movie-sin-imagen"/>
              <div class="movie-degradado column justify-end q-pa-sm">
                <div class="movie-name">{{ m.nombre }}</div>
                <div class="row items-center q-gutter-xs no-wrap q-mt-xs">
                  <q-badge color="blue-8">{{ m.formato }}</q-badge>
                  <q-badge color="grey-9">{{ m.duracion }} min</q-badge>
                  <q-space/>
                  <q-badge :color="parseInt(m.cantidad) > 0 ? 'white' : 'grey-8'"
                           :text-color="parseInt(m.cantidad) > 0 ? 'green-9' : 'white'" class="text-bold">
                    <q-icon name="o_confirmation_number" size="12px" class="q-mr-xs"/>
                    {{ m.cantidad }}
                  </q-badge>
                </div>
              </div>
            </q-card>
          </div>

        </div>
      </q-card-section>
      <q-separator/>
      <q-card-section>


      </q-card-section>
      <q-separator/>
      <q-card-section>
        <div class="row">
          <div class="col-4">
            <div class="text-bold text-center">{{ movie.nombre }}</div>
            <q-btn @click="clickSala(h)" :loading="loading" size="12px" class="q-ma-xs full-width flex flex-center"
                   v-for="h in hours" color="primary" :key="h.id">
              <q-icon name="schedule" left/>
              <q-badge color="red">{{ h.sala.nombre }}</q-badge>
              {{ h.horaInicio.substring(10, 16) }} - {{ h.price.precio + 'Bs' }}
            </q-btn>
          </div>
          <div class="col-4">
          </div>
          <div class="col-4">
            <div class="text-bold q-pa-xs bg-grey-8 text-white">
              <div class="row">
                <div class="col-4">Detalle venta</div>
                <div class="col-4">
                  <q-btn color="red" :loading="loading" @click="momentaneoDeleteAll()" dense label="Cancelar Venta"
                         no-caps icon="o_delete"/>
                </div>
                <div class="col-4">
                  <q-btn color="green" :disable="detalleVenta.length==0" :loading="loading" @click="saleCreate" dense
                         label="Terminar Venta" no-caps icon="check_circle"/>
                </div>
              </div>
            </div>
            <table>
              <thead>
              <tr>
                <th colspan="4">Detalle de venta</th>
              </tr>
              <tr>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Pelicula</th>
                <th>Subtotal</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(d,i) in detalleVenta" :key="i">
                <td class="tdx">
                  <div class="text-bold">{{ horaTexto(d.fecha) }}</div>
                  <div style="font-size: 11px">{{ diaTexto(d.fecha) }}</div>
                </td>
                <td class="tdx">{{ d.cantidad }}</td>
                <td class="tdx">{{ d.pelicula }}</td>
                <td class="tdx text-right">{{ d.subtotal }} Bs</td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="3" class=" tdx text-right text-bold">TOTAL:</td>
                <td class="text-right tdx">{{ total }}Bs</td>
              </tr>
              </tfoot>
            </table>
          </div>

          <!--      <div class="col-4">-->
          <!--        <div class="row">-->
          <!--          <div class="col-12">-->
          <!--            <div class="text-bold">Operaciones</div>-->
          <!--          </div>-->
          <!--          <div class="col-6">-->
          <!--            <q-btn size="12px" class="q-ma-xs flex flex-center full-width">-->
          <!--              <div class="row items-center no-wrap">-->
          <!--                <q-icon left name="remove_circle_outline" />-->
          <!--                <div class="text-center">-->
          <!--                  Entradas-->
          <!--                </div>-->
          <!--              </div>-->
          <!--            </q-btn>-->
          <!--          </div>-->
          <!--          <div class="col-6">-->
          <!--            <q-btn size="12px" class="q-ma-xs flex flex-center full-width">-->
          <!--              <div class="row items-center no-wrap">-->
          <!--                <q-icon left name="add_circle_outline" />-->
          <!--                <div class="text-center">-->
          <!--                  Entradas-->
          <!--                </div>-->
          <!--              </div>-->
          <!--            </q-btn>-->
          <!--          </div>-->
          <!--          <div class="col-6">-->
          <!--            <q-btn color="red" size="12px" class="q-ma-xs flex flex-center full-width">-->
          <!--              <div class="row items-center no-wrap">-->
          <!--                <q-icon left name="highlight_off" />-->
          <!--                <div class="text-center">-->
          <!--                  Entradas-->
          <!--                </div>-->
          <!--              </div>-->
          <!--            </q-btn>-->
          <!--          </div>-->
          <!--          <div class="col-6">-->
          <!--            <q-btn size="12px" class="q-ma-xs flex flex-center full-width">-->
          <!--              <div class="row items-center no-wrap">-->
          <!--                <q-icon left name="add_circle_outline" />-->
          <!--                <div class="text-center">-->
          <!--                  Entradas-->
          <!--                </div>-->
          <!--              </div>-->
          <!--            </q-btn>-->
          <!--          </div>-->
          <!--        </div>-->
          <!--      </div>-->
        </div>

      </q-card-section>
    </q-card>
    <q-dialog full-width v-model="salaDialog" persistent>
      <q-card>
        <q-card-section>
          <div class="row">
            <div class="col-12 row items-center q-pb-none">
              <div class="col-4">
                <div class="text-bold">{{ movie.nombre }} {{ movie.formato }}</div>
                <!-- Fecha y hora de la funcion a la vista al elegir butacas -->
                <div class="funcion-cuando row items-center q-gutter-x-sm">
                  <q-badge color="red">{{ hour.sala.nombre }}</q-badge>
                  <div><q-icon name="event"/> {{ diaTexto(hour.horaInicio) }}</div>
                  <div class="funcion-hora"><q-icon name="schedule"/> {{ horaTexto(hour.horaInicio) }}</div>
                  <q-badge outline color="grey-9">{{ hour.price.precio }} Bs</q-badge>
                </div>
              </div>
              <div class="q-pa-xs flex flex-center">
                <div style="font-size: 12px; font-weight: bold">
                  <q-icon name="event_seat"/>
                  Disponibles: <span style="font-size: 14px;font-weight:  bolder;">{{ disponible }}|</span>
                  <q-icon name="credit_card"/>
                  Vendidas: <span style="font-size: 14px;font-weight:  bolder;">{{ vendido }}|</span>
                  <q-icon name="settings_backup_restore"/>
                  Devueltas: <span style="font-size: 14px;font-weight:  bolder;">{{ devueltos }}|</span>
                  <q-icon name="apartment"/>
                  Capacidad:<span style="font-size: 14px;font-weight:  bolder;">{{ capacidad }}</span>
                </div>
              </div>
              <q-space/>
              <div class="text-bold">CANTIDAD: <span
                class="text-red text-bold text-h4">{{ seleccionados.length }}</span> SUBTOTAL: <span
                class="text-red text-bold text-h4">{{ seleccionados.length * hour.price.precio }}Bs. </span></div>
              <q-btn icon="highlight_off" color="red" flat round dense @click="salaDialogClose"/>
            </div>
            <div class="col-12">
              <table>
                <thead>
                <tr>
                  <th :colspan="parseInt(hour.sala.columnas)+1" class="bg-blue-10 text-bold text-white">PANTALLA</th>
                </tr>
                <tr>
                  <th></th>
                  <th v-for="(c,i) in parseInt(hour.sala.columnas)" :key="i">{{ hour.sala.columnas - c + 1 }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(f,i) in parseInt(hour.sala.filas)" :key="i">
                  <th>{{ letra[i + 1] }}</th>
                  <td v-for="(c,j) in parseInt(hour.sala.columnas)" click="cambio(f,c)" :key="j" class="text-center tdx"
                      style="padding: 0px;margin: 0px;border: 0px">
                    <q-btn color="green-6" class="full-width"
                           :label="letra[i+1]+'-'+(hour.sala.columnas-c+1).toString()"
                           v-if="seats[hour.sala.columnas*(f-1)+(c-1)]['activo']=='LIBRE'"
                           @click="seleccionar(hour,seats[hour.sala.columnas*(f-1)+(c-1)])"/>
                    <q-btn color="red-6" class="full-width"
                           v-else-if="seats[hour.sala.columnas*(f-1)+(c-1)]['activo']=='OCUPADO'"/>
                    <q-btn color="yellow-6" class="full-width"
                           v-else-if="seats[hour.sala.columnas*(f-1)+(c-1)]['activo']=='RESERVADO'"/>
                    <q-btn color="blue-6" class="full-width" :label="letra[i+1]+'-'+(hour.sala.columnas-c+1).toString()"
                           v-else-if="seats[hour.sala.columnas*(f-1)+(c-1)]['activo']=='SELECCIONADO'"
                           @click="seleccionar(hour,seats[hour.sala.columnas*(f-1)+(c-1)])"/>
                    <q-btn color="grey-6" class="full-width" v-else/>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
            <div class="col-12 text-center">
              <q-btn icon="check_circle" class="q-ml-lg" :disable="seleccionados.length==0" color="primary"
                     :loading="loading" label="Agregar" @click="salaDialog=false"/>
              <q-btn icon="highlight_off" class="q-ml-lg" color="red" label="Cancelar" @click="salaDialogClose"/>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog full-width v-model="saleDialog" persistent>
      <q-card class="venta-dialog">
        <q-card-section class="venta-header row items-center no-wrap q-py-sm">
          <q-icon name="point_of_sale" size="28px" class="q-mr-sm"/>
          <div>
            <div class="text-h6 text-white line-height-1">Realizar venta</div>
            <div class="venta-header__sub">
              {{ resumenVenta.length }} funcion(es) · {{ cantidades }} butaca(s)
            </div>
          </div>
          <q-space/>
          <div class="text-right">
            <div class="venta-header__sub">TOTAL A COBRAR</div>
            <div class="venta-total">{{ total }} Bs</div>
          </div>
        </q-card-section>
        <q-form @submit.prevent="saleInsert">
          <q-card-section class="q-pa-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-5">
                <div class="venta-titulo">
                  <q-icon name="fact_check" size="18px"/>
                  Revise lo que esta vendiendo
                </div>
                <q-card v-for="f in resumenVenta" :key="f.programa_id" flat bordered
                        class="funcion-card q-mb-sm" :class="{'funcion-card--alerta': f.iniciada}">
                  <q-card-section class="q-pa-sm">
                    <div class="row items-start no-wrap">
                      <div class="funcion-pelicula">{{ f.pelicula }}</div>
                      <q-space/>
                      <q-badge v-if="f.sala" color="blue-9" class="q-ml-xs">{{ f.sala }}</q-badge>
                    </div>
                    <div class="funcion-cuando row items-center q-gutter-x-sm q-mt-xs">
                      <div><q-icon name="event"/> {{ f.dia }}</div>
                      <div class="funcion-hora"><q-icon name="schedule"/> {{ f.hora }}</div>
                      <q-badge v-if="f.nroFuncion" outline color="grey-8">Funcion {{ f.nroFuncion }}</q-badge>
                    </div>
                    <div v-if="f.iniciada" class="funcion-alerta q-mt-xs">
                      <q-icon name="warning" size="16px"/>
                      Esta funcion ya empezo. Confirme con el cliente antes de cobrar.
                    </div>
                    <div class="q-mt-xs">
                      <q-chip v-for="b in f.butacas" :key="b" dense square color="blue-1" text-color="blue-10"
                              class="butaca-chip" icon="event_seat">{{ b }}</q-chip>
                    </div>
                    <div class="row items-center q-mt-xs">
                      <div class="text-caption text-grey-8">{{ f.cantidad }} x {{ f.precio }} Bs</div>
                      <q-space/>
                      <div class="text-bold">{{ f.subtotal }} Bs</div>
                    </div>
                  </q-card-section>
                </q-card>
                <div v-if="!resumenVenta.length" class="text-grey-7 text-center q-pa-md">
                  No hay butacas seleccionadas.
                </div>
              </div>
              <div class="col-12 col-md-7">
                <div class="venta-titulo">
                  <q-icon name="badge" size="18px"/>
                  Datos del cliente
                </div>
                <div class="row q-col-gutter-sm">
                  <div class="col-6 col-md-3">
                    <q-input outlined dense label="NIT/CARNET"
                             required v-model="client.numeroDocumento"
                             debounce="300"
                             @update:modelValue="searchClient"
                    />
                  </div>
                  <div class="col-6 col-md-2">
                    <q-input outlined dense label="Complemento" v-model="client.complemento"
                             debounce="300"
                             @update:modelValue="searchClient"
                             style="text-transform: uppercase"/>
                  </div>
                  <div class="col-12 col-md-7">
                    <q-input outlined dense label="Nombre / Razon social" required v-model="client.nombreRazonSocial"
                             style="text-transform: uppercase"/>
                  </div>
                  <div class="col-12 col-md-6">
                    <q-select v-model="document" outlined dense label="Tipo de documento" :options="documents"
                              @update:model-value="validarnit"/>
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input outlined dense label="Email" v-model="client.email" type="email"/>
                  </div>
                </div>
                <div class="venta-titulo q-mt-md">
                  <q-icon name="payments" size="18px"/>
                  Cobro
                </div>
                <div class="row q-col-gutter-sm items-center">
                  <div class="col-6 col-md-3">
                    <q-input outlined dense label="TOTAL A PAGAR:" disable v-model="total" input-class="text-bold"/>
                  </div>
                  <div class="col-6 col-md-3">
                    <q-input outlined dense label="EFECTIVO BS." @keyup="cambio" v-model="efectivo"/>
                  </div>
                  <div class="col-6 col-md-3">
                    <q-input outlined dense label="CAMBIO:" disable v-model="cambio" input-class="text-bold"/>
                  </div>
                  <div class="col-6 col-md-3">
                    <q-toggle outlined :label="`${credito} T CREDITO`" v-model="credito" color="green" false-value="NO"
                              true-value="SI" :disable="qrId || qrPolling"/>
                  </div>
                  <div class="col-6 col-md-3">
                    <q-checkbox outlined label="N CORTESIA" @update:model-value="habilitarCortesia" v-model="cortesia"
                                color="primary"/>
                  </div>
                  <div class="col-6 col-md-3">
                    <q-toggle outlined :label="`${tarjeta} VIP`" v-model="tarjeta" color="green" false-value="NO"
                              true-value="SI"/>
                  </div>
                </div>
                <div class="coll-12">
                  <template v-if="tarjeta == 'SI'">
                    <q-form @submit.prevent="consultartarjeta">
                      <div class="row q-col-gutter-sm q-mt-xs items-center">
                        <div class="col-12 col-md-6">
                          <q-input outlined dense label="Codigo" v-model="codigo" @keyup="consultartarjeta"/>
                        </div>
                        <div class="col-12 col-md-6">
                          <q-banner dense class="bg-grey-2">Saldo :{{ nombresaldo.saldo }} -- {{ nombresaldo.nombre }}</q-banner>
                        </div>
                      </div>
                    </q-form>
                  </template>
                  <template v-if="cortesia">
                    <div class="row q-mt-xs">
                      <div class="col-12">
                        <q-checkbox @click="verificarCortesia" v-for="c in frees" :key="c.id" v-model="c.status"
                                    :label="c.id+''" color="teal"/>
                      </div>
                      <div class="col-12">
                        <div class="text-bold text-center text-h5"> {{ marcados }} - {{ cantidades }}</div>
                      </div>
                    </div>
                  </template>
                </div>
                <div class="col-12 text-red text-bold q-mt-sm" v-if="error!=''">
                  {{ error }}
                </div>
                <div class="row q-col-gutter-sm items-stretch q-mt-xs">
                  <div class="col-12 col-md-4">
                    <q-btn type="submit" class="full-width q-py-sm" icon="o_add_circle" label="Realizar venta" :loading="loading"
                           no-caps color="green" :disable="btn || qrPolling"/>
                  </div>
                  <div class="col-12 col-md-3">
                    <q-btn class="full-width q-py-sm" icon="qr_code_2" @click="generarQr" label="Generar QR" no-caps
                           color="teal" :loading="loading" :disable="qrPolling || !numeroDocumentoValido"/>
                  </div>
                  <div class="col-12 col-md-2">
                    <q-btn v-if="qrId" class="full-width q-py-sm" icon="cancel" @click="cancelarQr" label="Cancelar QR" no-caps
                           color="warning" :loading="loading"/>
                    <div v-else class="full-width q-py-sm"></div>
                  </div>
                  <div class="col-12 col-md-3">
                    <q-btn class="full-width q-py-sm" icon="undo" @click="cancelarDialogoVenta" label="Atras" no-caps
                           color="red"/>
                  </div>
                  <div class="col-12" v-if="qrImage">
                    <q-card flat bordered class="bg-teal-1">
                      <q-card-section class="q-pb-sm">
                        <div><strong>ID QR:</strong> {{ qrId }}</div>
                        <div><strong>ID Venta:</strong> {{ qrTransactionId }}</div>
                        <div><strong>Estado:</strong> {{ qrStatusMessage }}</div>
                      </q-card-section>
                      <q-card-section class="row justify-center q-pt-none">
                        <img :src="qrImage" alt="QR de pago" style="max-width: 260px; width: 100%;">
                      </q-card-section>
                    </q-card>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-form>
      </q-card>
    </q-dialog>
    <div id="myelement" class="hidden"></div>

  </q-page>
</template>

<script>
import {date} from "quasar";
import {globalStore} from "stores/globalStore";
import {Printd} from "printd";
import conversor from "conversor-numero-a-letras-es-ar";
import QRCode from "qrcode";
import moment from "moment"
import { printBoleto, printPromo, printFactura } from '../addons/impresion'

export default {
  name: `Sale`,

  data() {
    return {
      store: globalStore(),
      saleDialog: false,
      letra: ['', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB'],
      url: process.env.API,
      salaDialog: false,
      now: date.formatDate(new Date(), "YYYY-MM-DD"),
      fecha: date.formatDate(new Date(), "YYYY-MM-DD"),
      movies: [],
      movie: {},
      hours: [],
      nombresaldo: {},
      efectivo: '',
      hour: {},
      momentaneos: [],
      momentaneo: {},
      seats: [],
      seat: {},
      client: {complemento: '', vip: 'NO', credito: 'NO'},
      temporal: [],
      numeroboleto: 0,
      loading: false,
      documents: [],
      document: {},
      credito: 'NO',
      tarjeta: 'NO',
      cortesia: false,
      disponible: 0,
      vendido: 0,
      devueltos: 0,
      capacidad: 0,
      totalventa: 0,
      // cine: {},
      // leyendas: [],
      error: '',
      btn: false,
      tienerebaja: false,
      booltarjeta: false,
      codigo: '',
      frees: [],
      pagoQr: false,
      qrId: '',
      qrImage: '',
      qrPolling: false,
      qrPollTimer: null,
      qrPaymentConfirmed: false,
      qrPaymentData: [],
      qrStatusMessage: '',
      qrTransactionId: '',
      qrSaleProcessing: false,
      opts: {
        errorCorrectionLevel: 'M',
        type: 'png',
        quality: 0.95,
        width: 100,
        margin: 1,
        color: {
          dark: '#000000',
          light: '#FFF',
        },
      }
    }
  },
  created() {
    // Una sola peticion inicial (antes eran 6: document, free, momentaneo, eventSearch, totalventa, movies)
    this.loadSale(this.fecha)
  },
  beforeUnmount() {
    this.stopQrPolling()
  },
  methods: {
    // "Sabado 30/08/2026" a partir de la hora de inicio de la funcion
    diaTexto(valor) {
      const dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado']
      const fecha = moment(valor)
      return fecha.isValid() ? `${dias[fecha.day()]} ${fecha.format('DD/MM/YYYY')}` : ''
    },
    horaTexto(valor) {
      const fecha = moment(valor)
      return fecha.isValid() ? fecha.format('HH:mm') : ''
    },
    resetQrState() {
      this.stopQrPolling()
      this.pagoQr = false
      this.qrId = ''
      this.qrImage = ''
      this.qrPolling = false
      this.qrPaymentConfirmed = false
      this.qrPaymentData = []
      this.qrStatusMessage = ''
      this.qrTransactionId = ''
      this.qrSaleProcessing = false
    },
    stopQrPolling() {
      if (this.qrPollTimer) {
        clearInterval(this.qrPollTimer)
        this.qrPollTimer = null
      }
      this.qrPolling = false
    },
    startQrPolling() {
      this.stopQrPolling()
      this.qrPolling = true
      this.qrStatusMessage = 'Esperando pago QR'
      this.verificarEstadoQr()
      this.qrPollTimer = setInterval(() => {
        this.verificarEstadoQr()
      }, 3000)
    },
    // El poster va de fondo de la tarjeta: si no hay imagen queda el degradado del CSS
    posterStyle(m) {
      if (!m.imagen) {
        return {}
      }
      return {backgroundImage: `url('${this.url}../imagen/${m.imagen}')`}
    },
    // Carga todo el panel en una sola peticion
    loadSale(fecha) {
      this.movie = {}
      this.hours = []
      this.loading = true
      this.$api.post('saleInit', {fecha: fecha}).then(res => {
        const data = res.data
        this.documents = data.documents.map(r => ({...r, label: r.descripcion}))
        this.document = this.documents[0]
        this.frees = data.frees.map(r => ({...r, status: false}))
        this.momentaneos = data.momentaneos
        this.store.eventNumber = data.eventNumber
        this.totalventa = data.totalventa
        this.movies = data.movies
      }).finally(() => {
        this.loading = false
      })
    },
    habilitarCortesia() {
      this.btn = !this.cortesia
    },
    verificarCortesia() {
      if (this.marcados == this.cantidades) {
        this.btn = false
      } else {
        this.btn = true
      }
    },
    consultartarjeta() {
      if (this.codigo != '' || this.codigo != undefined) {
        //this.$q.loading.show()
        this.nombresaldo = {}
        this.codigo = this.codigo.replaceAll(' ', '');
        if (this.tienerebaja) {
          this.momentaneos.forEach(r => {
            r.precio = (1.25 * r.precio).toFixed(2)
            r.subtotal = (1.25 * r.subtotal).toFixed(2)
          })
          this.btn = false
          this.tienerebaja = false
        }
        this.$api.get('validarTarjeta/' + this.codigo).then(res => {
          console.log(res.data)
          this.$q.loading.hide()
          if (res.data == '0' || res.data == '') {

          } else {
            this.nombresaldo = res.data
            // console.log(res.data)
            if (!this.tienerebaja) {
              this.momentaneos.forEach(r => {
                r.precio = (0.8 * r.precio).toFixed(2)
                r.subtotal = (0.8 * r.subtotal).toFixed(2)
              })
              this.tienerebaja = true
              if (parseFloat(this.total) <= parseFloat(this.nombresaldo.saldo)) {

                this.btn = false
              } else {
                this.btn = true
              }
            }
          }
        })
      }
    },
    // cargarLeyenda() {
    //   this.$api.post('listleyenda', {codigo: '590000'}).then(res => {
    //     // console.log(res.data)
    //     this.leyendas = res.data;
    //   })
    // },
    // encabezado() {
    //   this.$api.get('datocine').then(res => {
    //     this.cine = res.data;
    //     // console.log(this.cine)
    //   })
    // },
    generarQr() {
      if (this.qrPolling) {
        return
      }
      if (!this.numeroDocumentoValido) {
        this.$q.notify({
          color: 'warning',
          textColor: 'black',
          message: 'Debe ingresar CI/NIT antes de generar el QR',
          position: 'top',
          timeout: 4000,
        })
        return
      }
      this.loading = true
      this.credito = 'NO'
      this.resetQrState()
      this.$api.post('generarQr', {
        client: this.client,
        montoTotal: this.total,
        tipoVenta: 'BOLETERIA',
      }).then(res => {
        this.pagoQr = true
        this.qrImage = res.data.qr
        this.qrId = res.data.qrId
        this.qrTransactionId = res.data.transactionId
        this.qrStatusMessage = 'QR generado, esperando pago'
        this.startQrPolling()
        this.$q.notify({
          message: res.data.message || 'QR generado correctamente',
          color: 'positive',
          icon: 'qr_code_2'
        })
        this.loading = false
      }).catch(err => {
        console.log(err)
        this.resetQrState()
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || 'No se pudo generar el QR',
          position: 'top',
          timeout: 5000,
        })
        this.loading = false
      })
    },
    verificarEstadoQr() {
      if (!this.qrId || this.qrPaymentConfirmed) {
        return
      }
      this.$api.get('statusQr/' + this.qrId).then(res => {
        const status = parseInt(res.data.statusQrCode)
        this.qrPaymentData = Array.isArray(res.data.payment) ? res.data.payment : []
        if (status === 1) {
          if (this.qrSaleProcessing) {
            return
          }
          this.stopQrPolling()
          this.pagoQr = true
          this.qrPaymentConfirmed = true
          this.qrSaleProcessing = true
          this.qrStatusMessage = 'Pago QR confirmado'
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            message: 'Pago QR confirmado. Registrando venta e imprimiendo...',
            position: 'top',
            timeout: 3000,
          })
          this.saleInsert(true)
          return
        }
        if (status === 9) {
          this.stopQrPolling()
          this.qrStatusMessage = 'QR anulado'
          this.$q.notify({
            color: 'warning',
            textColor: 'black',
            message: 'El QR fue anulado',
            position: 'top',
            timeout: 4000,
          })
          return
        }
        this.qrStatusMessage = 'Esperando pago QR'
      }).catch(err => {
        console.log(err)
        this.qrStatusMessage = err.response?.data?.message || 'Error al verificar QR'
      })
    },
    cancelarQr() {
      if (!this.qrId) {
        return
      }
      const qrId = this.qrId
      this.stopQrPolling()
      this.loading = true
      this.$api.post('cancelarQr/' + qrId).then(res => {
        this.$q.notify({
          color: res.data.cancelled ? 'positive' : 'warning',
          textColor: 'white',
          message: res.data.message || 'QR cancelado',
          position: 'top',
          timeout: 5000,
        })
      }).catch(err => {
        console.log(err)
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || 'No se pudo cancelar el QR',
          position: 'top',
          timeout: 5000,
        })
      }).finally(() => {
        this.loading = false
        this.resetQrState()
      })
    },
    cancelarDialogoVenta() {
      this.resetQrState()
      this.saleDialog = false
      this.consultartarjeta()
    },
    saleInsert(qrConfirmado = false) {
      // Un solo envio a la vez: dos POST seguidos generaban dos ventas.
      if (this.loading) {
        return
      }
      if (this.qrSaleProcessing && !qrConfirmado) {
        return
      }
      // if (this.client.numeroDocumento==0) {
      //   this.$q.notify({
      //     color: 'red',
      //     textColor: 'white',
      //     message: 'Debe ingresar un numero de documento'
      //   })
      //  return false
      // }
      if (this.qrId && !qrConfirmado && !this.qrPaymentConfirmed) {
        this.$q.notify({
          color: 'warning',
          textColor: 'black',
          message: 'El QR aun no fue pagado. Espere la confirmacion o cancele el QR.',
          position: 'top',
          timeout: 5000,
        })
        return
      }
      this.error = ''
      this.loading = true
      this.client.codigoTipoDocumentoIdentidad = this.document.codigoClasificador
      this.client.email = this.client.email == undefined ? '' : this.client.email

      this.$api.post('sale', {
        client: this.client,
        montoTotal: this.total,
        detalleVenta: this.detalleVenta,
        vip: this.tarjeta,
        tarjeta: this.credito,
        codigoTarjeta: this.codigo,
        cortesia: this.cortesia ? 'SI' : 'NO',
        frees: this.frees,
        pagoQr: this.pagoQr && this.qrPaymentConfirmed,
        qrId: this.qrId,
        qrTransactionId: this.qrTransactionId,
      }).then(res => {
        this.stopQrPolling()
        this.tarjeta = 'NO'
        // console.log(res.data)
        if (res.data.error != '') {
          this.$q.notify({
            color: 'negative',
            textColor: 'white',
            message: res.data.error,
            position: 'top',
            timeout: 5000,
          })
        }
        if (res.data.sale.siatEnviado) {
          this.printFactura(res.data.sale)
        }
        let valpromo = 0
        res.data.tickets.forEach(r => {
          if (r.promo) valpromo++
          this.boletoprint(r)
        })
        //console.log(valpromo)
        if (valpromo > 1) {
          let promototal = Math.trunc(valpromo / 2)
          //console.log(promototal)
          for (let index = 0; index < promototal; index++) {
            console.log(res.data.sale)
            this.printPromo(res.data.sale)
          }
        }
        // 2 peticiones: limpiar reservas temporales y recargar el panel
        this.momentaneoDeleteAll(true)
        this.client = {complemento: '', vip: 'NO', credito: 'NO'}
        this.resetQrState()
        this.saleDialog = false
        this.loading = false
      }).finally(() => {
        this.loading = false
      }).catch(err => {
        //this.error=err.response.data.message
        this.qrSaleProcessing = false
        this.loading = false
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || err.message,
          position: 'top',
          timeout: 5000,
        })
      })
    },

    printPromo(info){
      printPromo(info, this.store.cine)
    },
    boletoprint(bol) {
      printBoleto(bol, this.store.cine)
    },
    async printFactura(factura){
      await printFactura(factura, this.store.cine, factura.leyenda, this.opts)
    },
    validarnit() {
      if (this.document == this.documents[4]) {
        this.$api.get('validanit/' + this.client.numeroDocumento).then(res => {
          console.log(res.data)
          this.$q.notify({
            message: res.data.RespuestaVerificarNit.mensajesList.descripcion,
            color: 'teal',
            icon: 'info'
          })
        })

      }
    },
    searchClient() {
      // console.log(this.client)
      this.document = this.documents[0]
      this.client.nombreRazonSocial = ''

      this.client.email = ''
      this.client.id = undefined
      this.$api.post('searchClient', this.client).then(res => {
        // console.log(res.data)
        //console.log(res.data.codigoTipoDocumentoIdentidad)
        if (res.data.nombreRazonSocial != undefined) {
          this.client.nombreRazonSocial = res.data.nombreRazonSocial
          this.client.email = res.data.email
          this.client.id = res.data.id
          let documento = this.documents.find(r => r.codigoClasificador == res.data.codigoTipoDocumentoIdentidad)
          documento.label = documento.descripcion
          this.document = documento
        }
        if (this.document.codigoClasificador == 5) this.validarnit()
      })
    },
    saleCreate() {
      this.tienerebaja = false
      this.codigo = ''
      this.nombresaldo = {}
      this.resetQrState()
      this.saleDialog = true
      this.client = {complemento: '', vip: 'NO', credito: 'NO'}
    },
    momentaneoDeleteAll(recargar = false) {
      this.loading = true
      this.$api.post('momentaneoDeleteall').then(() => {
        this.momentaneos = []
        if (recargar) {
          this.loadSale(this.fecha)
        }
      }).finally(() => {
        this.loading = false
      })
    },
    salaDialogClose() {
      const programaId = this.hour.id
      this.salaDialog = false
      this.$api.post('momentaneoDeleteUser', {
        programa_id: programaId
      }).then(() => {
        this.momentaneos = this.momentaneos.filter(m => m.programa_id != programaId)
      })
    },
    // Butacas y resumen de la funcion en una sola peticion
    clickSala(h) {
      this.hour = h
      this.loading = true
      this.$api.post('salaData', {id: h.id}).then(res => {
        this.seats = res.data.seats
        let valores = res.data.resumen || {salatotal: 0, venta: 0, temp: 0, dev: 0}
        this.disponible = parseInt(valores.salatotal) - parseInt(valores.venta) - parseInt(valores.temp)
        this.vendido = parseInt(valores.venta)
        this.devueltos = parseInt(valores.dev)
        this.capacidad = parseInt(valores.salatotal)
        this.salaDialog = true
      }).finally(() => {
        this.loading = false
      })
    },
    myMovies(fecha) {
      this.movie = {}
      this.hours = []
      this.movies = []
      if (moment(this.now).isAfter(moment(fecha))) {
        return false
      }
      this.loadSale(fecha)
    },
    myHours(movie) {
      this.movie = movie
      this.loading = true
      this.$api.post('hours', {fecha: this.fecha, id: movie.id}).then(res => {
        this.loading = false
        this.hours = res.data
        this.hour = this.hours[0]
        // this.clickSala(this.hour) //
      });
    },
    seleccionar(funcion, seat) {
      this.loading = true
      if (seat.activo == 'SELECCIONADO') {
        seat.activo = 'LIBRE'
        this.$api.post('momentaneoDelete', {
          user_id: 1,
          programa_id: funcion.id,
          fila: seat.fila,
          columna: seat.columna,
          letra: seat.letra,
        }).then(() => {
          this.loading = false
          this.momentaneos = this.momentaneos.filter(m => !(m.programa_id == funcion.id && m.fila == seat.fila && m.columna == seat.columna && m.letra == seat.letra))
        })
      } else {
        seat.activo = 'SELECCIONADO'
        this.$api.post('momentaneo', {
          user_id: this.store.user.id,
          programa_id: funcion.id,
          fila: seat.fila,
          columna: seat.columna,
          letra: seat.letra,
          fecha: funcion.horaInicio,
          pelicula: funcion.movie.nombre + ' ' + funcion.movie.formato,
          pelicula_id: funcion.movie.id,
          precio: funcion.price.precio,
          promo: funcion.price.promo == 'SI' ? true : false
        }).then(res => {
          this.loading = false
          if (res.data == 1) {
            // La butaca ya estaba tomada: se refresca la sala
            this.clickSala(funcion)
          } else if (res.data && res.data.id) {
            this.momentaneos = [...this.momentaneos, res.data]
          }
        })
      }
      // console.log(seat)
      // this.hour.sala.seats[indice]['activo']
      //   this.temporal.push(asiento)
    }
  },
  computed: {
    numeroDocumentoValido() {
      return !!(this.client?.numeroDocumento && this.client.numeroDocumento.toString().trim().length > 0)
    },

    btnCortesia() {
      if (this.cortesia) {
        return true
      } else {
        return false
      }
    },
    marcados() {
      let cantidad = 0
      this.frees.forEach(m => {
        if (m.status) {
          cantidad++
        }
      })
      return cantidad
    },
    cantidades() {
      let cantidad = 0
      this.detalleVenta.forEach(m => {
        cantidad += m.cantidad
      })
      return cantidad
    },
    total() {
      let t = 0
      this.detalleVenta.forEach(d => {
        t += parseFloat(d.subtotal)
      })
      return t.toFixed(2);
    },
    cambio() {
      let cambio = parseFloat(this.efectivo == '' ? 0 : this.efectivo) - parseFloat(this.total)
      return Math.round(cambio * 100) / 100
    },
    seleccionados() {
      let array = []
      this.seats.forEach(s => {
        if (s.activo == "SELECCIONADO") {
          array.push(s)
        }
      })
      return array
    },
    detalleVenta() {
      let array = []
      let find
      this.momentaneos.forEach(m => {
        find = array.find(mo => mo.programa_id === m.programa_id)
        if (find == undefined) {
          array.push({
            promo: m.promo,
            fecha: m.fecha,
            precio: m.precio,
            cantidad: 1,
            pelicula: m.pelicula,
            subtotal: m.precio,
            programa_id: m.programa_id,
            pelicula_id: m.pelicula_id
          })
        } else {
          find.cantidad = find.cantidad + 1
          find.subtotal = find.cantidad * m.precio
        }
      })
      return array
    },
    // Lo que se esta vendiendo, agrupado por funcion: pelicula, sala, fecha,
    // hora y butacas. Se muestra en el dialogo de venta para que el cajero
    // confirme la funcion antes de cobrar (la hora es el error mas comun).
    resumenVenta() {
      const grupos = []
      this.momentaneos.forEach(m => {
        let grupo = grupos.find(g => g.programa_id === m.programa_id)
        if (grupo == undefined) {
          const inicio = moment(m.horaInicio || m.fecha)
          grupo = {
            programa_id: m.programa_id,
            pelicula: m.pelicula,
            sala: m.sala || '',
            nroFuncion: m.nroFuncion || '',
            precio: parseFloat(m.precio),
            dia: this.diaTexto(m.horaInicio || m.fecha),
            hora: this.horaTexto(m.horaInicio || m.fecha),
            iniciada: inicio.isValid() && inicio.isBefore(moment()),
            butacas: [],
          }
          grupos.push(grupo)
        }
        grupo.butacas.push(`${m.letra}-${m.columna}`)
      })
      return grupos.map(g => ({
        ...g,
        cantidad: g.butacas.length,
        subtotal: (g.butacas.length * g.precio).toFixed(2),
      }))
    },

  }
}
</script>

<style scoped>
/* --- Dialogo de venta --- */
.venta-dialog {
  border-radius: 10px;
  overflow: hidden;
}

.venta-header {
  background: linear-gradient(90deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%);
  color: #fff;
}

.venta-header__sub {
  font-size: 11px;
  letter-spacing: .5px;
  text-transform: uppercase;
  color: rgba(255, 255, 255, .85);
}

.venta-total {
  font-size: 26px;
  font-weight: 700;
  line-height: 1.1;
}

.line-height-1 {
  line-height: 1.15;
}

.venta-titulo {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .6px;
  text-transform: uppercase;
  color: #37474f;
  border-bottom: 2px solid #cfd8dc;
  padding-bottom: 4px;
  margin-bottom: 8px;
}

.funcion-card {
  border-left: 4px solid #2e7d32;
  border-radius: 6px;
}

/* Funcion que ya empezo: el caso del boleto vendido para una funcion pasada */
.funcion-card--alerta {
  border-left-color: #c62828;
  background: #fff8f8;
}

.funcion-pelicula {
  font-size: 14px;
  font-weight: 700;
  line-height: 1.2;
  color: #1b2429;
}

.funcion-cuando {
  font-size: 14px;
  font-weight: 600;
  color: #1565c0;
}

.funcion-hora {
  font-size: 18px;
  font-weight: 700;
  color: #0d47a1;
}

.funcion-alerta {
  font-size: 12px;
  font-weight: 600;
  color: #b71c1c;
  background: #ffebee;
  border-radius: 4px;
  padding: 3px 6px;
}

.butaca-chip {
  font-weight: 600;
  margin: 2px 3px 2px 0;
}

table {
  width: 100%;
}

table, .tdx, th {
  border-collapse: collapse;
  border: 1px solid #ddd;
  padding: 5px;
}

input {
  border: 1px solid #ddd;
}

.subtitule-text {
  font-size: 12px;
  line-height: 1;
  color: #fff;
}

.movie-card {
  position: relative;
  height: 150px;
  border-radius: 10px;
  overflow: hidden;
  /* Si la pelicula no tiene imagen (o el archivo no existe) queda este fondo */
  background-color: #263238;
  background-image: linear-gradient(135deg, #37474f 0%, #1b2429 100%);
  background-size: cover;
  background-position: center;
  transition: transform .15s ease, box-shadow .15s ease;
}

.movie-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 18px rgba(0, 0, 0, .45);
}

.movie-card--activa {
  outline: 3px solid #21ba45;
  outline-offset: -3px;
}

.movie-sin-imagen {
  position: absolute;
  top: 32px;
  left: 0;
  right: 0;
  margin: 0 auto;
  color: rgba(255, 255, 255, .35);
}

/* Degradado para que el nombre se lea sobre cualquier poster */
.movie-card .movie-degradado {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  top: 0;
  background: linear-gradient(to top,
    rgba(0, 0, 0, .95) 0%,
    rgba(0, 0, 0, .80) 32%,
    rgba(0, 0, 0, .30) 62%,
    rgba(0, 0, 0, 0) 100%);
}

.movie-name {
  font-size: 13px;
  font-weight: bold;
  line-height: 1.15;
  color: #fff;
  text-shadow: 0 1px 3px rgba(0, 0, 0, .9);
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}
</style>
