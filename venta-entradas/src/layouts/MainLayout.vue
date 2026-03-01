<template>
  <q-layout view="hhh lpR fff" class="bg-dark text-white">
    <q-header class="bg-dark text-white" elevated>
      <q-toolbar class="q-px-md">
        <q-toolbar-title class="text-weight-bold">
          Multicines Plaza
        </q-toolbar-title>
        <q-btn v-if="$q.screen.gt.sm" flat no-caps label="Inicio" class="q-mx-xs" />
        <q-btn v-if="$q.screen.gt.sm" flat no-caps label="Peliculas" class="q-mx-xs" />
        <q-btn v-if="$q.screen.gt.sm" flat no-caps label="Proximos" class="q-mx-xs" />
        <q-space />
        <q-input dense dark outlined rounded placeholder="Buscar..." :style="{ width: $q.screen.lt.md ? '150px' : '220px' }">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-toolbar>
    </q-header>
    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer class="footer-shell text-white">
      <div class="footer-top-accent" />
      <q-card flat class="footer-card bg-grey-10 text-white">
        <q-card-section class="row q-col-gutter-lg">
          <div class="col-12 col-md-3">
            <div class="text-h6 text-weight-bold q-mb-md">Multicines Plaza</div>
            <q-list dense dark class="footer-links">
              <q-item clickable><q-item-section>Inicio</q-item-section></q-item>
              <q-item clickable><q-item-section>Peliculas</q-item-section></q-item>
              <q-item clickable><q-item-section>Proximos</q-item-section></q-item>
              <q-item clickable><q-item-section>Promociones</q-item-section></q-item>
            </q-list>
          </div>

          <div class="col-12 col-md-3">
            <div class="text-subtitle1 text-weight-bold q-mb-md">Informacion</div>
            <q-list dense dark class="footer-links">
              <q-item clickable><q-item-section>Sobre nosotros</q-item-section></q-item>
              <q-item clickable><q-item-section>Politica de privacidad</q-item-section></q-item>
              <q-item clickable><q-item-section>Terminos y condiciones</q-item-section></q-item>
              <q-item clickable><q-item-section>Preguntas frecuentes</q-item-section></q-item>
            </q-list>
          </div>

          <div class="col-12 col-md-3">
            <div class="text-subtitle1 text-weight-bold q-mb-md">Contacto</div>
            <div class="text-body2 text-grey-4 q-mb-sm">Atencion al cliente:</div>
            <div class="text-body1 q-mb-md">+591 4 5820000</div>
            <div class="text-body2 text-grey-4">Email:</div>
            <div class="text-body1">contacto@multicinesplaza.com</div>
          </div>

          <div class="col-12 col-md-3">
            <div class="text-subtitle1 text-weight-bold q-mb-md">Siguenos</div>
            <div class="row q-gutter-sm q-mb-md">
              <q-btn round outline color="white" icon="fa-brands fa-facebook-f" />
              <q-btn round outline color="white" icon="fa-brands fa-instagram" />
              <q-btn round outline color="white" icon="fa-brands fa-x-twitter" />
              <q-btn round outline color="white" icon="fa-brands fa-youtube" />
            </div>
            <q-badge color="pink-6" text-color="white" class="q-pa-sm">
              Venta online y reserva de asientos
            </q-badge>
          </div>
        </q-card-section>

        <q-separator dark />

        <q-card-section class="text-center text-caption text-grey-5">
          © {{ new Date().getFullYear() }} Multicines Plaza. Todos los derechos reservados.
        </q-card-section>
      </q-card>
    </q-footer>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'MainLayout'
})
</script>

<style scoped>
.footer-shell {
  background: transparent;
}
.footer-top-accent {
  height: 6px;
  background: linear-gradient(90deg, #ff2f6d, #e91e63, #ff2f6d);
}
.footer-card {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.footer-links .q-item {
  min-height: 30px;
  padding: 2px 0;
  color: #e0e0e0;
}
.footer-links .q-item:hover {
  color: #ff4f85;
}
</style>
