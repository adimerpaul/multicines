<template>
  <q-page class="q-pa-xs">
    <!-- Filtros -->
    <div class="row q-col-gutter-sm items-end q-mb-sm">
      <div class="col-12 col-sm-3">
        <q-input v-model="fi" type="date" outlined dense label="Desde" @update:model-value="fetchRows" />
      </div>
      <div class="col-12 col-sm-3">
        <q-input v-model="ff" type="date" outlined dense label="Hasta" @update:model-value="fetchRows" />
      </div>
      <div class="col-12 col-sm-3">
        <q-select dense outlined clearable v-model="estado" :options="estados" label="Estado" @update:model-value="fetchRows" />
      </div>
      <div class="col-12 col-sm-3">
        <q-input v-model="filter" outlined dense debounce="300" label="Buscar (cajero, motivo, sección, detalle)">
          <template #append><q-icon name="search" /></template>
        </q-input>
      </div>
    </div>

    <!-- Tabla -->
    <q-table
      title="Gestionar Anulaciones"
      flat bordered dense wrap-cells
      :rows="rows"
      :columns="columns"
      row-key="id"
      :rows-per-page-options="[0]"
    :filter="filter"
    separator="cell"
    >
    <!-- fecha -->
    <template #body-cell-fecha="props">
      <q-td :props="props">{{ props.row.fecha }}</q-td>
    </template>

    <!-- monto -->
    <template #body-cell-monto="props">
      <q-td :props="props" class="text-right">{{ Number(props.row.monto ?? 0).toFixed(2) }}</q-td>
    </template>

    <!-- estado chip -->
    <template #body-cell-estado="props">
      <q-td :props="props">
        <q-chip dense :color="chipColor(props.row.estado)" text-color="white">{{ props.row.estado }}</q-chip>
      </q-td>
    </template>

    <!-- autorizado_por -->
    <template #body-cell-autorizado_por="props">
      <q-td :props="props">
        {{ props.row.user_autoriza?.name || props.row.user_autoriza?.email || '-' }}
      </q-td>
    </template>

    <!-- anulado_por -->
    <template #body-cell-anulado_por="props">
      <q-td :props="props">
        {{ props.row.user_anulacion?.name || props.row.user_anulacion?.email || '-' }}
      </q-td>
    </template>

    <!-- opciones -->
    <template #body-cell-opciones="props">
      <q-td auto-width :props="props">
        <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert" no-caps dense size="10px">
          <q-list>
            <q-item clickable v-close-popup @click="openDetail(props.row)">
              <q-item-section avatar><q-icon name="info" /></q-item-section>
              <q-item-section>
                <q-item-label>Ver detalle</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="props.row.estado =='Pendiente' && store.boolautorizar" clickable v-close-popup @click="onAutorizar(props.row)">
              <q-item-section avatar><q-icon name="check_circle" /></q-item-section>
              <q-item-section>
                <q-item-label>Autorizar</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="props.row.estado =='Pendiente' && store.boolautorizar" clickable v-close-popup @click="onRechazar(props.row)">
              <q-item-section avatar><q-icon name="cancel" /></q-item-section>
              <q-item-section>
                <q-item-label>Rechazar</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="props.row.estado =='Autorizado' && store.boolaprobar" clickable v-close-popup @click="onAnular(props.row)">
              <q-item-section avatar><q-icon name="delete_forever" /></q-item-section>
              <q-item-section><q-item-label>Anular</q-item-label></q-item-section>
            </q-item>
            <q-item clickable v-close-popup>
              <q-item-section>
                <q-btn
                  type="a"
                  color="primary"
                  no-caps
                  dense
                  class="full-width"
                  icon="print"
                  label="Imprimir Formulario"
                  :href="`${$api.defaults.baseURL}anulaciones/${props.row.id}/pdf`"
                  target="_blank"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-td>
    </template>
    </q-table>

    <!-- Detalle -->
    <q-dialog v-model="detailDialog" persistent>
      <q-card style="min-width: 420px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Detalle de Anulación #{{ selected?.id }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="q-gutter-xs">
          <div><b>Fecha:</b> {{ selected?.fecha }}</div>
          <div><b>Cajero:</b> {{ selected?.cajero }}</div>
          <div><b>Monto:</b> {{ Number(selected?.monto ?? 0).toFixed(2) }}</div>
          <div><b>Sección:</b> {{ selected?.seccion || '-' }}</div>
          <div><b>Motivo:</b> {{ selected?.motivo || '-' }}</div>
          <div><b>Detalle:</b> {{ selected?.detalle || '-' }}</div>
          <div><b>Estado:</b>
            <q-chip dense :color="chipColor(selected?.estado)" text-color="white">{{ selected?.estado }}</q-chip>
          </div>
          <div><b>Solicitado por:</b> {{ selected?.user?.name || selected?.user?.email || selected?.user_id }}</div>
          <div><b>Autorizado por:</b> {{ selected?.user_autoriza?.name || selected?.user_autoriza?.email || '-' }}</div>
          <div><b>Anulado por:</b> {{ selected?.user_anulacion?.name || selected?.user_anulacion?.email || '-' }}</div>
          <div><b>Sale ID:</b> {{ selected?.sale_id || '-' }}</div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" v-close-popup dense />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="dialogAnular" >
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">ANULAR FACTURA</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-select label="motivo" :options="motivos" v-model="motivo"/>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup :loading="loading" />
          <q-btn flat label="ANULAR" @click="enviarAnular" :loading="loading" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'
import { globalStore } from '../stores/globalStore'
export default {
  name: 'AnulacionesPage',
  data () {
    return {
      store: globalStore(),
      rows: [],
      filter: '',
      estados: ['Pendiente', 'Autorizado', 'Anulado', 'Rechazado'],
      estado: null,
      fi: moment().startOf('month').format('YYYY-MM-DD'),
      ff: moment().endOf('month').format('YYYY-MM-DD'),
      detailDialog: false,
      selected: null,
      authUser: null, // si tienes store de usuario, cámbialo aquí
      columns: [
        { name: 'opciones',       label: 'Opciones',   field: 'opciones' },
        { name: 'id',             label: '#',          field: 'id', sortable: true },
        { name: 'fecha',          label: 'Fecha',      field: 'fecha', sortable: true },
        { name: 'cajero',         label: 'Cajero',     field: 'cajero', sortable: true },
        { name: 'monto',          label: 'Monto',      field: 'monto',  sortable: true, align: 'right' },
        { name: 'seccion',        label: 'Sección',    field: 'seccion', sortable: true },
        { name: 'motivo',         label: 'Motivo',     field: 'motivo',  sortable: true },
        { name: 'detalle',        label: 'Detalle',    field: 'detalle', sortable: true },
        { name: 'estado',         label: 'Estado',     field: 'estado',  sortable: true },
        { name: 'autorizado_por', label: 'Autorizado por', field: row => row.user_autoriza?.name || row.user_autoriza?.email || '-', sortable: true },
        { name: 'anulado_por',    label: 'Anulado por',    field: row => row.user_anulacion?.name || row.user_anulacion?.email || '-', sortable: true }
      ],
      motivos: [],
      motivo: null,
      dialogAnular: false,
      loading: false,
    }
  },
  created () {
    // si tienes /user con sanctum:
    this.$api.get('user').then(r => { this.authUser = r.data }).catch(() => (this.authUser = { id: 0 }))
    this.fetchRows()
    this.cargarMotivo()
  },
  methods: {
    enviarAnular(){
      // this.$q.loading.show()
      this.loading = true
      this.$api.post('anularSale',{sale:this.factura,motivo:this.motivo}).then(res => {
        // console.log(res.data)
        // this.$q.loading.hide()
        this.fetchRows()
        this.dialogAnular = false
        this.loading = false
        this.$q.notify({ message: 'Factura anulada', color: 'positive', icon: 'done' })
      })
    },
    cargarMotivo(){
      this.$api.get('motivoanular').then(res => {
        res.data.forEach(r=>{
          r.label=r.descripcion
        })
        this.motivos=res.data;

        this.motivo=this.motivos[0]
      })
    },
    chipColor (estado) {
      switch (estado) {
        case 'Pendiente':  return 'orange-7'
        case 'Autorizado': return 'primary'
        case 'Anulado':    return 'positive'
        case 'Rechazado':  return 'negative'
        default:           return 'grey-7'
      }
    },
    fetchRows () {
      const params = { per_page: 0 } // trae todo sin paginar
      if (this.estado) params.estado = this.estado
      if (this.fi) params.fi = this.fi
      if (this.ff) params.ff = this.ff

      this.$q.loading.show()
      this.$api.get('anulaciones', { params })
        .then(res => { this.rows = res.data })
        .finally(() => this.$q.loading.hide())
    },
    openDetail (row) {
      this.selected = row
      this.detailDialog = true
    },
    canAutorizar (row) {
      if (!this.authUser) return false
      return row.estado === 'Pendiente' && row.user_id !== this.authUser.id
    },
    canAnular (row) {
      if (!this.authUser) return false
      return row.estado === 'Autorizado' && row.user_autoriza_id !== this.authUser.id
    },
    onAutorizar (row) {
      this.$q.dialog({
        title: 'Autorizar anulación',
        message: '¿Confirmas autorizar esta solicitud?',
        prompt: {
          model: '',
          type: 'text', // 👈 aquí puede ir 'password'
          label: 'Confirma tu contraseña',
          isValid: val => !!val, // opcional: fuerza que no esté vacío
          inputClass: 'password-mask'
        },
        cancel: true,
        persistent: true
      }).onOk(password => {
        this.$q.loading.show()
        this.$api.post(`anulaciones/${row.id}/autorizar`, { password })
          .then(res => {
            this.fetchRows()
          })
          .catch(err => {
            this.$q.notify({ message: err.response?.data?.message || 'Error', color: 'negative', icon: 'error' })
          })
          .finally(() => {
            this.$q.loading.hide()
          })
      }).onCancel(() => {
        // console.log('Autorización cancelada')
      })
    },
    onRechazar (row) {
      this.$q.dialog({
        title: 'Rechazar anulación',
        message: '¿Confirmas rechazar esta solicitud?',
        prompt: { model: '', type: 'text', label: 'Motivo del rechazo (opcional)' },
        cancel: true, persistent: true
      }).onOk(motivo => {
        this.$q.loading.show()
        this.$api.post(`anulaciones/${row.id}/rechazar`, { motivo })
          .then(res => this.replaceRow(res.data))
          .finally(() => this.$q.loading.hide())
      })
    },
    onAnular (row) {
      this.factura=row.sale
      this.dialogAnular=true
      // this.$q.dialog({
      //   title: 'Anular registro',
      //   message: 'Esta acción marcará la anulación como ejecutada.',
      //   prompt: { model: '', type: 'text', label: 'Detalle (opcional)' },
      //   cancel: true, persistent: true
      // }).onOk(detalle => {
      //   this.$q.loading.show()
      //   this.$api.post(`anulaciones/${row.id}/anular`, { detalle })
      //     .then(res => this.replaceRow(res.data))
      //     .finally(() => this.$q.loading.hide())
      // })
    },
    replaceRow (updated) {
      const i = this.rows.findIndex(r => r.id === updated.id)
      if (i >= 0) this.$set ? this.$set(this.rows, i, updated) : this.rows.splice(i, 1, updated)
      this.$q.notify({ message: 'Actualizado', color: 'positive', icon: 'done' })
    }
  }
}
</script>
<style>
.password-mask {
  -webkit-text-security: disc; /* Safari/Chrome */
  text-security: disc;         /* Otros navegadores que lo soporten */
}
</style>
