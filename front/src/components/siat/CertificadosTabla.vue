<template>
  <div>
    <q-table
      :rows="rows"
      :columns="columns"
      :loading="loading"
      row-key="id"
      dense
      flat
      bordered
      title="Certificados digitales (.p12)"
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <q-btn color="green" icon="upload_file" label="Subir certificado .p12" no-caps @click="abrirDialog" />
      </template>

      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <q-chip v-if="props.row.activo" color="primary" text-color="white" icon="verified" dense size="sm">En uso</q-chip>
          <q-chip :color="est(props.row).color" text-color="white" :icon="est(props.row).icon" dense size="sm">
            {{ est(props.row).label }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-titular="props">
        <q-td :props="props">
          <div class="text-weight-medium">{{ props.row.titular }}</div>
          <div class="text-caption text-grey-7">{{ props.row.organizacion }} · NIT {{ props.row.nit }}</div>
        </q-td>
      </template>

      <template v-slot:body-cell-vigencia="props">
        <q-td :props="props">{{ formatFecha(props.row.fechaInicio) }} → {{ formatFecha(props.row.fechaFin) }}</q-td>
      </template>

      <template v-slot:body-cell-dias="props">
        <q-td :props="props" style="min-width: 170px">
          <div class="row items-center no-wrap">
            <div class="text-weight-bold q-mr-sm" :class="'text-' + est(props.row).color" style="min-width: 70px">
              {{ textoRestante(props.row.fechaFin) }}
            </div>
            <q-linear-progress
              rounded
              size="8px"
              :value="porcentajeRestante(props.row.fechaInicio, props.row.fechaFin) / 100"
              :color="est(props.row).color"
              track-color="grey-3"
              class="col"
            />
          </div>
        </q-td>
      </template>

      <template v-slot:body-cell-subido="props">
        <q-td :props="props">
          <div>{{ formatFecha(props.row.created_at) }}</div>
          <div class="text-caption text-grey-7">{{ props.row.user?.name }} · {{ props.row.archivo }}</div>
        </q-td>
      </template>

      <template v-slot:body-cell-opciones="props">
        <q-td :props="props">
          <q-btn
            v-if="!props.row.activo"
            dense
            flat
            color="primary"
            icon="published_with_changes"
            :disable="diasRestantes(props.row.fechaFin) < 0"
            @click="activar(props.row)"
          >
            <q-tooltip>Usar este certificado para firmar</q-tooltip>
          </q-btn>
          <q-btn v-if="!props.row.activo" dense flat color="negative" icon="delete" @click="eliminar(props.row)">
            <q-tooltip>Eliminar</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 360px; max-width: 480px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Subir certificado digital</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="text-body2 text-grey-8 q-mb-md">
            Se extraerán la llave privada y el certificado público del archivo .p12. La llave privada se guarda cifrada en
            la base de datos.
          </div>
          <q-form @submit.prevent="subir">
            <q-file
              v-model="form.archivo"
              outlined
              dense
              label="Archivo .p12 / .pfx"
              accept=".p12,.pfx"
              class="q-mb-sm"
              :rules="[v => !!v || 'Seleccione el archivo']"
            >
              <template v-slot:prepend><q-icon name="attach_file" /></template>
            </q-file>
            <q-input
              v-model="form.password"
              outlined
              dense
              label="Contraseña del certificado"
              :type="verPassword ? 'text' : 'password'"
              class="q-mb-sm"
              :rules="[v => !!v || 'Ingrese la contraseña']"
            >
              <template v-slot:append>
                <q-icon :name="verPassword ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verPassword = !verPassword" />
              </template>
            </q-input>
            <q-checkbox v-model="form.activar" label="Usar este certificado para firmar las facturas desde ahora" class="q-mb-md" />
            <q-btn class="full-width" :loading="guardando" type="submit" color="green" icon="check" label="Subir y generar llaves" no-caps />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>
<script>
import { diasRestantes, textoRestante, porcentajeRestante, estado, formatFecha, UMBRALES } from 'src/utils/vigencia'

export default {
  name: 'CertificadosTabla',
  emits: ['refresh'],
  props: {
    rows: { type: Array, default: () => [] },
    loading: Boolean,
  },
  data() {
    return {
      dialog: false,
      guardando: false,
      verPassword: false,
      form: { archivo: null, password: '', activar: true },
      columns: [
        { name: 'estado', label: 'Estado', field: 'activo', align: 'left' },
        { name: 'titular', label: 'Titular', field: 'titular', align: 'left', sortable: true },
        { name: 'emisor', label: 'Emisor', field: 'emisor', align: 'left' },
        { name: 'vigencia', label: 'Vigencia', field: 'fechaFin', align: 'left', sortable: true },
        { name: 'dias', label: 'Tiempo restante', field: row => diasRestantes(row.fechaFin), align: 'left', sortable: true },
        { name: 'subido', label: 'Subido', field: 'created_at', align: 'left', sortable: true },
        { name: 'opciones', label: '', field: 'id', align: 'right' },
      ],
    }
  },
  methods: {
    diasRestantes,
    textoRestante,
    porcentajeRestante,
    formatFecha,
    est(row) {
      return estado(row.fechaFin, UMBRALES.certificado.aviso, UMBRALES.certificado.urgente)
    },
    abrirDialog() {
      this.form = { archivo: null, password: '', activar: true }
      this.verPassword = false
      this.dialog = true
    },
    subir() {
      const data = new FormData()
      data.append('archivo', this.form.archivo)
      data.append('password', this.form.password)
      data.append('activar', this.form.activar ? '1' : '0')
      this.guardando = true
      this.$api.post('certificado', data, { headers: { 'Content-Type': 'multipart/form-data' } }).then(() => {
        this.dialog = false
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Certificado registrado y llaves generadas' })
      }).catch(this.notificarError).finally(() => {
        this.guardando = false
        this.$emit('refresh')
      })
    },
    activar(row) {
      this.$q.dialog({
        title: 'Activar certificado',
        message: `Las próximas facturas se firmarán con el certificado de ${row.titular} (vence ${formatFecha(row.fechaFin)}). ¿Continuar?`,
        cancel: true,
        persistent: true,
      }).onOk(() => {
        this.$api.post(`certificado/${row.id}/activar`).then(() => {
          this.$q.notify({ color: 'positive', icon: 'done', message: 'Certificado activado' })
          this.$emit('refresh')
        }).catch(this.notificarError)
      })
    },
    eliminar(row) {
      this.$q.dialog({
        title: 'Eliminar certificado',
        message: `¿Eliminar el certificado de ${row.titular}?`,
        cancel: true,
        persistent: true,
      }).onOk(() => {
        this.$api.delete(`certificado/${row.id}`).then(() => this.$emit('refresh')).catch(this.notificarError)
      })
    },
    notificarError(err) {
      this.$q.notify({
        color: 'negative',
        icon: 'error',
        position: 'top',
        timeout: 5000,
        message: err.response?.data?.message || 'Ocurrió un error',
      })
    },
  },
}
</script>
