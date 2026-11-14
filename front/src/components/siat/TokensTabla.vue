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
      title="Historial de tokens SIAT"
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <q-btn color="green" icon="add_circle" label="Registrar nuevo token" no-caps @click="abrirDialog" />
      </template>

      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <q-chip v-if="props.row.activo" color="primary" text-color="white" icon="verified" dense size="sm">En uso</q-chip>
          <q-chip :color="est(props.row).color" text-color="white" :icon="est(props.row).icon" dense size="sm">
            {{ est(props.row).label }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-token="props">
        <q-td :props="props">
          <div style="font-family: monospace">{{ props.row.tokenCorto }}</div>
          <div class="text-caption text-grey-7">{{ props.row.descripcion }}</div>
        </q-td>
      </template>

      <template v-slot:body-cell-titular="props">
        <q-td :props="props">
          <div>NIT {{ props.row.nit }}</div>
          <div class="text-caption text-grey-7">{{ props.row.email }}</div>
        </q-td>
      </template>

      <template v-slot:body-cell-vigencia="props">
        <q-td :props="props">{{ formatFecha(props.row.fechaEmision) }} → {{ formatFecha(props.row.fechaVigencia) }}</q-td>
      </template>

      <template v-slot:body-cell-dias="props">
        <q-td :props="props" style="min-width: 170px">
          <div class="row items-center no-wrap">
            <div class="text-weight-bold q-mr-sm" :class="'text-' + est(props.row).color" style="min-width: 70px">
              {{ textoRestante(props.row.fechaVigencia) }}
            </div>
            <q-linear-progress
              rounded
              size="8px"
              :value="porcentajeRestante(props.row.fechaEmision, props.row.fechaVigencia) / 100"
              :color="est(props.row).color"
              track-color="grey-3"
              class="col"
            />
          </div>
        </q-td>
      </template>

      <template v-slot:body-cell-registrado="props">
        <q-td :props="props">
          <div>{{ formatFecha(props.row.created_at) }}</div>
          <div class="text-caption text-grey-7">{{ props.row.user?.name || 'Sistema' }}</div>
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
            :disable="diasRestantes(props.row.fechaVigencia) < 0"
            @click="activar(props.row)"
          >
            <q-tooltip>Usar este token</q-tooltip>
          </q-btn>
          <q-btn v-if="!props.row.activo" dense flat color="negative" icon="delete" @click="eliminar(props.row)">
            <q-tooltip>Eliminar</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 360px; max-width: 560px; width: 100%">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar token del SIAT</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="text-body2 text-grey-8 q-mb-md">
            Pegue el token delegado que genera el SIAT. La fecha de vencimiento se lee automáticamente del token.
          </div>
          <q-form @submit.prevent="guardar">
            <q-input
              v-model="form.token"
              outlined
              dense
              type="textarea"
              autogrow
              label="Token (eyJ...)"
              class="q-mb-sm"
              input-style="font-family: monospace; font-size: 12px; max-height: 160px"
              :rules="[v => !!v || 'Pegue el token']"
            />
            <q-banner v-if="previa" dense rounded class="q-mb-sm" :class="previa.error ? 'bg-red-1 text-negative' : 'bg-green-1 text-positive'">
              <template v-if="previa.error">{{ previa.error }}</template>
              <template v-else>
                NIT {{ previa.nit }} · Vence el <b>{{ formatFecha(previa.fechaVigencia) }}</b> ({{ previa.texto }})
              </template>
            </q-banner>
            <q-input v-model="form.descripcion" outlined dense label="Descripción (opcional)" class="q-mb-sm" />
            <q-checkbox v-model="form.activar" label="Usar este token desde ahora" class="q-mb-md" />
            <q-btn class="full-width" :loading="guardando" type="submit" color="green" icon="check" label="Guardar" no-caps />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>
<script>
import moment from 'moment'
import { diasRestantes, textoRestante, porcentajeRestante, estado, formatFecha, UMBRALES } from 'src/utils/vigencia'

export default {
  name: 'TokensTabla',
  emits: ['refresh'],
  props: {
    rows: { type: Array, default: () => [] },
    loading: Boolean,
  },
  data() {
    return {
      dialog: false,
      guardando: false,
      form: { token: '', descripcion: '', activar: true },
      columns: [
        { name: 'estado', label: 'Estado', field: 'activo', align: 'left' },
        { name: 'token', label: 'Token', field: 'tokenCorto', align: 'left' },
        { name: 'titular', label: 'Delegado', field: 'nit', align: 'left' },
        { name: 'vigencia', label: 'Vigencia', field: 'fechaVigencia', align: 'left', sortable: true },
        { name: 'dias', label: 'Tiempo restante', field: row => diasRestantes(row.fechaVigencia), align: 'left', sortable: true },
        { name: 'registrado', label: 'Registrado', field: 'created_at', align: 'left', sortable: true },
        { name: 'opciones', label: '', field: 'id', align: 'right' },
      ],
    }
  },
  computed: {
    // Lee la fecha del token pegado antes de guardarlo
    previa() {
      const t = (this.form.token || '').trim().replace(/^"|"$/g, '')
      if (!t) return null
      try {
        const partes = t.split('.')
        if (partes.length !== 3) throw new Error()
        const json = JSON.parse(atob(partes[1].replace(/-/g, '+').replace(/_/g, '/')))
        if (!json.exp) return { error: 'El token no tiene fecha de vencimiento' }
        const fin = moment.unix(json.exp).format('YYYY-MM-DD HH:mm:ss')
        return { nit: json.nitDelegado, fechaVigencia: fin, texto: textoRestante(fin) }
      } catch (e) {
        return { error: 'No parece un token válido del SIAT' }
      }
    },
  },
  methods: {
    diasRestantes,
    textoRestante,
    porcentajeRestante,
    formatFecha,
    est(row) {
      return estado(row.fechaVigencia, UMBRALES.token.aviso, UMBRALES.token.urgente)
    },
    abrirDialog() {
      this.form = { token: '', descripcion: '', activar: true }
      this.dialog = true
    },
    guardar() {
      this.guardando = true
      this.$api.post('siatToken', this.form).then(() => {
        this.dialog = false
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Token registrado' })
        this.$emit('refresh')
      }).catch(this.notificarError).finally(() => {
        this.guardando = false
      })
    },
    activar(row) {
      this.$q.dialog({
        title: 'Activar token',
        message: `Todas las conexiones con el SIAT usarán este token (vence ${formatFecha(row.fechaVigencia)}). ¿Continuar?`,
        cancel: true,
        persistent: true,
      }).onOk(() => {
        this.$api.post(`siatToken/${row.id}/activar`).then(() => {
          this.$q.notify({ color: 'positive', icon: 'done', message: 'Token activado' })
          this.$emit('refresh')
        }).catch(this.notificarError)
      })
    },
    eliminar(row) {
      this.$q.dialog({
        title: 'Eliminar token',
        message: '¿Eliminar este token del historial?',
        cancel: true,
        persistent: true,
      }).onOk(() => {
        this.$api.delete(`siatToken/${row.id}`).then(() => this.$emit('refresh')).catch(this.notificarError)
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
