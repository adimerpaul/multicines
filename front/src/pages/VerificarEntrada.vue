<template>
  <q-page class="q-pa-sm">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pb-none">
            <div class="text-h6">Verificar entradas web</div>
            <q-space />
            <q-toggle v-model="auto" dense label="Marcar ingreso al escanear" />
          </q-card-section>
          <q-card-section>
            <q-form @submit.prevent="enviar">
              <q-input
                ref="codigo"
                v-model="codigo"
                outlined
                autofocus
                clearable
                label="Escanea el QR o escribe el código (MP-XXXXXXXX)"
                :loading="loading"
                @clear="resultado = null"
              >
                <template v-slot:prepend><q-icon name="qr_code_scanner" /></template>
                <template v-slot:append>
                  <q-btn flat round dense icon="search" type="submit" />
                </template>
              </q-input>
            </q-form>

            <div class="row items-center q-gutter-sm q-mt-sm">
              <q-btn
                :color="camara ? 'negative' : 'primary'"
                :icon="camara ? 'videocam_off' : 'photo_camera'"
                :label="camara ? 'Apagar cámara' : 'Usar cámara'"
                :loading="iniciandoCamara"
                @click="camara ? cerrarCamara() : abrirCamara()"
              />
              <q-select
                v-if="camaras.length > 1"
                v-model="camaraId"
                :options="camaras"
                option-value="id"
                option-label="label"
                emit-value
                map-options
                dense
                outlined
                label="Cámara"
                style="min-width: 200px"
                @update:model-value="cambiarCamara"
              />
            </div>
            <div v-show="camara" class="lector q-mt-sm">
              <video ref="video" muted playsinline />
              <div class="lector-estado">{{ loading ? 'Verificando…' : 'Apunta al QR de la entrada' }}</div>
            </div>
          </q-card-section>

          <q-card-section v-if="resultado" class="q-pt-none">
            <q-banner :class="banner.clase" rounded class="text-white q-mb-md">
              <template v-slot:avatar><q-icon :name="banner.icono" size="48px" /></template>
              <div class="text-h5 text-weight-bold">{{ banner.titulo }}</div>
              <div v-if="mensaje">{{ mensaje }}</div>
              <div v-if="resultado.verificado && resultado.verificado_at">
                Ingresó {{ fechaHora(resultado.verificado_at) }}<span v-if="resultado.verificado_por"> · {{ resultado.verificado_por }}</span>
              </div>
            </q-banner>

            <q-list dense bordered separator class="rounded-borders">
              <q-item><q-item-section>Código</q-item-section><q-item-section side class="text-weight-bold text-black">{{ resultado.codigo }}</q-item-section></q-item>
              <q-item><q-item-section>Película</q-item-section><q-item-section side class="text-weight-bold text-black">{{ resultado.pelicula }}</q-item-section></q-item>
              <q-item><q-item-section>Función</q-item-section><q-item-section side class="text-black">{{ resultado.fecha_funcion }} · {{ resultado.hora_funcion }} · {{ resultado.sala }}</q-item-section></q-item>
              <q-item>
                <q-item-section>Asientos ({{ resultado.cantidad }})</q-item-section>
                <q-item-section side>
                  <div class="row q-gutter-xs justify-end">
                    <q-chip v-for="a in resultado.asientos" :key="a" dense color="primary" text-color="white" icon="event_seat">{{ a }}</q-chip>
                  </div>
                </q-item-section>
              </q-item>
              <q-item><q-item-section>Cliente</q-item-section><q-item-section side class="text-black">{{ resultado.cliente }} ({{ resultado.nit }})</q-item-section></q-item>
              <q-item><q-item-section>Total</q-item-section><q-item-section side class="text-black">Bs. {{ Number(resultado.total).toFixed(2) }}</q-item-section></q-item>
            </q-list>

            <q-btn
              v-if="resultado.estado === 'VIGENTE'"
              class="full-width q-mt-md"
              color="positive"
              size="lg"
              icon="how_to_reg"
              label="Marcar ingreso"
              :loading="loading"
              @click="verificar(resultado.codigo)"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>

  </q-page>
</template>

<script>
import moment from 'moment'
import QrScanner from 'qr-scanner'

// Un QR que sigue frente a la camara no se vuelve a enviar: solo cuenta de
// nuevo si salio de cuadro al menos este tiempo (asi no pasa a "YA USADA" solo)
const FUERA_DE_CUADRO_MS = 3000

export default {
  name: 'VerificarEntrada',
  data() {
    return {
      codigo: '',
      auto: true,
      loading: false,
      resultado: null,
      recienVerificada: false,
      mensaje: '',
      camara: false,
      iniciandoCamara: false,
      camaras: [],
      camaraId: 'environment',
      ultimoQr: '',
      ultimoQrAt: 0,
    }
  },
  computed: {
    banner() {
      const estado = this.resultado ? this.resultado.estado : ''
      if (estado === 'VIGENTE') return { clase: 'bg-blue-7', icono: 'confirmation_number', titulo: 'ENTRADA VIGENTE' }
      if (estado === 'USADA' && this.recienVerificada) return { clase: 'bg-positive', icono: 'check_circle', titulo: 'PUEDE INGRESAR' }
      if (estado === 'USADA') return { clase: 'bg-negative', icono: 'block', titulo: 'ENTRADA YA USADA' }
      return { clase: 'bg-negative', icono: 'cancel', titulo: 'ENTRADA ANULADA' }
    },
  },
  beforeUnmount() {
    this.cerrarCamara()
  },
  methods: {
    fechaHora(v) {
      return moment(v).format('DD/MM/YYYY HH:mm')
    },
    limpiar(v) {
      return String(v || '').trim().toUpperCase()
    },
    enviar() {
      const codigo = this.limpiar(this.codigo)
      if (!codigo) return
      if (this.auto) this.verificar(codigo)
      else this.buscar(codigo)
    },
    buscar(codigo) {
      this.loading = true
      this.recienVerificada = false
      this.$api.post('entradaWeb/buscar', { codigo }).then((res) => {
        this.resultado = res.data
        this.mensaje = ''
      }).catch(this.error).finally(this.listo)
    },
    verificar(codigo) {
      this.loading = true
      this.$api.post('entradaWeb/verificar', { codigo }).then((res) => {
        this.recienVerificada = true
        this.resultado = res.data
        this.mensaje = res.data.message
      }).catch(this.error).finally(this.listo)
    },
    error(err) {
      const data = (err.response && err.response.data) || {}
      this.recienVerificada = false
      // 409 trae la entrada (ya usada / anulada) para mostrarla en rojo
      this.resultado = data.codigo ? data : null
      this.mensaje = data.message || 'No se pudo consultar la entrada'
      if (!data.codigo) this.$alert.error(this.mensaje)
    },
    listo() {
      this.loading = false
      // Listo para el siguiente escaneo
      this.codigo = ''
      if (!this.camara) this.$nextTick(() => this.$refs.codigo && this.$refs.codigo.focus())
    },

    // ---------- Camara (lee QR seguidos sin cerrarse) ----------
    async abrirCamara() {
      if (!window.isSecureContext) {
        this.$alert.error('La cámara solo funciona con https (o en localhost)')
        return
      }
      this.iniciandoCamara = true
      try {
        if (!(await QrScanner.hasCamera())) throw new Error('Este equipo no tiene cámara')
        this.scanner = new QrScanner(this.$refs.video, (r) => this.leido(r.data), {
          preferredCamera: this.camaraId,
          highlightScanRegion: true,
          highlightCodeOutline: true,
          maxScansPerSecond: 8,
          returnDetailedScanResult: true,
        })
        await this.scanner.start()
        this.camara = true
        // Los nombres de las camaras se conocen recien con el permiso dado
        this.camaras = await QrScanner.listCameras(true)
      } catch (e) {
        this.cerrarCamara()
        const msg = String((e && e.message) || e || '')
        this.$alert.error(/permission|NotAllowed|denied/i.test(msg)
          ? 'Permite el acceso a la cámara en el navegador'
          : 'No se pudo abrir la cámara: ' + msg)
      } finally {
        this.iniciandoCamara = false
      }
    },
    cambiarCamara(id) {
      if (this.scanner) this.scanner.setCamera(id)
    },
    leido(texto) {
      const codigo = this.limpiar(texto)
      const ahora = Date.now()
      if (!codigo) return
      const seguido = codigo === this.ultimoQr && ahora - this.ultimoQrAt < FUERA_DE_CUADRO_MS
      // Mientras el QR siga a la vista se renueva la marca y no se reenvia
      this.ultimoQr = codigo
      this.ultimoQrAt = ahora
      if (seguido || this.loading) return
      if (navigator.vibrate) navigator.vibrate(120)
      this.codigo = codigo
      this.enviar()
    },
    cerrarCamara() {
      if (this.scanner) {
        this.scanner.stop()
        this.scanner.destroy()
      }
      this.scanner = null
      this.camara = false
    },
  },
}
</script>

<style scoped>
.lector {
  position: relative;
  max-width: 480px;
  margin-left: auto;
  margin-right: auto;
  border-radius: 12px;
  overflow: hidden;
  background: #000;
}
.lector video {
  display: block;
  width: 100%;
}
.lector-estado {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 6px;
  text-align: center;
  color: #fff;
  font-weight: 600;
  background: rgba(0, 0, 0, 0.55);
}
</style>
