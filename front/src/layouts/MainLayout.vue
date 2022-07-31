<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          MultiCines PLAZA
        </q-toolbar-title>

        <div>Usuario: </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
    >
      <q-list bordered class="rounded-borders">
        <q-item-label header class="text-center q-pa-none q-ma-none" style="background: #770050">
          <q-img src="logo.png" width="150px" />
        </q-item-label>
        <q-expansion-item dense exact expand-separator icon="o_home" label="Principal" default-opened to="/" expand-icon="null"/>
        <q-expansion-item expand-separator dense exact icon="o_engineering" label="Siat">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_psychology" label="Cuis" default-opened to="cuis" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_countertops" label="sincronizacion" default-opened to="sincronizacion" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="link" label="Cufd" default-opened to="cufd" expand-icon="null"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_movie_filter" label="Peliculas">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_movie" label="Peliculas" default-opened to="peliculas" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Distribuidores" default-opened to="distribuidores" expand-icon="null"/>
        </q-expansion-item>
        <q-expansion-item dense exact expand-separator icon="o_living" label="Salas" to="salas" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="o_price_change" label="Tarifas" to="tarifas" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="calendar_month" label="Programación" to="programa" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="o_local_activity" label="Venta de boletos" to="sale" expand-icon="null"/>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
export default {
    name: `MainLayout`,
    data() {
      return {
        leftDrawerOpen: false
      }
    },
    methods: {
      toggleLeftDrawer() {
        this.leftDrawerOpen = !this.leftDrawerOpen
      }
    }
  }
</script>
