<template>
  <q-page class="q-pa-md">
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12 col-md-3">
          <div class="text-h6">Pagos QR</div>
          <div class="text-caption text-grey-7">Consulta por fecha</div>
        </div>
        <div class="col-12 col-md-3">
          <q-input v-model="fecha" outlined type="date" label="Fecha" />
        </div>
        <div class="col-12 col-md-2">
          <q-btn color="primary" class="full-width" icon="search" label="Buscar" :loading="loading" @click="consultar" />
        </div>
        <div class="col-12 col-md-4 text-right">
          <q-banner dense class="bg-grey-2">
            <strong>Origen:</strong> {{ source }}
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
          :rows-per-page-options="[10,20,50,0]"
        >
          <template v-slot:body-cell-monto="props">
            <q-td :props="props" class="text-right">
              {{ props.row.monto }}
            </q-td>
          </template>
          <template v-slot:body-cell-raw="props">
            <q-td :props="props">
              <q-btn dense flat color="primary" icon="visibility" @click="verRaw(props.row.raw)" />
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
      loading: false,
      rows: [],
      source: '-',
      message: '',
      rawDialog: false,
      rawItem: null,
      columns: [
        { name: 'fecha', label: 'Fecha', field: 'fecha', align: 'left', sortable: true },
        { name: 'qrId', label: 'QR ID', field: 'qrId', align: 'left', sortable: true },
        { name: 'transactionId', label: 'Transaccion', field: 'transactionId', align: 'left', sortable: true },
        { name: 'monto', label: 'Monto', field: 'monto', align: 'right', sortable: true },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left', sortable: true },
        { name: 'descripcion', label: 'Descripcion', field: 'descripcion', align: 'left', sortable: true },
        { name: 'cliente', label: 'Cliente', field: 'cliente', align: 'left', sortable: true },
        { name: 'origen', label: 'Origen', field: 'origen', align: 'left', sortable: true },
        { name: 'raw', label: 'Detalle', field: 'raw', align: 'center' },
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
      }).then(res => {
        this.rows = (res.data.items || []).map((item, index) => ({
          index,
          ...item,
        }))
        this.source = res.data.source || '-'
        this.message = res.data.message || ''
      }).catch(err => {
        this.rows = []
        this.source = '-'
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
  },
}
</script>
