<template>
  <q-page class="q-pa-sm factura-page">
    <div class="row q-col-gutter-xs q-mb-xs factura-toolbar">
      <q-select
        class="col-6 col-md-2"
        outlined
        dense
        v-model="mes"
        :options="meses"
        label="Mes"
        emit-value
        map-options
        :disable="loading || importing"
        @update:model-value="buscarFacturas"
      />
      <q-select
        class="col-6 col-md-2"
        outlined
        dense
        v-model="anio"
        :options="anios"
        label="Año"
        :disable="loading || importing"
        @update:model-value="buscarFacturas"
      />
      <div class="col-12 col-md-2">
        <q-btn
          color="primary"
          label="Buscar"
          icon="search"
          no-caps
          :loading="loading"
          :disable="importing"
          @click="buscarFacturas"
        />
      </div>
      <q-file
        class="col-12 col-md-4"
        outlined
        dense
        v-model="archivo"
        label="Reporte de ventas (.zip o .xlsx)"
        accept=".zip,.xlsx"
        :disable="importing"
        hide-bottom-space
      >
        <template #prepend><q-icon name="attach_file" /></template>
      </q-file>
      <div class="col-12 col-md-2">
        <q-btn
          color="primary"
          label="Importar"
          icon="cloud_upload"
          no-caps
          :loading="importing"
          :disable="!archivo || loading"
          @click="subirFactura"
        />
      </div>
    </div>
    <q-banner v-if="importing" class="bg-blue-1 q-mb-md" rounded>
      {{
        uploadProgress < 100
          ? `Subiendo archivo: ${uploadProgress}%`
          : "Procesando y guardando facturas…"
      }}
      · {{ tiempo }} s
      <q-linear-progress
        class="q-mt-sm"
        :indeterminate="uploadProgress === 100"
        :value="uploadProgress / 100"
      />
    </q-banner>
    <q-banner v-if="resultado" class="bg-green-1 q-mb-md" rounded>{{
      resultado
    }}</q-banner>
    <q-card v-if="resumen" flat bordered class="q-mb-sm factura-note">
      <q-card-section class="q-pa-sm">
        <div class="row items-center q-mb-xs">
          <div>
            <div class="text-subtitle1 text-weight-bold">
              {{ meses.find((item) => item.value === mes)?.label }} {{ anio }}
            </div>
            <div class="text-subtitle2">Resumen de ventas · sin anuladas</div>
          </div>
          <q-space /><q-btn
            class="no-print"
            flat
            dense
            icon="print"
            label="Imprimir resumen"
            no-caps
            @click="printSummary"
          />
        </div>
        <div class="text-caption text-grey-8">
          Formato de la nota. Parqueo y total de Impuestos son referencias
          externas editables; no crean facturas ni vínculos.
        </div>
        <div class="row q-col-gutter-sm q-mt-xs no-print">
          <q-input
            class="col-12 col-sm-6"
            outlined
            dense
            type="number"
            min="0"
            step="0.01"
            :model-value="referenciaMes.parqueo"
            label="Parqueo · nota (Bs)"
            @update:model-value="updateReference('parqueo', $event)"
          />
          <q-input
            class="col-12 col-sm-6"
            outlined
            dense
            type="number"
            min="0"
            step="0.01"
            :model-value="referenciaMes.siat"
            label="Impuestos · nota (Bs)"
            @update:model-value="updateReference('siat', $event)"
          />
        </div>
        <q-markup-table dense flat class="q-mt-xs note-table">
          <thead>
            <tr>
              <th class="text-left">Concepto</th>
              <th class="text-right">Monto (Bs)</th>
              <th class="text-left">Fuente</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in noteRows" :key="item.label">
              <td>{{ item.label }}</td>
              <td class="text-right">{{ money(item.amount) }}</td>
              <td>{{ item.source }}</td>
            </tr>
            <tr class="bg-blue-1 text-weight-bold">
              <td>Según sistema + parqueo externo</td>
              <td class="text-right">{{ money(totalConParqueo) }}</td>
              <td>
                {{
                  referenciaMes.parqueo == null
                    ? "Ingrese el total externo de parqueo"
                    : "Suma de las cuatro líneas"
                }}
              </td>
            </tr>
            <tr class="text-weight-bold">
              <td>Según Impuestos · referencia externa</td>
              <td class="text-right">{{ money(referenciaMes.siat) }}</td>
              <td>Dato de la nota; pendiente de respaldo por CUF</td>
            </tr>
            <tr class="bg-amber-1 text-weight-bold">
              <td>Diferencia de la nota (Impuestos − sistema)</td>
              <td class="text-right">{{ money(diferenciaNota) }}</td>
              <td>Positivo: Impuestos supera al sistema</td>
            </tr>
            <tr>
              <td>Archivo SIAT importado · sin anuladas</td>
              <td class="text-right">
                {{ money(resumen.siat.montoNoAnulado) }}
              </td>
              <td>
                {{ resumen.siat.cantidad - resumen.siat.anuladas }} facturas no
                anuladas
              </td>
            </tr>
            <tr>
              <td>Por justificar: referencia Impuestos − archivo SIAT</td>
              <td class="text-right text-weight-bold">
                {{ money(brechaArchivo) }}
              </td>
              <td>Diferencia entre las fuentes disponibles</td>
            </tr>
          </tbody>
        </q-markup-table>
        <div class="text-caption q-mt-xs">
          Parqueo identificado en el archivo:
          <strong
            >{{ resumen.parqueoSiat.cantidad }} facturas · Bs
            {{ money(resumen.parqueoSiat.montoNoAnulado) }} sin anuladas</strong
          >. Regla: «SIN NOMBRE», Bs 10 y sin coincidencia local. No se suma
          otra vez al total SIAT.
          <q-btn
            class="no-print"
            flat
            dense
            no-caps
            size="sm"
            label="Ver parqueos"
            @click="showParking"
          />
        </div>
        <div
          v-if="referenciaMes.siat != null && brechaArchivo !== 0"
          class="text-caption text-negative"
        >
          La cifra de Impuestos de la nota no coincide con el Excel cargado. La
          diferencia de la nota no demuestra por sí sola qué factura está mal:
          revise el respaldo y los CUF.
        </div>
      </q-card-section>
    </q-card>
    <q-expansion-item
      v-model="showReconciliation"
      class="no-print"
      icon="fact_check"
      label="Revisar facturas y justificar diferencias"
      caption="Totales por origen, filtros y comparación de CUF"
      header-class="bg-blue-1"
      expand-separator
    >
      <div v-if="resumen" class="row q-col-gutter-sm q-mb-sm q-mt-sm">
        <div
          v-for="card in summaryCards"
          :key="card.label"
          class="col-12 col-sm-6 col-lg"
        >
          <q-card flat bordered>
            <q-card-section class="q-pa-sm">
              <div class="text-caption text-grey-8">
                {{ card.label }} · mes seleccionado
              </div>
              <div class="text-h6">
                {{ card.data.cantidad.toLocaleString("es-BO") }}
                <span class="text-caption">facturas</span>
              </div>
              <div class="text-weight-bold">
                Bs {{ money(card.data.monto) }}
              </div>
              <div class="text-caption text-negative">
                Anuladas: {{ card.data.anuladas }} · Bs
                {{ money(card.data.montoAnulado) }}
              </div>
              <div class="text-caption">
                Sin anular: Bs {{ money(card.data.montoNoAnulado) }}
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
      <div v-if="resumen" class="text-caption q-mb-sm">
        Diferencia de totales (local − SIAT):
        <strong
          >Bs {{ money(resumen.local.monto - resumen.siat.monto) }}</strong
        >
        · Sin anuladas:
        <strong
          >Bs
          {{
            money(resumen.local.montoNoAnulado - resumen.siat.montoNoAnulado)
          }}</strong
        >
      </div>
      <q-linear-progress v-if="loading" indeterminate class="q-mb-sm" />
      <div v-if="resumen" class="row q-gutter-xs q-mb-sm">
        <q-chip dense color="green-1" clickable @click="setLink('vinculada')"
          >Vinculadas: {{ resumen.vinculada }}</q-chip
        >
        <q-chip dense color="orange-1" clickable @click="setLink('solo_siat')"
          >Solo SIAT: {{ resumen.solo_siat }}</q-chip
        >
        <q-chip dense color="red-1" clickable @click="setLink('falta_siat')"
          >Faltan en archivo SIAT: {{ resumen.falta_siat }}</q-chip
        >
        <q-chip dense clickable @click="setDifference('diferenciaMonto')"
          >Monto diferente: {{ resumen.diferenciaMonto }}</q-chip
        >
        <q-chip dense clickable @click="setDifference('diferenciaEstado')"
          >Estado diferente: {{ resumen.diferenciaEstado }}</q-chip
        >
        <q-chip dense clickable @click="setDifference('diferenciaFecha')"
          >Fecha diferente: {{ resumen.diferenciaFecha }}</q-chip
        >
        <q-chip dense clickable @click="setDifference('duplicado')"
          >CUF repetido: {{ resumen.duplicado }}</q-chip
        >
      </div>
      <div class="text-caption text-grey-8 q-mb-sm">
        Comparación con el archivo SIAT importado. Totales del mes,
        independientes de los filtros; incluyen anuladas. Se buscan
        coincidencias de CUF en todas las fechas. Los recibos sin factura no se
        incluyen.
      </div>
      <div class="row q-col-gutter-xs q-mb-xs factura-filters">
        <q-select
          class="col-12 col-sm-3"
          outlined
          dense
          v-model="vinculo"
          :options="linkOptions"
          emit-value
          map-options
          label="Vinculación"
          @update:model-value="buscarFacturas"
        />
        <q-select
          class="col-12 col-sm-3"
          outlined
          dense
          v-model="origen"
          :options="originOptions"
          emit-value
          map-options
          label="Origen local"
          @update:model-value="buscarFacturas"
        />
        <q-select
          class="col-12 col-sm-3"
          outlined
          dense
          v-model="diferencia"
          :options="differenceOptions"
          emit-value
          map-options
          label="Diferencias"
          @update:model-value="buscarFacturas"
        />
        <div class="col-12 col-sm-3 flex items-center">
          <q-checkbox
            dense
            v-model="anuladas"
            label="Anuladas en algún lado"
            @update:model-value="buscarFacturas"
          />
          <q-btn dense flat no-caps label="Limpiar" @click="clearFilters" />
        </div>
      </div>
      <q-table
        :title="`${pagination.rowsNumber.toLocaleString(
          'es-BO'
        )} CUF / registros encontrados`"
        class="facturas-table"
        dense
        flat
        bordered
        :visible-columns="visibleColumns"
        row-key="id"
        :rows="facturas"
        :columns="columns"
        :loading="loading"
        v-model:pagination="pagination"
        :rows-per-page-options="[25, 50, 100]"
        :filter="filter"
        @request="getYearMonthFacturas"
      >
        <template #body-cell-vinculo="props">
          <q-td :props="props"
            ><q-badge
              :color="
                props.row.vinculo === 'vinculada' ? 'positive' : 'warning'
              "
              >{{ linkLabel(props.row.vinculo) }}</q-badge
            ></q-td
          >
        </template>
        <template #body-cell-observaciones="props">
          <q-td :props="props" class="text-negative">{{
            props.value || "—"
          }}</q-td>
        </template>
        <template #body-cell-detalle="props">
          <q-td :props="props"
            ><q-btn
              flat
              dense
              size="sm"
              icon="compare_arrows"
              label="Revisar CUF"
              no-caps
              @click="detalle = props.row"
          /></q-td>
        </template>
        <template #top-right>
          <q-select
            v-model="visibleColumns"
            :options="columns"
            option-value="name"
            option-label="label"
            multiple
            emit-value
            map-options
            dense
            outlined
            options-dense
            display-value="Columnas"
            style="min-width: 110px"
            class="q-mr-sm"
          />
          <q-input
            v-model="filter"
            outlined
            dense
            debounce="400"
            placeholder="Factura, NIT, nombre o estado"
            :disable="importing"
            clearable
            ><template #append><q-icon name="search" /></template
          ></q-input>
        </template>
      </q-table>
      <q-dialog :model-value="!!detalle" @update:model-value="detalle = null">
        <q-card v-if="detalle" style="width: 1100px; max-width: 96vw">
          <q-card-section class="row items-center q-pa-sm">
            <div class="text-h6">Revisión por CUF</div>
            <q-space />
            <q-btn
              v-if="siatUrl(detalle.cuf, detalle.nFactura)"
              class="q-mr-sm"
              type="a"
              target="_blank"
              color="info"
              dense
              no-caps
              icon="print"
              label="Imp Impuestos"
              :href="siatUrl(detalle.cuf, detalle.nFactura)"
            >
              <q-tooltip
                >Abre la consulta de Impuestos (CUF + Nº factura + NIT
                {{ cine.nit }}) para imprimir</q-tooltip
              >
            </q-btn>
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section class="q-pa-sm">
            <div style="overflow-wrap: anywhere" class="text-weight-medium">
              {{ detalle.cuf || "Sin CUF" }}
            </div>
            <div class="text-negative q-my-sm">{{ detalle.observaciones }}</div>
            <div class="text-subtitle2">
              SIAT · {{ detalle.siat.length }} registro(s)
            </div>
            <q-markup-table flat bordered dense wrap-cells>
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Nº factura</th>
                  <th>Monto (Bs)</th>
                  <th>Estado SIAT</th>
                  <th>CUF SIAT</th>
                  <th>Impuestos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in detalle.siat" :key="item.id">
                  <td>{{ item.fecha }}</td>
                  <td>{{ item.nFactura }}</td>
                  <td>{{ money(item.importe) }}</td>
                  <td>{{ item.estado }}</td>
                  <td style="word-break: break-all">{{ item.cuf }}</td>
                  <td>
                    <q-btn
                      v-if="siatUrl(item.cuf, item.nFactura)"
                      type="a"
                      target="_blank"
                      flat
                      dense
                      size="sm"
                      no-caps
                      color="info"
                      icon="print"
                      label="Imp Impuestos"
                      :href="siatUrl(item.cuf, item.nFactura)"
                    />
                    <span v-else class="text-grey-7">—</span>
                  </td>
                </tr>
                <tr v-if="!detalle.siat.length">
                  <td colspan="6">
                    No se encontró en los archivos SIAT importados.
                  </td>
                </tr>
              </tbody>
            </q-markup-table>
            <div class="text-subtitle2 q-mt-md">
              Sistema local · {{ detalle.ventas.length }} registro(s)
            </div>
            <q-markup-table flat bordered dense wrap-cells>
              <thead>
                <tr>
                  <th>Origen / ID</th>
                  <th>Fecha</th>
                  <th>Nº factura</th>
                  <th>Monto (Bs)</th>
                  <th>Estado local</th>
                  <th>Enviado</th>
                  <th>CUF local</th>
                  <th>Impuestos</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in detalle.ventas"
                  :key="`${item.tabla}:${item.id}`"
                >
                  <td>
                    {{ item.origen }} #{{ item.id }}
                    <span v-if="item.deleted_at" class="text-negative"
                      >Eliminada</span
                    >
                  </td>
                  <td>{{ item.fechaEmision }}</td>
                  <td>{{ item.numeroFactura }}</td>
                  <td>{{ money(item.montoTotal) }}</td>
                  <td>{{ Number(item.siatAnulado) ? "ANULADA" : "VALIDA" }}</td>
                  <td>{{ Number(item.siatEnviado) ? "Sí" : "Pendiente" }}</td>
                  <td style="word-break: break-all">
                    {{ item.cuf || "Sin CUF" }}
                  </td>
                  <td>
                    <q-btn
                      v-if="siatUrl(item.cuf, item.numeroFactura)"
                      type="a"
                      target="_blank"
                      flat
                      dense
                      size="sm"
                      no-caps
                      color="info"
                      icon="print"
                      label="Imp Impuestos"
                      :href="siatUrl(item.cuf, item.numeroFactura)"
                    />
                    <span v-else class="text-grey-7">—</span>
                  </td>
                </tr>
                <tr v-if="!detalle.ventas.length">
                  <td colspan="8">
                    No se encontró en boletería, candy ni alquileres.
                  </td>
                </tr>
              </tbody>
            </q-markup-table>
            <div class="text-caption q-mt-sm">
              «Imp Impuestos» abre
              {{ siatBase }}consulta/QR con el CUF, el Nº de factura y el NIT
              {{ cine.nit || "—" }} del emisor, para verificar e imprimir desde
              el portal de Impuestos.
            </div>
            <div class="text-caption q-mt-sm">
              Diferencia = monto local − monto SIAT. Si un CUF se repite, se
              muestran las sumas de cada lado y se requiere revisión individual.
            </div>
          </q-card-section>
        </q-card>
      </q-dialog>
    </q-expansion-item>
  </q-page>
</template>

<script>
import { globalStore } from "stores/globalStore";

export default {
  name: "FacturaPage",
  data() {
    const today = new Date();
    const previousMonth = new Date(
      today.getFullYear(),
      today.getMonth() - 1,
      1
    );
    return {
      filter: "",
      cineLocal: null,
      resumen: null,
      showReconciliation: false,
      referencias: { "2026-8": { parqueo: 11565, siat: 1210140 } },
      detalle: null,
      requestId: 0,
      vinculo: "",
      origen: "",
      diferencia: "",
      anuladas: false,
      linkOptions: [
        { label: "Todas", value: "" },
        { label: "Vinculadas", value: "vinculada" },
        { label: "Solo SIAT / sin venta local", value: "solo_siat" },
        { label: "Local / falta en archivo SIAT", value: "falta_siat" },
      ],
      originOptions: ["", "BOLETERIA", "CANDY", "ALQUILER", "PARQUEO"].map(
        (value) => ({
          label: value || "Todos",
          value,
        })
      ),
      differenceOptions: [
        { label: "Todas", value: "" },
        { label: "Monto diferente", value: "diferenciaMonto" },
        { label: "Estado diferente", value: "diferenciaEstado" },
        { label: "Fecha diferente", value: "diferenciaFecha" },
        { label: "CUF repetido", value: "duplicado" },
      ],
      facturas: [],
      archivo: null,
      anio: previousMonth.getFullYear(),
      mes: previousMonth.getMonth() + 1,
      anios: Array.from(
        { length: new Date().getFullYear() - 2018 },
        (_, i) => 2019 + i
      ),
      meses: [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre",
      ].map((label, i) => ({ label, value: i + 1 })),
      loading: false,
      importing: false,
      uploadProgress: 0,
      tiempo: 0,
      timer: null,
      resultado: "",
      pagination: { page: 1, rowsPerPage: 50, rowsNumber: 0 },
      visibleColumns: [
        "fecha",
        "origen",
        "vinculo",
        "importe",
        "montoLocal",
        "diferencia",
        "estado",
        "estadoLocal",
        "observaciones",
        "detalle",
      ],
      columns: [
        ["fecha", "Fecha SIAT / local"],
        ["origen", "Origen"],
        ["vinculo", "Vinculación"],
        ["importe", "Monto SIAT"],
        ["montoLocal", "Monto local"],
        ["diferencia", "Diferencia Bs"],
        ["estado", "Estado SIAT"],
        ["estadoLocal", "Estado local"],
        ["observaciones", "Revisión"],
        ["nFactura", "Nº factura"],
        ["nit", "NIT / CI"],
        ["nombre", "Razón social"],
        ["cuf", "CUF"],
        ["detalle", "Detalle"],
      ].map(([field, label]) => ({
        name: field,
        field,
        label,
        align: ["importe", "montoLocal", "diferencia"].includes(field)
          ? "right"
          : "left",
        format: ["importe", "montoLocal", "diferencia"].includes(field)
          ? (value) => this.money(value)
          : undefined,
      })),
    };
  },
  computed: {
    cine() {
      return this.cineLocal || globalStore().cine || {};
    },
    siatBase() {
      const url = this.cine.url2 || "https://siat.impuestos.gob.bo/";
      return url.endsWith("/") ? url : `${url}/`;
    },
    referenciaMes() {
      return (
        this.referencias[`${this.anio}-${this.mes}`] || {
          parqueo: null,
          siat: null,
        }
      );
    },
    noteRows() {
      if (!this.resumen) return [];
      return [
        {
          label: "TV Candy",
          amount: this.resumen.origenes.CANDY.montoNoAnulado,
          source: "Sistema local",
        },
        {
          label: "TV Boletería",
          amount: this.resumen.origenes.BOLETERIA.montoNoAnulado,
          source: "Sistema local",
        },
        {
          label: "TV Alquileres",
          amount: this.resumen.origenes.ALQUILER.montoNoAnulado,
          source: "Sistema local",
        },
        {
          label: "TV Parqueo",
          amount: this.referenciaMes.parqueo,
          source: "Referencia externa; no cargada como venta local",
        },
      ];
    },
    totalConParqueo() {
      if (!this.resumen || this.referenciaMes.parqueo == null) return null;
      return (
        (Math.round(this.resumen.local.montoNoAnulado * 100) +
          Math.round(this.referenciaMes.parqueo * 100)) /
        100
      );
    },
    diferenciaNota() {
      return this.totalConParqueo == null || this.referenciaMes.siat == null
        ? null
        : Math.round((this.referenciaMes.siat - this.totalConParqueo) * 100) /
            100;
    },
    brechaArchivo() {
      return !this.resumen || this.referenciaMes.siat == null
        ? null
        : Math.round(
            (this.referenciaMes.siat - this.resumen.siat.montoNoAnulado) * 100
          ) / 100;
    },
    summaryCards() {
      if (!this.resumen) return [];
      return [
        { label: "Archivo SIAT", data: this.resumen.siat },
        { label: "Sistema local", data: this.resumen.local },
        ...Object.entries(this.resumen.origenes).map(([label, data]) => ({
          label,
          data,
        })),
      ];
    },
  },
  mounted() {
    this.buscarFacturas();
    this.cargarCine();
  },
  beforeUnmount() {
    clearInterval(this.timer);
  },
  methods: {
    async cargarCine() {
      if (this.cine.nit) return;
      try {
        const { data } = await this.$api.get("datocine");
        this.cineLocal = typeof data === "string" ? JSON.parse(data) : data;
      } catch (error) {
        this.cineLocal = null;
      }
    },
    siatUrl(cuf, numeroFactura) {
      const nit = this.cine.nit;
      if (!nit || !cuf || numeroFactura == null || numeroFactura === "")
        return null;
      return `${this.siatBase}consulta/QR?nit=${encodeURIComponent(
        nit
      )}&cuf=${encodeURIComponent(
        String(cuf).trim()
      )}&numero=${encodeURIComponent(numeroFactura)}&t=2`;
    },
    printSummary() {
      window.print();
    },
    updateReference(field, value) {
      const key = `${this.anio}-${this.mes}`;
      const number = value === "" || value == null ? null : Number(value);
      this.referencias[key] = {
        ...this.referenciaMes,
        [field]:
          number !== null && Number.isFinite(number) && number >= 0
            ? number
            : null,
      };
    },
    showParking() {
      this.showReconciliation = true;
      this.vinculo = "";
      this.diferencia = "";
      this.anuladas = false;
      this.filter = "";
      this.origen = "PARQUEO";
      this.buscarFacturas();
    },
    money(value) {
      return value == null
        ? "—"
        : Number(value).toLocaleString("es-BO", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
    },
    linkLabel(value) {
      return (
        this.linkOptions.find((option) => option.value === value)?.label ||
        value
      );
    },
    setLink(value) {
      this.vinculo = value;
      this.diferencia = "";
      this.buscarFacturas();
    },
    setDifference(value) {
      this.diferencia = value;
      this.vinculo = "";
      this.buscarFacturas();
    },
    clearFilters() {
      this.vinculo = "";
      this.origen = "";
      this.diferencia = "";
      this.anuladas = false;
      this.filter = "";
      this.buscarFacturas();
    },
    errorMessage(error) {
      const data = error.response?.data;
      return (
        Object.values(data?.errors || {})
          .flat()
          .join(" ") ||
        data?.message ||
        "No se pudo completar la operación. Intente nuevamente."
      );
    },
    buscarFacturas() {
      return this.getYearMonthFacturas({
        pagination: { ...this.pagination, page: 1 },
      });
    },
    async getYearMonthFacturas({ pagination }) {
      const requestId = ++this.requestId;
      this.loading = true;
      try {
        const { data } = await this.$api.post("facturasConciliacion", {
          anio: this.anio,
          mes: this.mes,
          page: pagination.page,
          per_page: pagination.rowsPerPage,
          filter: this.filter || "",
          origen: this.origen,
          vinculo: this.vinculo,
          diferencia: this.diferencia,
          anuladas: this.anuladas,
        });
        if (requestId !== this.requestId) return;
        this.resumen = data.resumen;
        this.facturas = data.data;
        this.pagination = { ...pagination, rowsNumber: data.total };
      } catch (error) {
        this.$q.notify({
          color: "negative",
          message: this.errorMessage(error),
        });
      } finally {
        if (requestId === this.requestId) this.loading = false;
      }
    },
    async subirFactura() {
      if (!this.archivo || this.importing) return;
      this.importing = true;
      this.resultado = "";
      this.uploadProgress = 0;
      this.tiempo = 0;
      this.timer = setInterval(() => {
        this.tiempo++;
      }, 1000);
      const formData = new FormData();
      formData.append("archivo", this.archivo);
      try {
        const { data } = await this.$api.post("import", formData, {
          onUploadProgress: ({ loaded, total }) => {
            this.uploadProgress = total
              ? Math.round((loaded * 100) / total)
              : 0;
          },
        });
        this.resultado = `${data.total} facturas procesadas: ${
          data.insertadas
        } nuevas y ${data.omitidas} omitidas por CUF existente en ${
          data.segundos
        } s. Meses: ${data.meses.join(", ")}.`;
        const [year, month] = data.meses[data.meses.length - 1]
          .split("-")
          .map(Number);
        this.anio = year;
        this.mes = month;
        if (!this.anios.includes(year)) this.anios.push(year);
        this.filter = "";
        this.archivo = null;
        await this.buscarFacturas();
      } catch (error) {
        this.$q.notify({
          color: "negative",
          message: this.errorMessage(error),
        });
      } finally {
        clearInterval(this.timer);
        this.importing = false;
      }
    },
  },
};
</script>

<style scoped>
.factura-toolbar {
  align-items: center;
}
.factura-toolbar > :nth-child(1) {
  width: 135px;
}
.factura-toolbar > :nth-child(2) {
  width: 100px;
}
.factura-toolbar > :nth-child(3),
.factura-toolbar > :nth-child(5) {
  width: auto;
}
.factura-toolbar > :nth-child(4) {
  flex: 1;
  min-width: 230px;
}
.factura-page :deep(.q-field--dense .q-field__control),
.factura-page :deep(.q-field--dense .q-field__marginal) {
  height: 32px;
  min-height: 32px;
}
.factura-page :deep(.q-field--dense .q-field__label) {
  top: 6px;
  font-size: 12px;
}
.factura-page :deep(.q-field--dense .q-field__native),
.factura-page :deep(.q-field--dense .q-field__input) {
  min-height: 30px;
  font-size: 12px;
  padding-bottom: 2px;
}
.factura-page :deep(.q-field--dense.q-field--float .q-field__label) {
  transform: translateY(-25%) scale(0.75);
}
.factura-page
  :deep(.q-field--dense.q-field--labeled .q-field__control-container) {
  padding-top: 10px;
}
.factura-page :deep(.q-btn:not(.q-btn--round)) {
  min-height: 26px;
  padding: 2px 7px;
  font-size: 11px;
}
.factura-page :deep(.q-btn .q-icon) {
  font-size: 17px;
}
.factura-page :deep(.q-item) {
  min-height: 34px;
  padding: 4px 8px;
}
.factura-page :deep(.text-caption) {
  font-size: 11px;
  line-height: 1.35;
}
.factura-page :deep(.q-banner) {
  min-height: 30px;
  padding: 4px 8px;
}
.factura-page :deep(.q-table__top .q-field) {
  max-width: 240px;
}
.factura-note {
  max-width: 960px;
  margin: 4px auto 8px;
  border-top: 2px solid #1d3557;
}
.factura-note > .q-card__section {
  padding: 8px;
}
.note-table :deep(th),
.note-table :deep(td) {
  height: 26px;
  font-size: 12px;
  padding: 3px 8px;
}
.note-table :deep(td:nth-child(2)) {
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
  font-weight: 600;
}
.note-table :deep(td:nth-child(3)) {
  font-size: 11px;
  color: #546e7a;
  white-space: normal;
}
@media print {
  :global(body:has(.factura-note) *) {
    visibility: hidden;
  }
  .factura-note,
  .factura-note :deep(*) {
    visibility: visible;
  }
  .factura-note {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    max-width: none;
    margin: 0;
    border: none;
  }
  .factura-note :deep(.no-print),
  .factura-note :deep(.no-print *) {
    display: none !important;
  }
}
.facturas-table :deep(.q-table th),
.facturas-table :deep(.q-table td) {
  padding: 2px 8px;
  height: 28px;
  font-size: 12px;
}
.facturas-table :deep(.q-table__top),
.facturas-table :deep(.q-table__bottom) {
  padding: 6px 8px;
  min-height: 36px;
}
.facturas-table :deep(.q-table__title) {
  font-size: 16px;
}
</style>
