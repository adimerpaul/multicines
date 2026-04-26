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
          <template v-slot:body-cell-raw="props">
            <q-td :props="props">
              <q-btn dense flat color="primary" icon="visibility" @click="verRaw(props.row.raw)" />
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
  </q-page>
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'PagosPage',
  data() {
    return {
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
      pagination: { rowsPerPage: 0 },
      columns: [
        { name: 'raw', label: 'Detalle', field: 'raw', align: 'left' },
        { name: 'fechaHora', label: 'Fecha/Hora', field: row => this.formatFechaHora(row), align: 'left', sortable: true },
        { name: 'ventaTipo', label: 'Tipo', field: 'ventaTipo', align: 'left', sortable: true },
        { name: 'ventaId', label: 'Venta', field: 'ventaId', align: 'left', sortable: true },
        { name: 'qrId', label: 'QR ID', field: 'qrId', align: 'left', sortable: true },
        { name: 'transactionId', label: 'Transaccion', field: 'transactionId', align: 'left', sortable: true },
        { name: 'monto', label: 'Monto', field: 'monto', align: 'right', sortable: true },
        { name: 'descripcion', label: 'Descripcion', field: 'descripcion', align: 'left', sortable: true },
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
    verRaw(raw) {
      this.rawItem = raw
      this.rawDialog = true
    },
    formatMoney(value) {
      return (parseFloat(value || 0)).toFixed(2)
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
