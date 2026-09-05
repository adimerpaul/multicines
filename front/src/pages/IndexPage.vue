<template>
  <q-page class="dashboard">
    <div class="page-heading">
      <div>
        <div class="eyebrow">ADMINISTRACIÓN / INICIO</div>
        <h1>Panel de control</h1>
        <p>
          Bienvenido, {{ store.user.name || "usuario" }}. Todo listo para una
          nueva función.
        </p>
      </div>
      <div class="date-chip">
        <q-icon name="calendar_today" size="17px" />{{ today }}
      </div>
    </div>

    <section class="welcome-panel">
      <div>
        <span class="welcome-label">TU CINE, EN UN SOLO LUGAR</span>
        <h2>Una gran experiencia<br />empieza con una buena gestión.</h2>
        <p>Administra tus ventas, cartelera y operaciones diarias.</p>
        <div class="welcome-actions">
          <q-btn
            v-if="store.boolboleteria"
            unelevated
            no-caps
            color="white"
            text-color="primary"
            icon="confirmation_number"
            label="Vender entradas"
            to="/sale"
          /><q-btn
            v-if="store.boolprogram"
            outline
            no-caps
            color="white"
            icon="calendar_month"
            label="Ver programación"
            to="/programa"
          />
        </div>
      </div>
      <div class="hero-art" aria-hidden="true">
        <q-icon name="local_movies" />
      </div>
    </section>

    <div class="section-heading">
      <div>
        <h2>Accesos rápidos</h2>
        <p>Las herramientas de tu operación diaria</p>
      </div>
      <span class="module-count">{{ shortcuts.length }} disponibles</span>
    </div>
    <div v-if="shortcuts.length" class="shortcut-grid">
      <router-link
        v-for="item in shortcuts"
        :key="item.to"
        :to="item.to"
        class="shortcut-card"
        ><div class="shortcut-top">
          <span class="shortcut-icon"
            ><q-icon :name="item.icon" size="25px" /></span
          ><q-icon name="north_east" size="19px" color="grey-5" />
        </div>
        <h3>{{ item.label }}</h3>
        <p>{{ item.description }}</p>
        <span class="card-link"
          >Abrir módulo <q-icon name="arrow_forward" /></span
      ></router-link>
    </div>
    <q-banner v-else rounded class="bg-white text-grey-8"
      >No tienes accesos rápidos habilitados. Consulta tus permisos con el
      administrador.</q-banner
    >

    <div class="overview-grid">
      <section class="info-panel">
        <div class="panel-heading">
          <span class="panel-icon"
            ><q-icon name="receipt_long" size="23px"
          /></span>
          <div>
            <h2>Facturación electrónica</h2>
            <p>Comunicación con SIAT</p>
          </div>
        </div>
        <div class="status-line">
          <span class="status-dot" :class="connectionState" /><span
            role="status"
            >{{ mensaje || "Conexión pendiente de verificar" }}</span
          >
        </div>
        <p class="panel-description">
          Comprueba la disponibilidad del servicio de facturación.
        </p>
        <q-btn
          outline
          no-caps
          color="primary"
          icon="sync"
          label="Verificar comunicación"
          :loading="checking"
          @click="verifica"
        />
      </section>
      <section class="info-panel">
        <div class="panel-heading">
          <span class="panel-icon"
            ><q-icon name="space_dashboard" size="23px"
          /></span>
          <div>
            <h2>Gestión y consultas</h2>
            <p>Revisa la información de tu cine</p>
          </div>
        </div>
        <div class="management-links">
          <q-item
            v-for="item in reports"
            :key="item.to"
            clickable
            :to="item.to"
            class="management-link"
            ><q-item-section avatar
              ><q-icon :name="item.icon" color="primary" /></q-item-section
            ><q-item-section>{{ item.label }}</q-item-section
            ><q-item-section side
              ><q-icon name="chevron_right" /></q-item-section
          ></q-item>
        </div>
      </section>
    </div>
    <div class="dashboard-footer">
      <q-icon name="movie" /> Multicines <span>·</span> Sistema de gestión
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from "vue";
import { globalStore } from "stores/globalStore";

export default defineComponent({
  name: "IndexPage",
  data() {
    return {
      store: globalStore(),
      mensaje: "",
      checking: false,
      connectionState: "idle",
    };
  },
  computed: {
    today() {
      return new Intl.DateTimeFormat("es-BO", {
        weekday: "long",
        day: "numeric",
        month: "long",
      }).format(new Date());
    },
    shortcuts() {
      return [
        {
          permission: "boolboleteria",
          label: "Boletería",
          description: "Venta de entradas y selección de asientos.",
          icon: "confirmation_number",
          to: "/sale",
        },
        {
          permission: "boolcandy",
          label: "Candy Bar",
          description: "Productos y ventas para acompañar la función.",
          icon: "local_cafe",
          to: "/candy",
        },
        {
          permission: "boolprogram",
          label: "Programación",
          description: "Organiza las funciones y horarios de tus salas.",
          icon: "calendar_month",
          to: "/programa",
        },
        {
          permission: "boolmovie",
          label: "Películas",
          description: "Administra las películas de tu cartelera.",
          icon: "movie",
          to: "/peliculas",
        },
      ].filter((item) => this.store[item.permission]);
    },
    reports() {
      return [
        {
          permission: "boolcajabol",
          label: "Caja de boletería",
          icon: "point_of_sale",
          to: "/cajaboleteria",
        },
        {
          permission: "boolcajacandy",
          label: "Caja de Candy Bar",
          icon: "storefront",
          to: "/cajacandy",
        },
        {
          permission: "boolreporte",
          label: "Reporte por función",
          icon: "bar_chart",
          to: "/reportefuncion",
        },
        { label: "Facturas", icon: "description", to: "/factura" },
      ].filter((item) => !item.permission || this.store[item.permission]);
    },
  },
  methods: {
    async verifica() {
      this.checking = true;
      try {
        const { data } = await this.$api.get("verificarComunicacion");
        const connected = data === true || data === 1 || data === "1";
        this.connectionState = connected ? "connected" : "error";
        this.mensaje = connected
          ? "Comunicación con SIAT disponible"
          : "No se pudo establecer comunicación con SIAT";
      } catch (error) {
        this.connectionState = "error";
        this.mensaje = "No se pudo verificar la conexión. Intenta nuevamente.";
      } finally {
        this.checking = false;
      }
    },
  },
});
</script>

<style scoped lang="scss">
.dashboard {
  padding: 16px;
  max-width: 1600px;
  margin: 0 auto;
  color: #302b30;
}
.page-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}
.eyebrow {
  font-size: 10px;
  font-weight: 700;
  color: #94878d;
  letter-spacing: 1.5px;
}
h1 {
  font-size: 25px;
  line-height: 1.3;
  font-weight: 750;
  margin: 8px 0;
  letter-spacing: -0.7px;
}
p {
  color: #8a7e85;
  margin: 0;
  font-size: 13px;
  line-height: 1.6;
}
.date-chip {
  display: flex;
  align-items: center;
  gap: 9px;
  background: #fff;
  border: 1px solid #e8e2e5;
  border-radius: 8px;
  padding: 11px 14px;
  font-size: 12px;
  color: #70636a;
  white-space: nowrap;
}
.welcome-panel {
  position: relative;
  overflow: hidden;
  background: linear-gradient(110deg, #770050, #3d153f);
  padding: 20px 24px;
  border-radius: 14px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.welcome-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 2px;
  color: #eac3e6;
}
.welcome-panel h2 {
  font-size: 23px;
  font-weight: 650;
  line-height: 1.35;
  margin: 13px 0;
}
.welcome-panel p {
  color: #e5cce4;
}
.welcome-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 16px;
}
.welcome-actions .q-btn {
  border-radius: 7px;
  font-size: 12px;
  padding: 5px 14px;
}
.hero-art {
  transform: rotate(-15deg);
  padding: 20px 35px;
  border: 1px solid #ffffff15;
  border-radius: 35px;
  background: #ffffff07;
  margin-right: 30px;
}
.hero-art .q-icon {
  font-size: 110px;
  color: #ffffff29;
}
.section-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 20px 0 12px;
}
h2 {
  font-size: 16px;
  font-weight: 700;
  line-height: 1.5;
  margin: 0 0 3px;
}
.module-count {
  font-size: 11px;
  color: #8a7e85;
  background: #eee9ec;
  border-radius: 20px;
  padding: 5px 10px;
}
.shortcut-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}
.shortcut-card {
  display: block;
  background: #fff;
  border: 1px solid #e9e3e6;
  border-radius: 12px;
  padding: 16px;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
}
.shortcut-card:hover {
  transform: translateY(-3px);
  border-color: #c499c0;
  box-shadow: 0 7px 18px #570b480a;
}
.shortcut-card:focus-visible {
  outline: 2px solid #770050;
  outline-offset: 3px;
}
.shortcut-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.shortcut-icon,
.panel-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #f7edf7;
  color: #770050;
  border-radius: 10px;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
}
h3 {
  font-size: 15px;
  font-weight: 700;
  margin: 12px 0 5px;
  line-height: 1.5;
}
.shortcut-card p {
  font-size: 12px;
  min-height: 39px;
}
.card-link {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 11px;
  font-weight: 600;
  color: #770050;
  margin-top: 12px;
}
.overview-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-top: 16px;
}
.info-panel {
  border: 1px solid #e9e3e6;
  border-radius: 12px;
  background: #fff;
  padding: 16px;
}
.panel-heading {
  display: flex;
  gap: 13px;
  align-items: center;
  margin-bottom: 14px;
}
.panel-heading p {
  font-size: 12px;
}
.status-line {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}
.status-dot {
  width: 7px;
  height: 7px;
  background: #aaa0a5;
  border-radius: 50%;
  flex-shrink: 0;
}
.status-dot.connected {
  background: #23865b;
}
.status-dot.error {
  background: #c3293d;
}
.panel-description {
  font-size: 12px;
  margin: 10px 0 17px;
}
.info-panel > .q-btn {
  border-radius: 7px;
  font-size: 12px;
}
.management-link {
  border-top: 1px solid #f1edef;
  min-height: 36px;
  padding: 5px 0;
  font-size: 12px;
}
.management-link .q-item__section--avatar {
  min-width: 34px;
}
.dashboard-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 20px;
  color: #a1959c;
  font-size: 11px;
}
@media (max-width: 1200px) {
  .shortcut-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .hero-art {
    margin-right: 0;
    padding: 16px;
  }
}
@media (max-width: 599px) {
  .dashboard {
    padding: 20px 16px;
  }
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }
  h1 {
    font-size: 25px;
  }
  .welcome-panel {
    padding: 20px;
  }
  .welcome-panel h2 {
    font-size: 23px;
  }
  .hero-art {
    display: none;
  }
  .shortcut-grid,
  .overview-grid {
    grid-template-columns: 1fr;
  }
  .shortcut-card p {
    min-height: 0;
  }
}
</style>
