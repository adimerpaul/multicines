<template>
  <q-card flat bordered class="full-height vigencia-card" :class="{ 'cursor-pointer': clickable }" @click="$emit('click')">
    <q-card-section>
      <div class="row items-center no-wrap q-mb-sm">
        <q-icon :name="icon" size="22px" color="primary" class="q-mr-sm" />
        <div class="text-subtitle1 text-weight-bold">{{ titulo }}</div>
        <q-space />
        <q-chip v-if="fin" :color="est.color" text-color="white" :icon="est.icon" dense size="sm">{{ est.label }}</q-chip>
      </div>

      <div v-if="!fin" class="text-body2 text-orange-9 q-py-md">
        <q-icon name="warning" /> {{ vacio }}
      </div>

      <div v-else class="row items-center no-wrap">
        <q-circular-progress
          show-value
          :value="porcentajeRestante(inicio, fin)"
          size="84px"
          :thickness="0.18"
          :color="est.color"
          track-color="grey-3"
          class="q-mr-md"
        >
          <div class="text-center">
            <div class="text-h6 text-weight-bold" :class="'text-' + est.color">{{ Math.max(0, diasRestantes(fin)) }}</div>
            <div style="font-size: 10px" class="text-grey-7">días</div>
          </div>
        </q-circular-progress>
        <div style="min-width: 0">
          <div class="text-body2 ellipsis" :class="mono ? 'mono' : 'text-weight-medium'">{{ detalle }}</div>
          <div class="text-caption text-grey-7 ellipsis" v-if="subdetalle">{{ subdetalle }}</div>
          <div class="text-body2 q-mt-xs">Vence: <b>{{ formatFecha(fin, conHora) }}</b></div>
          <div class="text-caption" :class="'text-' + est.color">{{ textoRestante(fin) }}</div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
<script>
import { diasRestantes, textoRestante, porcentajeRestante, estado, formatFecha } from 'src/utils/vigencia'

export default {
  name: 'VigenciaCard',
  emits: ['click'],
  props: {
    titulo: String,
    icon: String,
    inicio: String,
    fin: String,
    detalle: String,
    subdetalle: String,
    vacio: String,
    mono: Boolean,
    conHora: Boolean,
    clickable: Boolean,
    aviso: { type: Number, default: 30 },
    urgente: { type: Number, default: 7 },
  },
  computed: {
    est() {
      return estado(this.fin, this.aviso, this.urgente)
    }
  },
  methods: { diasRestantes, textoRestante, porcentajeRestante, formatFecha },
}
</script>
<style scoped>
.mono { font-family: monospace; }
.vigencia-card { transition: box-shadow 0.2s; }
.vigencia-card.cursor-pointer:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12); }
</style>
