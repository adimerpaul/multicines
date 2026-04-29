<template>
  <q-page class="q-pa-md">
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12 col-md-2">
          <div class="text-h6">Pagos QR</div>
          <div class="text-caption text-grey-7">Consulta por fecha</div>
        </div>
        <div class="col-12 col-md-3">
          <q-input v-model="fecha" outlined type="date" label="Fecha" />
        </div>
        <div class="col-12 col-md-3">
          <q-select v-model="tipo" outlined :options="tipos" label="Tipo" emit-value map-options />
        </div>
        <div class="col-12 col-md-2">
          <q-btn color="primary" class="full-width" icon="search" label="Buscar" :loading="loading" @click="consultar" />
        </div>
        <div class="col-12 col-md-2">
          <q-banner dense class="bg-green-1 text-dark text-right">
            <strong>Total:</strong> {{ totalPagos }} Bs
          </q-banner>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section v-if="message" class="q-pb-none">
        <q-banner class="bg-blue-1 text-dark">
          {{ message }}
        </q-banner>
      </q-card-section>

      <q-card-section>
        <q-table
          title="Movimientos"
          :rows="rows"
          :columns="columns"
          row-key="index"
          :loading="loading"
          flat
          bordered
          dense
          :rows-per-page-options="[0]"
          :pagination="pagination"
        >
          <template v-slot:body-cell-acciones="props">
            <q-td :props="props">
              <q-btn-dropdown dense flat color="primary" icon="more_vert" label="Opciones" no-caps>
                <q-list dense>
                  <q-item clickable v-close-popup @click="verRaw(props.row.raw)">
                    <q-item-section avatar>
                      <q-icon name="visibility" color="primary" />
                    </q-item-section>
                    <q-item-section>Ver</q-item-section>
                  </q-item>
                  <q-item
                    v-if="store.boolvincularpago"
                    clickable
                    v-close-popup
                    :disable="props.row.vinculado || !props.row.qrId"
                    @click="abrirVincular(props.row)"
                  >
                    <q-item-section avatar>
                      <q-icon name="link" color="positive" />
                    </q-item-section>
                    <q-item-section>Vincular a venta</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
          <template v-slot:body-cell-ventaId="props">
            <q-td :props="props" class="text-left">
              <q-badge v-if="props.row.vinculado" color="green" :label="'#' + props.row.ventaId" />
              <q-badge v-else color="grey" label="Sin venta" />
            </q-td>
          </template>
          <template v-slot:body-cell-ventaTipo="props">
            <q-td :props="props">
              <q-badge v-if="props.row.ventaTipo" :color="props.row.ventaTipo === 'CANDY' ? 'teal' : 'indigo'" :label="props.row.ventaTipo" />
              <span v-else>-</span>
            </q-td>
          </template>
          <template v-slot:body-cell-monto="props">
            <q-td :props="props" class="text-right">
              {{ formatMoney(props.row.monto) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Historial de vinculaciones -->
    <q-card flat bordered class="q-mt-md">
      <q-card-section class="row items-center q-py-sm">
        <div class="text-subtitle2 text-grey-8 col">
          <q-icon name="history" class="q-mr-xs" />
          Historial de vinculaciones
        </div>
        <q-btn
          flat
          dense
          no-caps
          :icon="mostrarHistorial ? 'expand_less' : 'expand_more'"
          :label="mostrarHistorial ? 'Ocultar' : 'Mostrar'"
          color="grey-7"
          @click="toggleHistorial"
        />
      </q-card-section>

      <q-slide-transition>
        <div v-show="mostrarHistorial">
          <q-separator />
          <q-card-section>
            <q-table
              :rows="historialRows"
              :columns="historialColumns"
              row-key="id"
              :loading="historialLoading"
              flat
              dense
              bordered
              :rows-per-page-options="[10, 25, 50]"
            >
              <template v-slot:body-cell-venta_monto="props">
                <q-td :props="props" class="text-right">
                  {{ formatMoney(props.row.venta_monto) }} Bs
                </q-td>
              </template>
              <template v-slot:body-cell-created_at="props">
                <q-td :props="props">
                  {{ formatFechaTexto(props.row.created_at) }}
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </div>
      </q-slide-transition>
    </q-card>

    <q-dialog v-model="rawDialog">
      <q-card style="min-width: 70vw; max-width: 90vw;">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">Detalle movimiento</div>
          <q-space />
          <q-btn flat dense round icon="close" v-close-popup />
        </q-card-section>
        <q-card-section>
          <pre style="white-space: pre-wrap;">{{ JSON.stringify(rawItem, null, 2) }}</pre>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="linkDialog">
      <q-card style="width: 520px; max-width: 95vw;">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">Vincular a venta</div>
          <q-space />
          <q-btn flat dense round icon="close" v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="q-mb-sm">
            <div><strong>Pago QR:</strong> {{ selectedPayment?.qrId }}</div>
            <div><strong>Monto:</strong> {{ formatMoney(selectedPayment?.monto) }} Bs</div>
          </div>
          <q-select
            v-model="selectedSale"
            :options="salesOptions"
            :loading="salesLoading"
            outlined
            clearable
            use-input
            input-debounce="0"
            label="Venta del dia"
            option-label="label"
            @filter="filtrarVentas"
          >
            <template v-slot:option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section>
                  <q-item-label>{{ scope.opt.label }}</q-item-label>
                  <q-item-label caption>{{ scope.opt.caption }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="grey-8" v-close-popup />
          <q-btn flat label="Vincular" color="positive" :loading="linkLoading" :disable="!selectedSale" @click="vincularVenta" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import { globalStore } from '../stores/globalStore'

export default {
  name: 'PagosPage',
  data() {
    return {
      store: globalStore(),
      fecha: date.formatDate(new Date(), 'YYYY-MM-DD'),
      tipo: 'TODOS',
      tipos: [
        { label: 'Todos', value: 'TODOS' },
        { label: 'Boleteria', value: 'BOLETERIA' },
        { label: 'Candy', value: 'CANDY' },
      ],
      loading: false,
      rows: [],
      message: '',
      rawDialog: false,
      rawItem: null,
      linkDialog: false,
      linkLoading: false,
      salesLoading: false,
      selectedPayment: null,
      selectedSale: null,
      sales: [],
      salesOptions: [],
      pagination: { rowsPerPage: 0 },
      mostrarHistorial: false,
      historialLoading: false,
      historialRows: [],
      columns: [
        { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'left' },
        { name: 'fechaHora', label: 'Fecha/Hora', field: row => this.formatFechaHora(row), align: 'left', sortable: true },
        { name: 'ventaTipo', label: 'Tipo', field: 'ventaTipo', align: 'left', sortable: true },
        { name: 'ventaId', label: 'Venta', field: 'ventaId', align: 'left', sortable: true },
        { name: 'qrId', label: 'QR ID', field: 'qrId', align: 'left', sortable: true },
        { name: 'transactionId', label: 'Transaccion', field: 'transactionId', align: 'left', sortable: true },
        { name: 'monto', label: 'Monto', field: 'monto', align: 'right', sortable: true },
        { name: 'descripcion', label: 'Descripcion', field: 'descripcion', align: 'left', sortable: true },
      ],
      historialColumns: [
        { name: 'created_at', label: 'Fecha/Hora', field: 'created_at', align: 'left', sortable: true },
        { name: 'user_name', label: 'Usuario', field: 'user_name', align: 'left', sortable: true },
        { name: 'sale_id', label: 'Venta #', field: 'sale_id', align: 'left', sortable: true },
        { name: 'venta_tipo', label: 'Tipo', field: 'venta_tipo', align: 'left', sortable: true },
        { name: 'venta_monto', label: 'Monto', field: 'venta_monto', align: 'right', sortable: true },
        { name: 'qr_id', label: 'QR ID', field: 'qr_id', align: 'left', sortable: true },
        { name: 'transaction_id', label: 'Transaccion', field: 'transaction_id', align: 'left', sortable: true },
      ],
    }
  },
  created() {
    this.consultar()
  },
  methods: {
    consultar() {
      this.loading = true
      this.$api.post('movimientosQr', {
        fecha: this.fecha,
        tipo: this.tipo,
      }).then(res => {
        this.rows = (res.data.items || []).map((item, index) => ({
          index,
          ...item,
        }))
        this.message = res.data.message || ''
      }).catch(err => {
        this.rows = []
        this.message = ''
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || 'No se pudo consultar los pagos',
          position: 'top',
          timeout: 5000,
        })
      }).finally(() => {
        this.loading = false
      })
    },
    toggleHistorial() {
      this.mostrarHistorial = !this.mostrarHistorial
      if (this.mostrarHistorial && this.historialRows.length === 0) {
        this.cargarHistorial()
      }
    },
    cargarHistorial() {
      this.historialLoading = true
      this.$api.get('historialVincularQr').then(res => {
        this.historialRows = res.data || []
      }).catch(() => {
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: 'No se pudo cargar el historial',
          position: 'top',
        })
      }).finally(() => {
        this.historialLoading = false
      })
    },
    verRaw(raw) {
      this.rawItem = raw
      this.rawDialog = true
    },
    abrirVincular(row) {
      this.selectedPayment = row
      this.selectedSale = null
      this.sales = []
      this.salesOptions = []
      this.linkDialog = true
      this.cargarVentas()
    },
    cargarVentas() {
      this.salesLoading = true
      this.$api.post('ventasParaVincularQr', {
        fecha: this.fecha,
        tipo: this.tipo,
        todo: this.store.booltodoventa,
      }).then(res => {
        this.sales = (res.data || []).map(sale => ({
          ...sale,
          label: `#${sale.id} - ${sale.tipo || ''} - ${this.formatMoney(sale.montoTotal)} Bs`,
          caption: `${this.formatFechaTexto(sale.fechaEmision)} - ${sale.usuario || 'Sin usuario'}${sale.cliente ? ' - ' + sale.cliente : ''}`,
        }))
        this.salesOptions = this.sales
      }).catch(err => {
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || 'No se pudo cargar las ventas',
          position: 'top',
        })
      }).finally(() => {
        this.salesLoading = false
      })
    },
    filtrarVentas(val, update) {
      update(() => {
        const needle = (val || '').toString().toLowerCase()
        this.salesOptions = needle === ''
          ? this.sales
          : this.sales.filter(sale => `${sale.label} ${sale.caption}`.toLowerCase().includes(needle))
      })
    },
    vincularVenta() {
      if (!this.selectedPayment || !this.selectedSale) {
        return
      }

      this.linkLoading = true
      this.$api.post('vincularVentaQr', {
        sale_id: this.selectedSale.id,
        qrId: this.selectedPayment.qrId,
        transactionId: this.selectedPayment.transactionId,
        fecha: this.fecha,
      }).then(res => {
        this.$q.notify({
          color: 'positive',
          textColor: 'white',
          message: res.data.message || 'Pago vinculado correctamente',
          position: 'top',
        })
        this.linkDialog = false
        this.consultar()
        if (this.mostrarHistorial) {
          this.cargarHistorial()
        } else {
          this.historialRows = []
        }
      }).catch(err => {
        this.$q.notify({
          color: 'negative',
          textColor: 'white',
          message: err.response?.data?.message || 'No se pudo vincular el pago',
          position: 'top',
          timeout: 5000,
        })
      }).finally(() => {
        this.linkLoading = false
      })
    },
    formatMoney(value) {
      return (parseFloat(value || 0)).toFixed(2)
    },
    formatFechaTexto(value) {
      return (value || '').toString().substring(0, 19)
    },
    formatFechaHora(row) {
      const fecha = (row.fecha || '').toString().substring(0, 10)
      const hora = row.hora || (row.fecha || '').toString().substring(11, 19)
      return `${fecha} ${hora}`.trim()
    },
  },
  computed: {
    totalPagos() {
      const total = this.rows.reduce((sum, row) => sum + parseFloat(row.monto || 0), 0)
      return total.toFixed(2)
    },
  },
}
</script>
