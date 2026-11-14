<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div>
        <div class="text-h6 text-weight-bold">Vigencias SIAT</div>
        <div class="text-caption text-grey-7">Fechas de vencimiento y renovación del CUIS, la firma digital y el token</div>
      </div>
      <q-space />
      <q-btn flat dense icon="refresh" label="Actualizar" no-caps :loading="cargando" @click="cargarTodo" />
    </div>

    <!-- Alerta si algo vence pronto -->
    <q-banner v-if="alertas.length" rounded class="bg-red-1 text-negative q-mb-md">
      <template v-slot:avatar><q-icon name="warning" color="negative" /></template>
      <div v-for="a in alertas" :key="a">{{ a }}</div>
    </q-banner>

    <!-- Resumen: los tres vencimientos juntos -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4" v-if="store.boolcuis">
        <VigenciaCard
          titulo="CUIS"
          icon="o_psychology"
          :inicio="cuiVigente && (cuiVigente.fechaCreacion || cuiVigente.created_at)"
          :fin="cuiVigente && cuiVigente.fechaVigencia"
          :detalle="cuiVigente && cuiVigente.codigo"
          subdetalle="Punto de venta 0"
          vacio="No hay un CUI vigente. Genere uno nuevo para poder facturar."
          mono
          con-hora
          clickable
          :aviso="UMBRALES.cui.aviso"
          :urgente="UMBRALES.cui.urgente"
          @click="tab = 'cuis'"
        />
      </div>
      <div class="col-12 col-md-4" v-if="store.boolcertificado">
        <VigenciaCard
          titulo="Certificado digital (firma)"
          icon="o_verified_user"
          :inicio="certActivo && certActivo.fechaInicio"
          :fin="certActivo && certActivo.fechaFin"
          :detalle="certActivo && certActivo.titular"
          :subdetalle="certActivo && (certActivo.emisor || certActivo.organizacion)"
          vacio="Ningún certificado registrado está activo; se usan las llaves que ya existen en el servidor."
          clickable
          :aviso="UMBRALES.certificado.aviso"
          :urgente="UMBRALES.certificado.urgente"
          @click="tab = 'certificados'"
        />
      </div>
      <div class="col-12 col-md-4" v-if="store.booltokensiat">
        <VigenciaCard
          titulo="Token SIAT"
          icon="o_key"
          :inicio="tokenActivo && tokenActivo.fechaEmision"
          :fin="tokenActivo && tokenActivo.fechaVigencia"
          :detalle="tokenActivo && tokenActivo.tokenCorto"
          :subdetalle="tokenActivo && ('NIT ' + tokenActivo.nit)"
          vacio="No hay un token activo en la base de datos; se usa el TOKEN del .env."
          mono
          clickable
          :aviso="UMBRALES.token.aviso"
          :urgente="UMBRALES.token.urgente"
          @click="tab = 'tokens'"
        />
      </div>
    </div>

    <div v-if="!tabsVisibles.length" class="text-center text-h6 text-negative q-mt-xl">
      <q-icon name="lock" /> No tiene permisos para ver esta página
    </div>

    <q-card v-else flat bordered>
      <q-tabs v-model="tab" dense align="left" active-color="primary" indicator-color="primary" no-caps class="text-grey-8">
        <q-tab v-if="store.boolcuis" name="cuis" icon="o_psychology" label="CUIS" />
        <q-tab v-if="store.boolcertificado" name="certificados" icon="o_verified_user" label="Certificado digital" />
        <q-tab v-if="store.booltokensiat" name="tokens" icon="o_key" label="Token SIAT" />
      </q-tabs>
      <q-separator />
      <q-tab-panels v-model="tab" animated>
        <q-tab-panel v-if="store.boolcuis" name="cuis" class="q-pa-sm">
          <CuisTabla :rows="cuis" :loading="loading.cuis" @refresh="getCuis" />
        </q-tab-panel>
        <q-tab-panel v-if="store.boolcertificado" name="certificados" class="q-pa-sm">
          <CertificadosTabla :rows="certificados" :loading="loading.certificados" @refresh="getCertificados" />
        </q-tab-panel>
        <q-tab-panel v-if="store.booltokensiat" name="tokens" class="q-pa-sm">
          <TokensTabla :rows="tokens" :loading="loading.tokens" @refresh="getTokens" />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-page>
</template>
<script>
import moment from 'moment'
import { globalStore } from 'stores/globalStore'
import { diasRestantes, formatFecha, UMBRALES } from 'src/utils/vigencia'
import VigenciaCard from 'components/siat/VigenciaCard.vue'
import CuisTabla from 'components/siat/CuisTabla.vue'
import CertificadosTabla from 'components/siat/CertificadosTabla.vue'
import TokensTabla from 'components/siat/TokensTabla.vue'

export default {
  name: 'VigenciasSiat',
  components: { VigenciaCard, CuisTabla, CertificadosTabla, TokensTabla },
  data() {
    return {
      store: globalStore(),
      UMBRALES,
      tab: this.$route.query.tab || 'cuis',
      cuis: [],
      certificados: [],
      tokens: [],
      loading: { cuis: false, certificados: false, tokens: false },
    }
  },
  computed: {
    cargando() {
      return this.loading.cuis || this.loading.certificados || this.loading.tokens
    },
    tabsVisibles() {
      const t = []
      if (this.store.boolcuis) t.push('cuis')
      if (this.store.boolcertificado) t.push('certificados')
      if (this.store.booltokensiat) t.push('tokens')
      return t
    },
    cuiVigente() {
      return this.cuis
        .filter(c => !c.deleted_at && Number(c.codigoPuntoVenta) === 0 && moment(c.fechaVigencia).isAfter(moment()))
        .sort((a, b) => moment(b.fechaVigencia) - moment(a.fechaVigencia))[0] || null
    },
    certActivo() {
      return this.certificados.find(c => c.activo) || null
    },
    tokenActivo() {
      return this.tokens.find(t => t.activo) || null
    },
    alertas() {
      const a = []
      const revisar = (nombre, fin, urgente) => {
        if (!fin) return
        const d = diasRestantes(fin)
        if (d < 0) a.push(`${nombre} está VENCIDO desde el ${formatFecha(fin)}.`)
        else if (d <= urgente) a.push(`${nombre} vence en ${d} día${d === 1 ? '' : 's'} (${formatFecha(fin)}). Renuévelo.`)
      }
      if (this.store.boolcuis && !this.loading.cuis && this.cuis.length && !this.cuiVigente) a.push('No hay un CUIS vigente.')
      if (this.cuiVigente) revisar('El CUIS', this.cuiVigente.fechaVigencia, UMBRALES.cui.urgente)
      if (this.certActivo) revisar('El certificado digital', this.certActivo.fechaFin, UMBRALES.certificado.urgente)
      if (this.tokenActivo) revisar('El token SIAT', this.tokenActivo.fechaVigencia, UMBRALES.token.urgente)
      return a
    },
  },
  watch: {
    // los permisos llegan después de /me al recargar la página
    tabsVisibles(v) {
      if (v.length && !v.includes(this.tab)) this.tab = v[0]
      this.cargarTodo()
    },
    '$route.query.tab'(t) {
      if (t) this.tab = t
    },
  },
  created() {
    this.cargarTodo()
  },
  methods: {
    cargarTodo() {
      if (this.store.boolcuis) this.getCuis()
      if (this.store.boolcertificado) this.getCertificados()
      if (this.store.booltokensiat) this.getTokens()
    },
    cargar(clave, url) {
      this.loading[clave] = true
      this.$api.get(url).then(res => {
        this[clave] = res.data
      }).catch(err => {
        this.$q.notify({ color: 'negative', icon: 'error', message: err.response?.data?.message || 'Error al cargar ' + clave })
      }).finally(() => {
        this.loading[clave] = false
      })
    },
    getCuis() { this.cargar('cuis', 'cui') },
    getCertificados() { this.cargar('certificados', 'certificado') },
    getTokens() { this.cargar('tokens', 'siatToken') },
  },
}
</script>
