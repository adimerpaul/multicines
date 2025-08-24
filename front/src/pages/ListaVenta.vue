<template>
  <q-page>
    <div class="row">
      <div class="col-12">
        <div class="bg-primary text-white text-bold text-center text-h4">BOLETERIA</div>
      </div>

      <!-- Filtros -->
      <div class="col-12">
        <q-form @submit.prevent="listaVentaBoleteriaGet">
          <div class="row">
            <div class="col-4 q-pa-xs">
              <q-input outlined dense label="FechaIni" type="date" v-model="fechaIni" />
            </div>
            <div class="col-4 q-pa-xs">
              <q-input outlined dense label="FechaFin" type="date" v-model="fechaFin" />
            </div>
            <div class="col-4 q-pa-xs flex flex-center">
              <q-btn label="Buscar" icon="search" color="primary" type="submit" :loading="loading" dense />
            </div>
          </div>
        </q-form>
      </div>

      <!-- Tabla ventas -->
      <div class="col-12">
        <q-table
          flat bordered dense wrap-cells
          :rows="listaVentaBoleteria"
          :columns="listaColums"
          :rows-per-page-options="[0]"
          :filter="filter"
          row-key="id"
          separator="cell"
        >
          <template #top-right>
            <q-btn color="green" label="Export EXCEL" class="q-mr-sm" dense @click="exportar" />
            <q-input outlined dense debounce="300" v-model="filter" placeholder="Buscar">
              <template #append><q-icon name="search" /></template>
            </q-input>
          </template>

          <template #body-cell-siatAnulado="props">
            <q-td :props="props" auto-width>
              <q-badge :color="props.row.siatAnulado ? 'red' : 'green'" :label="props.row.siatAnulado ? 'A' : 'V'"/>
            </q-td>
          </template>

          <template #body-cell-siatEnviado="props">
            <q-td :props="props" auto-width>
              <q-badge v-if="props.row.vip==='NO'" :color="props.row.siatEnviado ? 'green' : 'red'" :label="props.row.siatEnviado ? 'V' : 'E'"/>
              <q-badge v-else color="orange">VIP</q-badge>
            </q-td>
          </template>

          <template #body-cell-Opciones="props">
            <q-td :props="props" auto-width>
              <q-btn-dropdown color="primary" label="Opciones" dense no-caps>
                <q-list>
                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0 && props.row.cortesia=='NO' && props.row.venta=='F'">
                    <q-item-section>
                      <q-btn icon="print" color="primary" class="full-width" label="Imprimir" no-caps
                             @click="printFactura(props.row)" v-if="props.row.siatAnulado==0" dense/>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0">
                    <q-item-section>
                      <q-btn icon="list" color="green" class="full-width" label="Imp Boletos" no-caps
                             @click="detalleimp(props.row)" v-if="props.row.siatAnulado==0" dense/>
                    </q-item-section>
                  </q-item>

<!--                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0 ">-->
<!--                    <q-item-section>-->
<!--                      <q-btn icon="cancel_presentation" color="red" class="full-width" label="Anular (SIAT)"-->
<!--                             no-caps @click="anularSale(props.row)" v-if="props.row.siatAnulado==0" dense/>-->
<!--                    </q-item-section>-->
<!--                  </q-item>-->

                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0 ">
                    <q-item-section>
                      <q-btn icon="assignment" color="deep-orange" class="full-width"
                             label="Formulario de Anulación" no-caps dense
                             @click="openFormularioAnulacion(props.row)"/>
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup v-if="props.row.cortesia=='NO'">
                    <q-item-section>
                      <q-btn type="a" label="Imp Impuestos" class="full-width" color="info" dense target="_blank"
                             :href="`${cine.url2}consulta/QR?nit=${cine.nit}&cuf=${props.row.cuf}&numero=${props.row.numeroFactura }&t=2`" />
                    </q-item-section>
                  </q-item>

                  <q-item clickable v-close-popup v-if="props.row.siatAnulado==0 && props.row.cortesia=='NO'">
                    <q-item-section>
                      <q-btn label="Enviar Correo" class="full-width" color="teal" dense @click="correo(props.row)"/>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>
      </div>
    </div>

    <!-- Tabla Boletos -->
    <q-dialog v-model="ticketsDialog" full-width>
      <q-card>
        <q-card-section><div class="text-h6">Boletos</div></q-card-section>
        <q-card-section class="q-pt-none">
          <q-table flat bordered dense :columns="boletoColums" :rows="tickets" :rows-per-page-options="[0]" row-key="id"/>
        </q-card-section>
        <q-card-actions align="right"><q-btn flat label="Cerrar" v-close-popup dense/></q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Diálogo SIAT antiguo -->
    <q-dialog v-model="dialogAnular">
      <q-card style="min-width: 350px">
        <q-card-section><div class="text-h6">ANULAR FACTURA</div></q-card-section>
        <q-card-section class="q-pt-none">
          <q-select dense outlined label="Motivo" :options="motivos" v-model="motivo"/>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup dense/>
          <q-btn flat label="ANULAR" @click="enviarAnular" dense/>
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- NUEVO: Formulario de Anulación (como la imagen) -->
    <q-dialog v-model="dialogFormAnulacion" persistent full-width>
      <q-card class="form-card">
        <q-card-section class="row items-center q-pb-xs">
          <div class="text-h6">FORMULARIO DE ANULACIÓN</div>
          <q-space/>
          <q-btn icon="close" flat round dense v-close-popup/>
        </q-card-section>

        <q-separator/>

        <q-card-section class="q-pt-sm">
          <q-form @submit.prevent="enviarFormularioAnulacion">
            <!-- Encabezado: Fecha / Código / Monto -->
            <div class="row q-col-gutter-sm">
<!--              <div class="col-12 col-md-4">-->
<!--                <div class="line-label">FECHA:</div>-->
<!--                <q-input dense outlined type="date" v-model="formAnu.fecha"/>-->
<!--              </div>-->
<!--              <div class="col-6 col-md-4">-->
<!--                <div class="line-label">CÓDIGO:</div>-->
<!--                <q-input dense outlined v-model="formAnu.codigo" />-->
<!--              </div>-->
<!--              <div class="col-6 col-md-4">-->
<!--                <div class="line-label">MONTO:</div>-->
<!--                <q-input dense outlined type="number" step="0.01" v-model.number="formAnu.monto"/>-->
<!--              </div>-->
            </div>

            <!-- Nombre cajero / Sección -->
<!--            <div class="row q-col-gutter-sm q-mt-sm">-->
<!--              <div class="col-12 col-md-6">-->
<!--                <div class="line-label">NOMBRE DEL CAJERO:</div>-->
<!--                <q-input dense outlined v-model="formAnu.cajero"/>-->
<!--              </div>-->
<!--              <div class="col-12 col-md-6">-->
<!--                <div class="line-label">SECCIÓN:</div>-->
<!--                <q-input dense outlined v-model="formAnu.seccion"/>-->
<!--              </div>-->
<!--            </div>-->

            <!-- Motivos (checkboxes) -->
            <div class="row q-col-gutter-sm q-mt-sm">
              <div class="col-12">
                <div class="line-label">MOTIVO:</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-auto">
                    <q-checkbox dense v-model="formAnu.motivos.errorCajero" label="Error de cajero"/>
                  </div>
                  <div class="col-auto">
                    <q-checkbox dense v-model="formAnu.motivos.errorCliente" label="Error de cliente"/>
                  </div>
                  <div class="col-auto">
                    <q-checkbox dense v-model="formAnu.motivos.errorSistema" label="Error de Sistema"/>
                  </div>
                  <div class="col-auto">
                    <q-checkbox dense v-model="formAnu.motivos.ventaDuplicada" label="Venta Duplicada"/>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detalle -->
            <div class="row q-mt-sm">
              <div class="col-12">
                <div class="line-label">DETALLE:</div>
                <q-input dense outlined type="textarea" autogrow :rows="3" v-model="formAnu.detalle"
                         placeholder="Se ingresó la venta de 6 butacas, pero al momento de imprimir solo salieron 2..."/>
              </div>
            </div>

            <!-- Firmas -->
<!--            <div class="row q-col-gutter-sm q-mt-md">-->
<!--              <div class="col-12 col-md-6">-->
<!--                <div class="line-label">AUTORIZADO POR:</div>-->
<!--                <q-input dense outlined v-model="formAnu.autorizadoPor" placeholder="(se completa al autorizar)" disable/>-->
<!--              </div>-->
<!--              <div class="col-12 col-md-6">-->
<!--                <div class="line-label">FIRMA:</div>-->
<!--                <q-input dense outlined placeholder="____________________" disable/>-->
<!--              </div>-->
<!--              <div class="col-12 col-md-6 q-mt-sm">-->
<!--                <div class="line-label">MODIFICADO POR:</div>-->
<!--                <q-input dense outlined v-model="formAnu.modificadoPor" placeholder="(se completa al anular)" disable/>-->
<!--              </div>-->
<!--              <div class="col-12 col-md-6 q-mt-sm">-->
<!--                <div class="line-label">FIRMA:</div>-->
<!--                <q-input dense outlined placeholder="____________________" disable/>-->
<!--              </div>-->
<!--              <div class="col-12 q-mt-sm">-->
<!--                <div class="line-label">FIRMA DE CAJERO:</div>-->
<!--                <q-input dense outlined placeholder="____________________" disable/>-->
<!--              </div>-->
<!--            </div>-->

            <div class="q-mt-md">
              <q-btn type="submit" color="deep-orange" icon="assignment_turned_in" label="Enviar solicitud de anulación" :loading="loading" no-caps dense/>
              <q-btn flat color="primary" label="Cancelar" class="q-ml-sm" v-close-popup dense/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Helpers invisibles -->
    <div><img :src="qrImage" style="visibility:hidden"></div>
    <div id="myelement" class="hidden">{{ lorem }}</div>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import { Printd } from 'printd'
import xlsx from 'json-as-xlsx'
const conversor = require('conversor-numero-a-letras-es-ar')
const QRCode = require('qrcode')

export default {
  name: 'ListaVenta',
  data () {
    return {
      filter: '',
      lorem: 'lorem impus',
      ticketsDialog: false,
      loading: false,
      fechaIni: date.formatDate(new Date(), 'YYYY-MM-DD'),
      fechaFin: date.formatDate(new Date(), 'YYYY-MM-DD'),
      listaVentaBoleteria: [],
      facturadetalle: {},
      factura: {},
      cine: {},
      dialogAnular: false,
      dialogFormAnulacion: false,  // NUEVO
      listaColums: [
        { name: 'Opciones', label: 'Opciones', field: 'Opciones' },
        { name: 'numeroFactura', label: 'numeroFactura', field: row => 'N' + row.numeroFactura, sortable: true },
        { name: 'siatEnviado', label: 'siatEnviado', field: 'siatEnviado', sortable: true },
        { name: 'fechaEmision', label: 'fechaEmision', field: 'fechaEmision', sortable: true },
        { name: 'client_id', label: 'client_id', field: row => row.client.nombreRazonSocial, sortable: true },
        { name: 'user_id', label: 'user_id', field: row => row.user.name, sortable: true },
        { name: 'montoTotal', label: 'montoTotal', field: 'montoTotal', sortable: true },
        { name: 'siatAnulado', label: 'siatAnulado', field: 'siatAnulado', sortable: true },
        { name: 'id', label: 'id', field: 'id', sortable: true },
        { name: 'cuf', label: 'cuf', field: 'cuf', sortable: true },
        { name: 'credito', label: 'Tcredito', field: 'credito', sortable: true }
      ],
      boletoColums: [
        { name: 'opcion', label: 'Opción', field: 'opcion' },
        { name: 'titulo', label: 'Título', field: 'titulo', sortable: true },
        { name: 'nombreSala', label: 'Sala', field: 'nombreSala', sortable: true },
        { name: 'horaFuncion', label: 'Hora', field: 'horaFuncion', sortable: true },
        { name: 'letra', label: 'Fila', field: 'letra', sortable: true },
        { name: 'columna', label: 'Col', field: 'columna', sortable: true },
        { name: 'costo', label: 'Costo', field: 'costo', sortable: true }
      ],
      tickets: [],
      leyendas: [],
      qrImage: '',
      motivos: [],
      motivo: {},
      opts: {
        errorCorrectionLevel: 'M',
        type: 'png',
        quality: 0.95,
        width: 100,
        margin: 1,
        color: { dark: '#000000', light: '#FFF' }
      },

      // NUEVO: estado del formulario de anulación
      formAnu: {
        sale_id: null,
        fecha: date.formatDate(new Date(), 'YYYY-MM-DD'),
        codigo: '',
        monto: null,
        cajero: '',
        seccion: 'Boletería',
        motivos: { errorCajero: false, errorCliente: false, errorSistema: false, ventaDuplicada: false },
        detalle: '',
        autorizadoPor: '',
        modificadoPor: ''
      }
    }
  },
  mounted () {
    this.listaVentaBoleteriaGet()
    this.encabezado()
    this.cargarLeyenda()
    this.cargarMotivo()
  },
  methods: {
    exportar () {
      const data = [{
        sheet: 'Boleteria',
        columns: [
          { label: 'id', value: 'id' },
          { label: 'numeroFactura', value: row => row.numeroFactura },
          { label: 'siatEnviado', value: 'siatEnviado' },
          { label: 'fechaEmision', value: 'fechaEmision' },
          { label: 'client_id', value: row => row.client.nombreRazonSocial },
          { label: 'user_id', value: row => row.user.name },
          { label: 'montoTotal', value: 'montoTotal' },
          { label: 'siatAnulado', value: 'siatAnulado' },
          { label: 'cuf', value: 'cuf' }
        ],
        content: this.listaVentaBoleteria
      }]
      const settings = { fileName: 'VentaBoleteria' + date.formatDate(new Date(), 'YYYY-MM-DD'), writeOptions: {} }
      xlsx(data, settings)
    },
    correo (venta) {
      this.$api.post('enviarCorreo', { client: venta.client, sale: venta }).then(res => {
        if (res.data) this.$q.notify({ color: 'green', textColor: 'white', message: 'Enviado al correo' })
      })
    },

    // SIAT (flujo previo)
    enviarAnular () {
      this.$q.loading.show()
      this.$api.post('anularSale', { sale: this.factura, motivo: this.motivo }).then(() => {
        this.dialogAnular = false
        this.listaVentaBoleteriaGet()
      }).finally(() => this.$q.loading.hide())
    },

    cargarMotivo () {
      this.$api.get('motivoanular').then(res => {
        res.data.forEach(r => { r.label = r.descripcion })
        this.motivos = res.data
        this.motivo = this.motivos[0]
      })
    },
    cargarLeyenda () {
      this.$api.post('listleyenda', { codigo: '590000' }).then(res => { this.leyendas = res.data })
    },
    encabezado () {
      this.$api.get('datocine').then(res => { this.cine = res.data })
    },

    boletoprint (bol) {
      // ... (tu código actual de impresión de boletos, sin cambios)
    },
    anularSale (factura) {
      this.factura = factura
      this.dialogAnular = true
    },
    detalleimp (factura) {
      this.tickets = factura.tickets
      this.ticketsDialog = true
    },
    async printFactura (factura) {
      // ... (tu código actual de impresión de factura, sin cambios)
    },

    listaVentaBoleteriaGet () {
      this.loading = true
      this.$api.post('listaVentaBoleteria', { fechaIni: this.fechaIni, fechaFin: this.fechaFin })
        .then(res => { this.listaVentaBoleteria = res.data })
        .finally(() => { this.loading = false })
    },

    // NUEVO: abrir formulario estilo papel
    openFormularioAnulacion (venta) {
      this.formAnu.sale_id = venta.id
      this.formAnu.fecha = venta.fechaEmision ? venta.fechaEmision.substring(0, 10) : date.formatDate(new Date(), 'YYYY-MM-DD')
      this.formAnu.codigo = venta.numeroFactura ? `N${venta.numeroFactura}` : String(venta.id)
      this.formAnu.monto = Number(venta.montoTotal || 0).toFixed(2)
      this.formAnu.cajero = (venta.user && venta.user.name) ? venta.user.name : ''
      this.formAnu.seccion = 'Boletería'
      this.formAnu.motivos = { errorCajero: false, errorCliente: false, errorSistema: false, ventaDuplicada: false }
      this.formAnu.detalle = ''
      this.dialogFormAnulacion = true
    },

    // NUEVO: enviar a /anulaciones (queda Pendiente)
    enviarFormularioAnulacion () {
      // construir motivo como en el papel
      const seleccionados = []
      if (this.formAnu.motivos.errorCajero) seleccionados.push('Error de cajero')
      if (this.formAnu.motivos.errorCliente) seleccionados.push('Error de cliente')
      if (this.formAnu.motivos.errorSistema) seleccionados.push('Error de Sistema')
      if (this.formAnu.motivos.ventaDuplicada) seleccionados.push('Venta Duplicada')
      const motivoStr = seleccionados.join(', ') || null

      const payload = {
        // fecha: this.formAnu.fecha,
        // cajero: this.formAnu.cajero,
        // monto: this.formAnu.monto,
        seccion: 'BOLETERIA',
        motivo: motivoStr,
        detalle: this.formAnu.detalle,
        sale_id: this.formAnu.sale_id
      }

      this.loading = true
      this.$api.post('anulaciones', payload)
        .then(() => {
          this.$q.notify({ message: 'Solicitud de anulación enviada (Pendiente)', color: 'deep-orange', icon: 'assignment_turned_in' })
          this.dialogFormAnulacion = false
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>

<style scoped>
.form-card {
  border: 1px solid #ddd;
}
.line-label {
  font-weight: 600;
  margin-bottom: 2px;
  font-size: 12px;
}
</style>
