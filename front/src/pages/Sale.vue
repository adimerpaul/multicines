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
            <q-card @click="myHours(m)" style="height: 150px" class="q-ma-xs">
              <q-img :src="url+'../imagen/'+m.imagen" height="150px">
                <div class="absolute-bottom text-subtitle2 text-center" style="padding: 0px;margin:0px;border: 0px">
                  <div class="row">
                    <div class="col-6 q-pa-none q-ma-none">
                      <div class="subtitule-text">{{ m.nombre }} {{ m.formato }}</div>
                    </div>
                    <div class="col-6 text-right flex flex-center"> {{ m.cantidad }}</div>
                  </div>
                </div>
              </q-img>
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
                  <q-btn color="red" :loading="loading" @click="momentaneoDeleteAll" dense label="Cancelar Venta"
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
                <td class="tdx">{{ d.fecha }}</td>
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
              <div class="col-4 text-bold">{{ movie.nombre }} {{ movie.formato }}
                <q-icon name="schedule" left/>
                <q-badge color="red">{{ hour.sala.nombre }}</q-badge>
                {{ hour.horaInicio.substring(10, 16) }} - {{ hour.price.precio + 'Bs' }}
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
                     :loading="loading" label="Agregar" @click="myMomentaneo();salaDialog=false"/>
              <q-btn icon="highlight_off" class="q-ml-lg" color="red" label="Cancelar" @click="salaDialogClose"/>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog full-width v-model="saleDialog" persistent>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Realizar venta</div>
          <q-space/>

        </q-card-section>
        <q-form @submit.prevent="saleInsert">
          <q-card-section>
            <div class="row">
              <div class="col-2">
                <q-input outlined label="NIT/CARNET"
                         required v-model="client.numeroDocumento"
                         debounce="300"
                         @update:modelValue="searchClient"
                />
              </div>
              <div class="col-2">
                <q-input outlined label="Complemento" v-model="client.complemento"
                         debounce="300"
                         @update:modelValue="searchClient"
                         style="text-transform: uppercase"/>
              </div>
              <div class="col-3">
                <q-input outlined label="nombreRazonSocial" required v-model="client.nombreRazonSocial"
                         style="text-transform: uppercase"/>
              </div>
              <div class="col-3">
                <q-select v-model="document" outlined :options="documents" @update:model-value="validarnit"/>
              </div>
              <div class="col-2">
                <q-input outlined label="Email" v-model="client.email" type="email"/>
              </div>
            </div>
          </q-card-section>
          <q-separator/>
          <q-card-section>
            <div class="row">
              <div class="col-3">
                <q-input outlined label="TOTAL A PAGAR:" disable v-model="total"/>
              </div>
              <div class="col-3">
                <q-input outlined label="EFECTIVO BS." @keyup="cambio" v-model="efectivo"/>
              </div>
              <div class="col-2">
                <q-input outlined label="CAMBIO:" disable v-model="cambio"/>
              </div>
              <div class="col-2 flex flex-center">
                <q-toggle outlined :label="`${credito} T CREDITO`" v-model="credito" color="green" false-value="NO"
                          true-value="SI"/>
              </div>
              <div class="col-2 flex flex-center">
                <q-checkbox outlined label="N CORTESIA" @update:model-value="habilitarCortesia" v-model="cortesia"
                            color="primary"/>
              </div>
              <div class="col-2 flex flex-center">
                <q-toggle outlined :label="`${tarjeta} VIP`" v-model="tarjeta" color="green" false-value="NO"
                          true-value="SI"/>
              </div>
            </div>
            <div class="coll-12">
              <template v-if="tarjeta == 'SI'">
                <q-form @submit.prevent="consultartarjeta">
                  <div class="row">
                    <div class="col-6 ">
                      <q-input outlined label="Codigo" v-model="codigo" @keyup="consultartarjeta"/>
                    </div>
                    <div class="col-6 ">
                      <q-banner>Saldo :{{ nombresaldo.saldo }} -- {{ nombresaldo.nombre }}</q-banner>
                    </div>
                  </div>
                </q-form>
              </template>
              <template v-if="cortesia">
                <div class="row">
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
            <div class="col-12 text-red text-bold" v-if="error!=''">
              {{ error }}
            </div>
          </q-card-section>
          <q-separator/>
          <q-card-section>
            <div class="row">
              <div class="col-6">
                <q-btn type="submit" class="full-width" icon="o_add_circle" label="Realizar venta" :loading="loading"
                       no-caps color="green" :disable="btn"/>
              </div>
              <div class="col-6">
                <q-btn class="full-width" icon="undo" @click="saleDialog=false, consultartarjeta" label="Atras" no-caps
                       color="red"/>
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
      totventa: [],
      error: '',
      btn: false,
      tienerebaja: false,
      booltarjeta: false,
      codigo: '',
      frees: [],
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
    // this.cargarLeyenda()
    // this.encabezado()

    this.freeGet()
    this.tventa()
    this.myMomentaneo()
    this.eventSearch()
    this.loadDocuments()

    this.myMovies(this.fecha)
  },
  methods: {
    loadDocuments(){
      this.$api.get('document').then(res => {
        res.data.forEach(r => {
          r.label = r.descripcion
        })
        this.documents = res.data
        this.document = this.documents[0]
      })
    },
    freeGet() {
      this.$api.get('free').then(res => {
        this.frees = []
        res.data.forEach(r => {
          r.status = false
          this.frees = res.data
        })
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
    movieVenta(fecha) {
      this.$api.post('movietotal', {'fecha': fecha}).then(res => {
        this.totventa = res.data
        this.movies.forEach(r => {

        })
      })

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
    tventa() {
      this.$api.post('totalventa', {'fecha': this.fecha}).then(res => {
        // console.log(res.data)
        this.totalventa = res.data
      })
    },
    eventSearch() {
      this.$api.post('eventSearch').then(res => {
        this.store.eventNumber = res.data
      })
    },
    saleInsert() {
      // if (this.client.numeroDocumento==0) {
      //   this.$q.notify({
      //     color: 'red',
      //     textColor: 'white',
      //     message: 'Debe ingresar un numero de documento'
      //   })
      //  return false
      // }
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
        frees: this.frees
      }).then(res => {
        this.freeGet()
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
        this.momentaneoDeleteAll()
        this.tventa()
        this.client = {complemento: '', vip: 'NO', credito: 'NO'}
        this.saleDialog = false
        this.myMovies(this.fecha)
        this.loading = false
        this.eventSearch()
      }).finally(() => {
        this.loading = false
      }).catch(err => {
        //this.error=err.response.data.message
        this.loading = false
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response.data.message,
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
      this.saleDialog = true
      this.client = {complemento: '', vip: 'NO', credito: 'NO'}
    },
    momentaneoDeleteAll() {
      this.loading = true
      this.$api.post('momentaneoDeleteall').then(res => {
        this.loading = false
        this.myMomentaneo();
      });
    },
    myMomentaneo() {
      this.$api.get('momentaneo').then(res => {
        // console.log(res.data)
        this.momentaneos = res.data
      });
    },
    salaDialogClose() {
      this.$api.post('momentaneoDeleteUser', {
        programa_id: this.hour.id
      }).then(res => {
        this.myMomentaneo()
      })
      this.salaDialog = false

    },
    clickSala(h) {
      this.hour = h
      this.loading = true
      this.$api.post('mySeats', {id: h.id}).then(res => {
        // console.log(res.data)
        this.seats = res.data

        this.$api.post('disponibleSeats', {id: h.id}).then(res => {
          // console.log(res)
          let valores = res.data[0]
          this.disponible = parseInt(valores.salatotal) - parseInt(valores.venta) - parseInt(valores.temp)
          this.vendido = parseInt(valores.venta)
          this.devueltos = parseInt(valores.dev)
          this.capacidad = parseInt(valores.salatotal)
          this.salaDialog = true
          this.loading = false
        })


      })
    },
    myMovies(fecha) {
      this.movie = {}
      this.hours = []
      this.movies = []
      if (moment(this.now).isAfter(moment(fecha))) {
        return false
      }

      this.$api.post('movies', {fecha: fecha}).then(res => {
        console.log(res.data)
        this.movies = res.data
        this.tventa()
        this.movieVenta(fecha)
        // this.movie = this.movies[0].movie //
        // this.myHours(this.movie) //
      });
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
        }).then(res => {
          this.loading = false
          this.myMomentaneo()
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
          this.myMomentaneo()
          if (res.data == 1) {
            this.clickSala(funcion)
          }
        })
      }
      // console.log(seat)
      // this.hour.sala.seats[indice]['activo']
      //   this.temporal.push(asiento)
    }
  },
  computed: {

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

  }
}
</script>

<style scoped>
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
</style>
