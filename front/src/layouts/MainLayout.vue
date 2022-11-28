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
        <q-expansion-item dense exact expand-separator icon="o_people" label="Usuarios" to="usuarios" expand-icon="null" v-if="store.booluser"/>
        <q-expansion-item expand-separator dense exact icon="o_engineering" label="Siat" v-if="store.boolcuis||store.boolsincr||store.boolcufd||store.boolevento">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_psychology" label="Cuis" default-opened to="cuis" expand-icon="null" v-if="store.boolcuis"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_countertops" label="sincronizacion" default-opened to="sincronizacion" expand-icon="null" v-if="store.boolsincr"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="link" label="Cufd" default-opened to="cufd" expand-icon="null" v-if="store.boolcufd"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="list" label="Evento significativo" default-opened to="eventoSignificativo" expand-icon="null" v-if="store.boolevento"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_movie_filter" label="Peliculas" v-if="store.boolmovie||store.booldistrib">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_movie" label="Peliculas" default-opened to="peliculas" expand-icon="null" v-if="store.boolmovie"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Distribuidores" default-opened to="distribuidores" expand-icon="null" v-if="store.booldistrib"/>
        </q-expansion-item>
        <q-expansion-item dense exact expand-separator icon="o_living" label="Salas" to="salas" expand-icon="null" v-if="store.boolsala"/>
        <q-expansion-item dense exact expand-separator icon="o_price_change" label="Tarifas" to="tarifas" expand-icon="null" v-if="store.booltarifa"/>
        <q-expansion-item dense exact expand-separator icon="format_list_bulleted" label="Rubro" to="rubro" expand-icon="null" v-if="store.boolrubro"/>
        <q-expansion-item dense exact expand-separator icon="receipt_long" label="Producto" to="productos" expand-icon="null" v-if="store.boolproducto"/>
        <q-expansion-item dense exact expand-separator icon="calendar_month" label="Programación" to="programa" expand-icon="null" v-if="store.boolprogram"/>
<!--        <q-expansion-item dense exact expand-separator icon="o_local_activity" label="Venta de boletos" to="sale" expand-icon="null"/>-->
        <q-expansion-item expand-separator dense exact icon="o_local_activity" label="Venta Boleteria" v-if="store.boolboleteria||store.boollistbol">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_local_activity" label="Venta de boletos" default-opened to="sale" expand-icon="null" v-if="store.boolboleteria"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Listado de ventas" default-opened to="listaVenta" expand-icon="null" v-if="store.boollistbol"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_store" label="Candy Bar" v-if="store.boolcandy||store.boollistcandy">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_store" label="Venta Candy Bar" default-opened to="candy" expand-icon="null" v-if="store.boolcandy"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_cast_for_education" label="Listado de ventas" default-opened to="listaVentaCandy" expand-icon="null" v-if="store.boollistcandy"/>
        </q-expansion-item>
        <q-expansion-item expand-separator dense exact icon="o_store" label="Reporte Caja" v-if="store.boolcajabol||store.boolcajacandy">
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_store" label="Caja Boleteria" default-opened to="cajaboleteria" expand-icon="null" v-if="store.boolcajabol"/>
          <q-expansion-item dense exact :header-inset-level="1" expand-separator icon="o_store" label="Caja Candy" default-opened to="cajacandy" expand-icon="null" v-if="store.boolcajacandy"/>

        </q-expansion-item>

        <q-expansion-item dense exact expand-separator icon="o_home_work" label="Factura de Alquiler " to="rental" expand-icon="null" v-if="store.boolalquiler"/>
        <q-expansion-item dense exact expand-separator icon="o_people" label="Clientes" to="cliente" expand-icon="null" v-if="store.boolcliente"/>
        <q-expansion-item dense exact expand-separator icon="o_book_online" label="Cortesia" to="cortesia" expand-icon="null" v-if="store.boolcortesia"/>
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
          globalStore().isLoggedIn=false
          globalStore().booluser=false
          globalStore().boolcuis=false
          globalStore().boolsincr=false
          globalStore().boolcufd=false
          globalStore().boolevento=false
          globalStore().boolmovie=false
          globalStore().booldistrib=false
          globalStore().boolsala=false
          globalStore().booltarifa=false
          globalStore().boolrubro=false
          globalStore().boolproducto=false
          globalStore().boolprogram=false
          globalStore().boolboleteria=false
          globalStore().boollistbol=false
          globalStore().boolcandy=false
          globalStore().boollistcandy=false
          globalStore().boolcajabol=false
          globalStore().boolcajacandy=false
          globalStore().boolalquiler=false
          globalStore().boolcliente=false
          globalStore().boolcortesia=false
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
