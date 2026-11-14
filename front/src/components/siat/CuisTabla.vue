<template>
  <div>
    <q-table
      :rows="rows"
      :columns="columns"
      :filter="filtro"
      :loading="loading"
      row-key="id"
      dense
      flat
      bordered
      title="Historial de CUIS"
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <q-input outlined dense debounce="300" v-model="filtro" placeholder="Buscar" class="q-mr-sm">
          <template v-slot:append><q-icon name="search" /></template>
        </q-input>
        <q-btn color="green" icon="add_circle" label="Generar CUI" no-caps :loading="guardando" @click="dialog = true" />
      </template>

      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <q-chip :color="est(props.row).color" text-color="white" :icon="est(props.row).icon" dense size="sm">
            {{ est(props.row).label }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-codigo="props">
        <q-td :props="props">
          <span style="font-family: monospace">{{ props.row.codigo }}</span>
          <q-btn flat round dense size="xs" icon="content_copy" @click="copiar(props.row.codigo)">
            <q-tooltip>Copiar</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <template v-slot:body-cell-fechaCreacion="props">
        <q-td :props="props">{{ formatFecha(inicio(props.row), true) }}</q-td>
      </template>

      <template v-slot:body-cell-fechaVigencia="props">
        <q-td :props="props">{{ formatFecha(props.row.fechaVigencia, true) }}</q-td>
      </template>

      <template v-slot:body-cell-opciones="props">
        <q-td :props="props">
          <q-btn
            v-if="!props.row.deleted_at && diasRestantes(props.row.fechaVigencia) >= 0"
            dense
            flat
            no-caps
            color="negative"
            icon="remove_circle_outline"
            label="Dar de baja"
            @click="abrirBaja(props.row)"
          />
        </q-td>
      </template>

      <template v-slot:body-cell-dias="props">
        <q-td :props="props" style="min-width: 180px">
          <div v-if="props.row.deleted_at" class="text-caption text-brown-6">
            Baja el {{ formatFecha(props.row.deleted_at, true) }}
            <span v-if="props.row.user_baja"> · {{ props.row.user_baja.name }}</span>
            <div v-if="props.row.motivoBaja">Motivo: {{ props.row.motivoBaja }}</div>
          </div>
          <div v-else class="row items-center no-wrap">
            <div class="text-weight-bold q-mr-sm" :class="'text-' + est(props.row).color" style="min-width: 70px">
              {{ textoRestante(props.row.fechaVigencia) }}
            </div>
            <q-linear-progress
              rounded
              size="8px"
              :value="porcentajeRestante(inicio(props.row), props.row.fechaVigencia) / 100"
              :color="est(props.row).color"
              track-color="grey-3"
              class="col"
            />
          </div>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="bajaDialog" persistent>
      <q-card style="min-width: 340px; max-width: 460px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 text-negative">Dar de baja el CUIS</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section v-if="bajaRow">
          <div class="text-body2 q-mb-xs">
            CUIS <b style="font-family: monospace">{{ bajaRow.codigo }}</b> (vence {{ formatFecha(bajaRow.fechaVigencia) }})
          </div>
          <div class="text-body2 text-grey-8 q-mb-md">
            Dejará de usarse para facturar y quedará en el historial como "Dado de baja".
            Si no genera uno nuevo, no se podrá facturar hasta que lo haga.
          </div>
          <q-form @submit.prevent="darBaja">
            <q-input
              v-model="baja.motivo"
              outlined
              dense
              autofocus
              label="Motivo de la baja"
              class="q-mb-sm"
              :rules="[v => !!v || 'Ingrese el motivo']"
            />
            <q-checkbox v-model="baja.generar" label="Generar un CUIS nuevo enseguida" class="q-mb-md" />
            <q-btn
              class="full-width"
              :loading="guardando"
              type="submit"
              color="negative"
              icon="remove_circle_outline"
              :label="baja.generar ? 'Dar de baja y generar nuevo' : 'Dar de baja'"
              no-caps
            />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialog">
      <q-card style="min-width: 320px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Generar CUI</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="text-body2 text-grey-8 q-mb-sm">
            Se solicitará un nuevo Código Único de Inicio de Sistemas al SIAT. Solo se puede generar si no existe uno vigente.
          </div>
          <q-form @submit.prevent="crear">
            <q-select :options="[0]" dense outlined label="Punto de venta" v-model="cui.codigoPuntoVenta" class="q-mb-sm" />
            <q-btn dense class="full-width" :loading="guardando" type="submit" color="green" icon="check" label="Generar" no-caps />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>
<script>
import { copyToClipboard } from 'quasar'
import { diasRestantes, textoRestante, porcentajeRestante, estado, formatFecha, UMBRALES } from 'src/utils/vigencia'

export default {
  name: 'CuisTabla',
  emits: ['refresh'],
  props: {
    rows: { type: Array, default: () => [] },
    loading: Boolean,
  },
  data() {
    return {
      filtro: '',
      dialog: false,
      guardando: false,
      cui: { codigoPuntoVenta: 0, codigoSucursal: 0 },
      bajaDialog: false,
      bajaRow: null,
      baja: { motivo: '', generar: true },
      columns: [
        { name: 'estado', label: 'Estado', field: row => this.est(row).label, align: 'left', sortable: true },
        { name: 'codigo', label: 'Código CUI', field: 'codigo', align: 'left', sortable: true },
        { name: 'codigoPuntoVenta', label: 'Punto venta', field: 'codigoPuntoVenta', align: 'center', sortable: true },
        { name: 'codigoSucursal', label: 'Sucursal', field: 'codigoSucursal', align: 'center', sortable: true },
        { name: 'fechaCreacion', label: 'Generado', field: 'fechaCreacion', align: 'left', sortable: true },
        { name: 'fechaVigencia', label: 'Vence', field: 'fechaVigencia', align: 'left', sortable: true },
        { name: 'dias', label: 'Tiempo restante', field: row => diasRestantes(row.fechaVigencia), align: 'left', sortable: true },
        { name: 'opciones', label: '', field: 'id', align: 'right' },
      ],
    }
  },
  methods: {
    diasRestantes,
    textoRestante,
    porcentajeRestante,
    formatFecha,
    inicio(row) {
      return row.fechaCreacion || row.created_at
    },
    est(row) {
      if (row.deleted_at) return { label: 'Dado de baja', color: 'brown-5', icon: 'remove_circle' }
      return estado(row.fechaVigencia, UMBRALES.cui.aviso, UMBRALES.cui.urgente)
    },
    copiar(texto) {
      copyToClipboard(texto).then(() => {
        this.$q.notify({ message: 'Código copiado', color: 'positive', icon: 'content_copy', timeout: 1000 })
      })
    },
    abrirBaja(row) {
      this.bajaRow = row
      this.baja = { motivo: '', generar: true }
      this.bajaDialog = true
    },
    darBaja() {
      const row = this.bajaRow
      this.guardando = true
      this.$api.delete(`cui/${row.id}`, { data: { motivo: this.baja.motivo } }).then(() => {
        this.$q.notify({ color: 'positive', icon: 'done', message: 'CUIS dado de baja' })
        this.bajaDialog = false
        if (this.baja.generar) {
          this.cui.codigoPuntoVenta = Number(row.codigoPuntoVenta)
          this.cui.codigoSucursal = Number(row.codigoSucursal)
          return this.solicitarCui().catch(() => {})
        }
      }).catch(this.notificarError).finally(() => {
        this.guardando = false
        this.$emit('refresh')
      })
    },
    crear() {
      this.guardando = true
      this.solicitarCui().then(() => {
        this.dialog = false
      }).catch(() => {}).finally(() => {
        this.guardando = false
      })
    },
    // Pide un CUIS nuevo al SIAT
    solicitarCui() {
      return this.$api.post('cui', this.cui).then(res => {
        this.$emit('refresh')
        this.$q.notify({
          color: 'positive',
          icon: 'done',
          message: 'CUIS generado: ' + (res.data.codigo || ''),
          caption: res.data.mensaje || '',
          timeout: 6000,
        })
      }).catch(err => {
        this.notificarError(err, 'Error al generar el CUIS')
        throw err
      })
    },
    notificarError(err, porDefecto = 'Ocurrió un error') {
      this.$q.notify({
        color: 'negative',
        message: err.response?.data?.message || porDefecto,
        position: 'top',
        icon: 'error',
        timeout: 6000,
      })
    },
  },
}
</script>
