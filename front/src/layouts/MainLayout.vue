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

        <div>
          <q-chip  color="red" v-if="store.eventNumber!=0" text-color="white" icon="warning_amber" :label="store.eventNumber+' Facturas no enviadas'" />
          <b>Usuario:</b>{{store.user.name}}
          <q-btn
            flat
            dense
            round

            icon="exit_to_app"
            aria-label="Menu"
            @click="logout"></q-btn>
        </div>
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
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="list" label="Evento significativo" default-opened to="eventoSignificativo" expand-icon="null"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_movie_filter" label="Peliculas">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_movie" label="Peliculas" default-opened to="peliculas" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Distribuidores" default-opened to="distribuidores" expand-icon="null"/>
        </q-expansion-item>
        <q-expansion-item dense exact expand-separator icon="o_living" label="Salas" to="salas" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="o_price_change" label="Tarifas" to="tarifas" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="format_list_bulleted" label="Rubro" to="rubro" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="receipt_long" label="Producto" to="productos" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="calendar_month" label="Programación" to="programa" expand-icon="null"/>
<!--        <q-expansion-item dense exact expand-separator icon="o_local_activity" label="Venta de boletos" to="sale" expand-icon="null"/>-->
        <q-expansion-item expand-separator dense exact icon="o_local_activity" label="Venta Boleteria">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_local_activity" label="Venta de boletos" default-opened to="sale" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Listado de ventas" default-opened to="listaVenta" expand-icon="null"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_store" label="Candy Bar">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_store" label="Venta Candy Bar" default-opened to="candy" expand-icon="null"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Listado de ventas" default-opened to="listaVentaCandy" expand-icon="null"/>
        </q-expansion-item>

        <q-expansion-item dense exact expand-separator icon="o_home_work" label="Factura de Alquiler " to="rental" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="o_local_activity" label="Prevalorada " to="prevalorada" expand-icon="null"/>
        <q-expansion-item dense exact expand-separator icon="o_people" label="Clientes" to="cliente" expand-icon="null"/>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import {globalStore} from "stores/globalStore";

export default {
    name: `MainLayout`,
    data() {
      return {
        leftDrawerOpen: false,
        store:globalStore()
      }
    },
  created() {
      this.eventSearch()
  },
  methods: {
    logout(){
      this.$q.dialog({
        title: 'Cerrar sesión',
        message: '¿Está seguro que desea cerrar sesión?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.post('logout').then(() => {
          globalStore().user={}
          localStorage.removeItem('tokenMulti')
          globalStore().isLoggedIn=false
          this.$router.push('/login')
          this.$q.loading.hide()
        })

      }).onCancel(() => {
      })
    },
      eventSearch(){
        this.$api.post('eventSearch').then(res=>{
          // console.log(res.data)
          this.store.eventNumber=res.data
        })
      },
      toggleLeftDrawer() {
        this.leftDrawerOpen = !this.leftDrawerOpen
      }
    }
  }
</script>
