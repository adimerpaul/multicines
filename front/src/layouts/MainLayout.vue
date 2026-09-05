<template>
  <q-layout view="lHh Lpr lFf">
    <q-header class="app-header">
      <q-toolbar class="app-toolbar">
        <q-btn
          flat
          round
          dense
          icon="menu"
          color="white"
          aria-label="Abrir o cerrar menú"
          @click="toggleLeftDrawer"
        />
        <q-toolbar-title
          ><div class="toolbar-brand">Multicines</div>
          <div class="toolbar-caption">Sistema de gestión</div></q-toolbar-title
        >
        <q-chip
          v-if="store.eventNumber != 0"
          class="invoice-alert"
          color="purple-1"
          text-color="primary"
          icon="warning_amber"
          >{{ store.eventNumber }} facturas no enviadas</q-chip
        >
        <q-btn
          flat
          dense
          no-caps
          class="account-button"
          aria-label="Menú de usuario"
        >
          <q-avatar
            size="28px"
            color="purple-1"
            text-color="primary"
            icon="person"
          />
          <span class="account-name">{{ store.user.name || "Usuario" }}</span
          ><q-icon name="expand_more" size="18px" />
          <q-menu
            ><q-list style="min-width: 220px">
              <q-item
                ><q-item-section
                  ><q-item-label>{{ store.user.name }}</q-item-label
                  ><q-item-label caption>{{
                    store.user.email
                  }}</q-item-label></q-item-section
                ></q-item
              >
              <q-separator />
              <q-item clickable v-close-popup @click="logout"
                ><q-item-section avatar
                  ><q-icon name="logout" color="primary" /></q-item-section
                ><q-item-section>Cerrar sesión</q-item-section></q-item
              >
            </q-list></q-menu
          >
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      :width="268"
      :breakpoint="1024"
      class="app-drawer"
    >
      <div class="drawer-brand">
        <div class="brand-symbol">
          <img
            src="multicines-brand.png"
            alt="Logo de Multicines"
            class="brand-logo"
          />
        </div>
        <div>
          <div class="brand-name">Multicines</div>
          <div class="brand-caption">Sistema de gestión</div>
        </div>
      </div>
      <div class="drawer-section-label">MÓDULOS</div>
      <q-list class="navigation-list">
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_home"
          label="Principal"
          default-opened
          to="/"
          expand-icon="null"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_people"
          label="Usuarios"
          to="usuarios"
          expand-icon="null"
          v-if="store.booluser"
        />
        <q-expansion-item
          expand-separator
          dense
          exact
          icon="o_engineering"
          label="Siat"
          v-if="
            store.boolcuis ||
            store.boolsincr ||
            store.boolcufd ||
            store.boolevento
          "
        >
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_psychology"
            label="Cuis"
            default-opened
            to="cuis"
            expand-icon="null"
            v-if="store.boolcuis"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_countertops"
            label="sincronizacion"
            default-opened
            to="sincronizacion"
            expand-icon="null"
            v-if="store.boolsincr"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="link"
            label="Cufd"
            default-opened
            to="cufd"
            expand-icon="null"
            v-if="store.boolcufd"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="list"
            label="Evento significativo"
            default-opened
            to="eventoSignificativo"
            expand-icon="null"
            v-if="store.boolevento"
          />
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          dense
          exact
          icon="o_movie_filter"
          label="Peliculas"
          v-if="store.boolmovie || store.booldistrib"
        >
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_movie"
            label="Peliculas"
            default-opened
            to="peliculas"
            expand-icon="null"
            v-if="store.boolmovie"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_public"
            label="Peliculas Web"
            default-opened
            to="peliculas-web"
            expand-icon="null"
            v-if="store.boolmovie"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_cast_for_education"
            label="Distribuidores"
            default-opened
            to="distribuidores"
            expand-icon="null"
            v-if="store.booldistrib"
          />
        </q-expansion-item>
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_living"
          label="Salas"
          to="salas"
          expand-icon="null"
          v-if="store.boolsala"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_price_change"
          label="Tarifas"
          to="tarifas"
          expand-icon="null"
          v-if="store.booltarifa"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="format_list_bulleted"
          label="Rubro"
          to="rubro"
          expand-icon="null"
          v-if="store.boolrubro"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="receipt_long"
          label="Producto"
          to="productos"
          expand-icon="null"
          v-if="store.boolproducto"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="calendar_month"
          label="Programación"
          to="programa"
          expand-icon="null"
          v-if="store.boolprogram"
        />
        <!--        <q-expansion-item dense exact expand-separator icon="o_local_activity" label="Venta de boletos" to="sale" expand-icon="null"/>-->
        <q-expansion-item
          expand-separator
          dense
          exact
          icon="o_local_activity"
          label="Venta Boleteria"
          v-if="store.boolboleteria || store.boollistbol"
        >
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_local_activity"
            label="Venta de boletos"
            default-opened
            to="sale"
            expand-icon="null"
            v-if="store.boolboleteria"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_cast_for_education"
            label="Listado de ventas"
            default-opened
            to="listaVenta"
            expand-icon="null"
            v-if="store.boollistbol"
          />
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          dense
          exact
          icon="o_store"
          label="Candy Bar"
          v-if="store.boolcandy || store.boollistcandy"
        >
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_store"
            label="Venta Candy Bar"
            default-opened
            to="candy"
            expand-icon="null"
            v-if="store.boolcandy"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_cast_for_education"
            label="Listado de ventas"
            default-opened
            to="listaVentaCandy"
            expand-icon="null"
            v-if="store.boollistcandy"
          />
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          dense
          exact
          icon="o_store"
          label="Reporte Caja"
          v-if="store.boolcajabol || store.boolcajacandy"
        >
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_store"
            label="Caja Boleteria"
            default-opened
            to="cajaboleteria"
            expand-icon="null"
            v-if="store.boolcajabol"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_store"
            label="Caja Candy"
            default-opened
            to="cajacandy"
            expand-icon="null"
            v-if="store.boolcajacandy"
          />
          <q-expansion-item
            dense
            exact
            :header-inset-level="1"
            expand-separator
            icon="o_movie"
            label="Reporte Funcion"
            default-opened
            to="reportefuncion"
            expand-icon="null"
            v-if="store.boolreporte"
          />
        </q-expansion-item>

        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_home_work"
          label="Factura de Alquiler "
          to="rental"
          expand-icon="null"
          v-if="store.boolalquiler"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_people"
          label="Clientes"
          to="cliente"
          expand-icon="null"
          v-if="store.boolcliente"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_block"
          label="Anulaciones"
          to="anulaciones"
          expand-icon="null"
          v-if="store.boolaprobar || store.boolautorizar"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_book_online"
          label="Cortesia"
          to="cortesia"
          expand-icon="null"
          v-if="store.boolcortesia"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_payments"
          label="Pagos"
          to="pagos"
          expand-icon="null"
          v-if="store.boolpagos"
        />
        <q-expansion-item
          dense
          exact
          expand-separator
          icon="o_description"
          label="factura"
          to="factura"
          expand-icon="null"
        />
        <!--        v-if="store.boolfactura"-->
      </q-list>
      <div class="drawer-footer">
        <div class="footer-caption">Multicines · Administración</div>
        <q-btn
          flat
          no-caps
          align="left"
          icon="logout"
          label="Cerrar sesión"
          class="logout-button full-width"
          @click="logout"
        />
      </div>
    </q-drawer>

    <q-page-container class="app-content">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { globalStore } from "stores/globalStore";

export default {
  name: `MainLayout`,
  data() {
    return {
      leftDrawerOpen: false,
      store: globalStore(),
    };
  },
  created() {
    this.eventSearch();
  },
  methods: {
    logout() {
      this.$q
        .dialog({
          title: "Cerrar sesión",
          message: "¿Está seguro que desea cerrar sesión?",
          cancel: true,
          persistent: true,
        })
        .onOk(() => {
          this.$q.loading.show();
          this.$api.post("logout").then(() => {
            globalStore().user = {};
            localStorage.removeItem("tokenMulti");
            globalStore().isLoggedIn = false;
            this.$router.push("/login");
            this.$q.loading.hide();
            globalStore().isLoggedIn = false;
            globalStore().booluser = false;
            globalStore().boolcuis = false;
            globalStore().boolsincr = false;
            globalStore().boolcufd = false;
            globalStore().boolevento = false;
            globalStore().boolmovie = false;
            globalStore().booldistrib = false;
            globalStore().boolsala = false;
            globalStore().booltarifa = false;
            globalStore().boolrubro = false;
            globalStore().boolproducto = false;
            globalStore().boolprogram = false;
            globalStore().boolboleteria = false;
            globalStore().boollistbol = false;
            globalStore().boolcandy = false;
            globalStore().boollistcandy = false;
            globalStore().boolcajabol = false;
            globalStore().boolcajacandy = false;
            globalStore().boolalquiler = false;
            globalStore().boolcliente = false;
            globalStore().boolcortesia = false;
            globalStore().boolpagos = false;
          });
        })
        .onCancel(() => {});
    },
    eventSearch() {
      this.$api.post("eventSearch").then((res) => {
        // console.log(res.data)
        this.store.eventNumber = res.data;
      });
    },
    toggleLeftDrawer() {
      this.leftDrawerOpen = !this.leftDrawerOpen;
    },
  },
};
</script>

<style scoped lang="scss">
.app-header {
  background: linear-gradient(110deg, #770050 0%, #a83278 60%, #803263 100%);
  color: #fff;
  border-bottom: 1px solid #ffffff20;
}
.app-toolbar {
  min-height: 44px;
  padding: 0 12px;
  gap: 8px;
}
.toolbar-brand {
  font-size: 16px;
  font-weight: 700;
}
.toolbar-caption {
  font-size: 10px;
  color: #f3ddeb;
  margin-top: 0;
  line-height: 1.2;
}
.account-button {
  padding: 2px 8px;
  border-radius: 18px;
  background: #ffffff18;
  color: #fff;
  font-size: 12px;
}
.account-name {
  margin: 0 8px;
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
:deep(.app-drawer) {
  min-height: 100%;
  background: linear-gradient(165deg, #770050 0%, #570b48 45%, #290d2b 100%);
  color: #fff;
}
.drawer-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 8px 6px;
  padding: 6px 8px;
  border: 1px solid #ffffff26;
  border-radius: 12px;
  background: #ffffff09;
}
.brand-symbol {
  background: #302a2a;
  padding: 0;
  border-radius: 10px;
}
.brand-logo {
  display: block;
  width: 32px;
  height: 32px;
  object-fit: contain;
  border-radius: 8px;
}
.brand-name {
  font-size: 16px;
  font-weight: 750;
  letter-spacing: 0.2px;
}
.brand-caption {
  color: #e5c8e3;
  font-size: 12px;
}
.drawer-section-label {
  color: #d6afd3;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.8px;
  padding: 0 16px 6px;
}
.navigation-list {
  padding: 0 8px;
}
.navigation-list :deep(.q-item) {
  min-height: 26px;
  padding-top: 2px;
  padding-bottom: 2px;
  margin: 0;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
}
.navigation-list :deep(.q-item__section--avatar) {
  min-width: 28px;
  padding-right: 8px;
}
.navigation-list :deep(.q-icon) {
  font-size: 18px;
  color: #ead5e8;
}
.navigation-list :deep(.q-item--active),
.navigation-list :deep(.q-router-link--active) {
  background: #ffffff20;
  color: #fff;
  box-shadow: inset 3px 0 #e2a5db;
}
.navigation-list :deep(.q-item--active .q-icon),
.navigation-list :deep(.q-router-link--active .q-icon),
.navigation-list :deep(.q-item:hover),
.navigation-list :deep(.q-item:hover .q-icon),
.navigation-list :deep(.q-item:focus-visible),
.navigation-list :deep(.q-item:focus-visible .q-icon) {
  color: #fff;
}
.navigation-list :deep(.q-expansion-item__content) {
  background: #20082338;
  border-radius: 8px;
}
.navigation-list :deep(.q-expansion-item__border) {
  display: none;
}
.drawer-footer {
  margin: 8px;
}
.footer-caption {
  color: #d0b3ce;
  font-size: 11px;
  padding: 5px;
}
.logout-button {
  border: 1px solid #ffffff26;
  border-radius: 9px;
  background: #ffffff0c;
  padding: 3px 10px;
}
.invoice-alert {
  height: 24px;
  font-size: 11px;
  margin: 0;
}
.app-content {
  background: #f6f5f7;
}
@media (max-width: 599px) {
  .app-toolbar {
    padding: 0 12px;
    gap: 4px;
    min-height: 44px;
  }
  .account-name,
  .invoice-alert {
    display: none;
  }
}
</style>
