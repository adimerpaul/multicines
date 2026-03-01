<template>
  <q-page class="q-pa-sm">
    <q-card flat bordered>
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col-12 col-md-4 text-h6">Peliculas Web</div>
        <div class="col-12 col-md-8 row justify-end q-gutter-sm">
          <q-btn color="primary" icon="travel_explore" label="Buscar en TMDB" @click="importDialog = true" />
          <q-btn color="positive" icon="add_circle" label="Nueva" @click="openCreate" />
          <q-input dense outlined debounce="300" v-model="filter" placeholder="Buscar">
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-table
          dense
          :rows="rows"
          :columns="columns"
          :filter="filter"
          row-key="id"
          :rows-per-page-options="[0]"
        >
          <template v-slot:body-cell-imagen="props">
            <q-td :props="props" auto-width>
              <q-img :src="movieImage(props.row.imagen)" style="height: 60px; width: 45px; border-radius: 8px" />
            </q-td>
          </template>

          <template v-slot:body-cell-backdrop_imagen="props">
            <q-td :props="props" auto-width>
              <q-img :src="movieImage(props.row.backdrop_imagen)" style="height: 45px; width: 80px; border-radius: 8px" />
            </q-td>
          </template>

          <template v-slot:body-cell-estudio="props">
            <q-td :props="props">{{ props.row.studio ? props.row.studio.nombre : '' }}</q-td>
          </template>

          <template v-slot:body-cell-estado="props">
            <q-td :props="props">
              <q-chip dense :color="props.row.estado === 'ACTIVO' ? 'positive' : 'negative'" text-color="white">
                {{ props.row.estado || 'INACTIVO' }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-actores="props">
            <q-td :props="props" style="max-width: 340px">
              <div class="ellipsis">{{ (props.row.actores || []).map((a) => a.nombre).join(', ') }}</div>
            </q-td>
          </template>

          <template v-slot:body-cell-opciones="props">
            <q-td :props="props" auto-width>
              <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert" no-caps dense size="11px">
                <q-list>
                  <q-item clickable v-close-popup @click="openEdit(props.row)">
                    <q-item-section avatar>
                      <q-icon name="edit" color="primary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="openImageDialog(props.row)">
                    <q-item-section avatar>
                      <q-icon name="image" color="warning" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Actualizar imagen</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="openBackgroundDialog(props.row)">
                    <q-item-section avatar>
                      <q-icon name="wallpaper" color="deep-orange" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Actualizar background</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="openLinkProgramDialog(props.row)">
                    <q-item-section avatar>
                      <q-icon name="link" color="secondary" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Vincular con programacion</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="removeRow(props.row.id)">
                    <q-item-section avatar>
                      <q-icon name="delete" color="negative" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="dialog" persistent full-width>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">{{ isEdit ? 'Editar pelicula web' : 'Nueva pelicula web' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit.prevent="saveRow">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-md-4"><q-input dense outlined label="Titulo" v-model="form.titulo" :rules="[required]" /></div>
              <div class="col-12 col-md-2"><q-select dense outlined label="Tipo" v-model="form.tipo" :options="['pelicula', 'proximo']" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="TMDB ID" v-model.number="form.tmdb_id" type="number" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="IMDb ID" v-model="form.imdb_id" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="Anio" v-model.number="form.anio" type="number" /></div>

              <div class="col-12 col-md-3"><q-input dense outlined label="Estudio" v-model="form.studio_nombre" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="Calidad" v-model="form.calidad" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="Descuento (%)" v-model.number="form.descuento" type="number" min="0" max="100" step="0.1" /></div>
              <div class="col-12 col-md-2"><q-input dense outlined label="Bucket" v-model="form.bucket" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Fecha Estreno" v-model="form.fecha_estreno" type="date" /></div>

              <div class="col-12 col-md-3"><q-input dense outlined label="Puntaje Web (0-10)" v-model.number="form.puntaje_web" type="number" step="0.1" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Puntaje Tomatoes (0-10)" v-model.number="form.puntaje_tomatoes" type="number" step="0.1" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Puntaje IBM (0-10)" v-model.number="form.puntaje_ibm" type="number" step="0.1" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Puntaje Metacritic (0-10)" v-model.number="form.puntaje_metacritic" type="number" step="0.1" /></div>

              <div class="col-12 col-md-3"><q-input dense outlined label="Votos Total" v-model.number="form.votos_total" type="number" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Popularidad" v-model.number="form.popularidad" type="number" step="0.001" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Clasificacion" v-model="form.clasificacion" /></div>
              <div class="col-12 col-md-3">
                <q-select dense outlined label="Estado" v-model="form.estado" :options="['ACTIVO', 'INACTIVO']" />
              </div>

              <div class="col-12 col-md-4"><q-input dense outlined label="Genero" v-model="form.genero" /></div>
              <div class="col-12 col-md-4"><q-input dense outlined label="Pais" v-model="form.pais" /></div>
              <div class="col-12 col-md-4"><q-input dense outlined label="Idioma" v-model="form.idioma" /></div>

              <div class="col-12 col-md-3"><q-input dense outlined label="Duracion" v-model="form.duracion" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Tele" v-model="form.tele" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Me gusta" v-model.number="form.me_gusta" type="number" /></div>
              <div class="col-12 col-md-3"><q-input dense outlined label="Tagline" v-model="form.tagline" /></div>

              <div class="col-12 col-md-6"><q-input dense outlined label="URL video YouTube" v-model="form.url_video_youtube" /></div>
              <div class="col-12 col-md-6"><q-input dense outlined label="URL oficial" v-model="form.url_oficial" /></div>
              <div class="col-12 col-md-6">
                <q-select
                  dense
                  outlined
                  label="Carrusel"
                  v-model="form.carrusel_tipo"
                  :options="[
                    { label: 'Ninguno', value: 'ninguno' },
                    { label: 'Carrusel 1 (principal)', value: 'carrusel_1' },
                    { label: 'Carrusel 2 (secundario)', value: 'carrusel_2' },
                    { label: 'Ambos', value: 'ambos' }
                  ]"
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12"><q-input dense outlined label="Actores (separados por coma)" v-model="actoresTexto" /></div>
              <div class="col-12"><q-input dense outlined label="Premios" v-model="form.premios" /></div>
              <div class="col-12"><q-input dense outlined type="textarea" label="Descripcion" v-model="form.descripcion" /></div>

              <div class="col-12 col-md-5">
                <q-file dense outlined v-model="posterFile" label="Imagen de portada (opcional)" accept=".jpg,.jpeg,.png" @update:model-value="onPosterSelected">
                  <template v-slot:prepend><q-icon name="image" /></template>
                </q-file>
              </div>
              <div class="col-12 col-md-7">
                <div class="text-caption text-grey-7 q-mb-xs">Vista previa</div>
                <q-img :src="posterPreview || movieImage(form.imagen)" style="height: 160px; max-width: 220px; border-radius: 10px" />
              </div>

              <div class="col-12 col-md-5">
                <q-file dense outlined v-model="backdropFile" label="Imagen de background (opcional)" accept=".jpg,.jpeg,.png" @update:model-value="onBackdropSelected">
                  <template v-slot:prepend><q-icon name="wallpaper" /></template>
                </q-file>
              </div>
              <div class="col-12 col-md-7">
                <div class="text-caption text-grey-7 q-mb-xs">Vista previa background</div>
                <q-img :src="backdropPreview || movieImage(form.backdrop_imagen)" style="height: 160px; max-width: 320px; border-radius: 10px" />
              </div>

              <div class="col-12" v-if="uploadProgress > 0 && uploadProgress < 100">
                <q-linear-progress :value="uploadProgress / 100" color="primary" rounded size="8px" />
              </div>

              <div class="col-12">
                <q-btn class="full-width" color="positive" icon="save" type="submit" :loading="loading" label="Guardar cambios" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="imageDialog" persistent>
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ imageDialogMode === 'background' ? 'Actualizar background' : 'Actualizar imagen' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <div class="text-subtitle2 q-mb-sm">Imagen actual</div>
              <q-img :src="imageDialogCurrent" style="height: 260px; border-radius: 12px" />
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2 q-mb-sm">Nueva imagen</div>
              <q-file dense outlined v-model="replaceImageFile" accept=".jpg,.jpeg,.png" label="Seleccionar imagen" @update:model-value="onReplaceSelected" />
              <q-img v-if="replaceImagePreview" class="q-mt-md" :src="replaceImagePreview" style="height: 260px; border-radius: 12px" />
            </div>
          </div>
          <div class="q-mt-md" v-if="uploadProgress > 0 && uploadProgress < 100">
            <q-linear-progress :value="uploadProgress / 100" color="warning" rounded size="8px" />
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="negative" v-close-popup />
          <q-btn :label="imageDialogMode === 'background' ? 'Actualizar background' : 'Actualizar imagen'" color="warning" icon="cloud_upload" :loading="loading" @click="saveImageOnly" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="linkProgramDialog" persistent full-width>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">Vincular con programacion</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="row q-col-gutter-sm q-mb-sm">
            <div class="col-12 col-md-4">
              <q-input dense outlined v-model="programFilterText" label="Filtrar por pelicula" clearable>
                <template v-slot:append><q-icon name="search" /></template>
              </q-input>
            </div>
            <div class="col-12 col-md-3">
              <q-select dense outlined v-model="programFilterSala" label="Filtrar por sala" :options="programSalaOptions" clearable />
            </div>
            <div class="col-12 col-md-3">
              <q-select dense outlined v-model="programFilterTarifa" label="Filtrar por tarifa" :options="programTarifaOptions" clearable />
            </div>
            <div class="col-12 col-md-2 row q-gutter-xs">
              <q-btn dense color="secondary" icon="done_all" label="Todos" @click="selectAllFilteredPrograms" />
              <q-btn dense flat color="negative" icon="clear_all" label="Limpiar" @click="clearProgramSelection" />
            </div>
          </div>
          <q-table
            dense
            :rows="filteredProgramRows"
            :columns="programColumns"
            row-key="id"
            :rows-per-page-options="[0]"
            selection="multiple"
            v-model:selected="selectedProgramRows"
          >
            <template v-slot:body-cell-fecha="props">
              <q-td :props="props">{{ formatProgramDate(props.row.fecha, props.row.horaInicio) }}</q-td>
            </template>
            <template v-slot:body-cell-pelicula="props">
              <q-td :props="props">{{ props.row.movie ? props.row.movie.nombre : '' }}</q-td>
            </template>
            <template v-slot:body-cell-sala="props">
              <q-td :props="props">{{ props.row.sala ? props.row.sala.nombre : '' }}</q-td>
            </template>
            <template v-slot:body-cell-tarifa="props">
              <q-td :props="props">{{ props.row.price ? props.row.price.precio : '' }}</q-td>
            </template>
          </q-table>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="negative" v-close-popup />
          <q-btn color="primary" icon="save" label="Guardar vinculos" :loading="loading" @click="saveProgramLinks" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="importDialog" full-width>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">Importar desde TMDB</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-md-10">
              <q-input dense outlined v-model="searchQuery" label="Buscar pelicula" @keyup.enter="searchExternal" />
            </div>
            <div class="col-12 col-md-2">
              <q-btn color="primary" class="full-width" label="Buscar" icon="search" :loading="searching" @click="searchExternal" />
            </div>
          </div>

          <q-list bordered separator class="q-mt-md">
            <q-item v-for="item in externalResults" :key="item.tmdb_id">
              <q-item-section avatar>
                <q-img :src="item.imagen || movieImage('default.jpg')" style="height: 64px; width: 48px; border-radius: 8px" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.titulo }}</q-item-label>
                <q-item-label caption>{{ item.anio }} | TMDB: {{ item.tmdb_id }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn color="positive" label="Importar" :loading="loadingImportId === item.tmdb_id" @click="importExternal(item)" />
              </q-item-section>
            </q-item>
            <q-item v-if="externalResults.length === 0">
              <q-item-section><q-item-label caption>No hay resultados</q-item-label></q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
const emptyForm = () => ({
  titulo: '',
  tipo: 'pelicula',
  carrusel_tipo: 'ninguno',
  tmdb_id: null,
  imdb_id: null,
  titulo_original: null,
  studio_nombre: '',
  imagen: null,
  backdrop_imagen: null,
  trailer_youtube: null,
  url_video_youtube: null,
  bucket: 'local',
  puntaje_web: null,
  puntaje_tomatoes: null,
  puntaje_ibm: null,
  puntaje_metacritic: null,
  votos_total: null,
  popularidad: null,
  tagline: null,
  url_oficial: null,
  calidad: 'HD',
  descuento: 0,
  anio: null,
  fecha_estreno: null,
  tele: null,
  descripcion: null,
  me_gusta: 0,
  duracion: null,
  genero: null,
  pais: null,
  idioma: null,
  clasificacion: null,
  premios: null,
  estado: 'ACTIVO',
  actores: [],
});

export default {
  name: 'PeliculasWeb',
  data() {
    return {
      url: process.env.API,
      filter: '',
      loading: false,
      searching: false,
      rows: [],
      dialog: false,
      importDialog: false,
      imageDialog: false,
      linkProgramDialog: false,
      isEdit: false,
      currentRow: null,
      searchQuery: '',
      externalResults: [],
      loadingImportId: null,
      uploadProgress: 0,
      actoresTexto: '',
      form: emptyForm(),
      posterFile: null,
      posterPreview: '',
      backdropFile: null,
      backdropPreview: '',
      replaceImageFile: null,
      replaceImagePreview: '',
      imageDialogCurrent: '',
      imageDialogMode: 'poster',
      programRows: [],
      selectedProgramRows: [],
      programFilterText: '',
      programFilterSala: null,
      programFilterTarifa: null,
      columns: [
        { label: 'Opciones', field: 'opciones', name: 'opciones' },
        { label: 'Imagen', field: 'imagen', name: 'imagen' },
        { label: 'BG', field: 'backdrop_imagen', name: 'backdrop_imagen' },
        { label: 'Titulo', field: 'titulo', name: 'titulo', sortable: true },
        { label: 'Estado', field: 'estado', name: 'estado', sortable: true },
        { label: 'Tipo', field: 'tipo', name: 'tipo', sortable: true },
        { label: 'Carrusel', field: 'carrusel_tipo', name: 'carrusel_tipo', sortable: true },
        { label: 'Puntaje', field: 'puntaje_web', name: 'puntaje_web', sortable: true },
        { label: 'Descuento %', field: 'descuento', name: 'descuento', sortable: true },
        { label: 'Bucket', field: 'bucket', name: 'bucket', sortable: true },
        { label: 'Programas', field: (row) => (row.programas || []).length, name: 'programas', sortable: true },
        { label: 'YouTube', field: 'url_video_youtube', name: 'url_video_youtube' },
        { label: 'Estudio', field: 'estudio', name: 'estudio', sortable: true },
        { label: 'Actores', field: 'actores', name: 'actores' },
      ],
      programColumns: [
        { label: 'ID', field: 'id', name: 'id', sortable: true },
        { label: 'Fecha', field: 'fecha', name: 'fecha', sortable: true },
        { label: 'Inicio', field: 'horaInicio', name: 'horaInicio', sortable: true },
        { label: 'Fin', field: 'horaFin', name: 'horaFin', sortable: true },
        { label: 'Nro Funcion', field: 'nroFuncion', name: 'nroFuncion', sortable: true },
        { label: 'Pelicula', field: 'pelicula', name: 'pelicula' },
        { label: 'Sala', field: 'sala', name: 'sala' },
        { label: 'Tarifa', field: 'tarifa', name: 'tarifa' },
      ],
    };
  },
  created() {
    this.loadRows();
  },
  computed: {
    filteredProgramRows() {
      return (this.programRows || []).filter((row) => {
        const movieName = row.movie?.nombre || '';
        const salaName = row.sala?.nombre || '';
        const tarifa = row.price?.precio != null ? String(row.price.precio) : '';

        const byName = this.programFilterText
          ? movieName.toLowerCase().includes(this.programFilterText.toLowerCase())
          : true;
        const bySala = this.programFilterSala ? salaName === this.programFilterSala : true;
        const byTarifa = this.programFilterTarifa ? tarifa === String(this.programFilterTarifa) : true;

        return byName && bySala && byTarifa;
      });
    },
    programSalaOptions() {
      const names = [...new Set((this.programRows || []).map((row) => row.sala?.nombre).filter(Boolean))];
      return names.sort();
    },
    programTarifaOptions() {
      const tarifas = [...new Set((this.programRows || []).map((row) => row.price?.precio).filter((v) => v != null))];
      return tarifas.sort((a, b) => a - b).map((v) => String(v));
    },
  },
  methods: {
    required(v) {
      return !!v || 'Requerido';
    },
    movieImage(name) {
      if (!name) return this.url + '../imagen/default.jpg';
      return this.url + '../imagen/' + name;
    },
    loadRows() {
      this.$q.loading.show();
      this.$api.get('webMovie').then((res) => {
        this.rows = res.data;
      }).finally(() => {
        this.$q.loading.hide();
      });
    },
    openCreate() {
      this.isEdit = false;
      this.currentRow = null;
      this.form = emptyForm();
      this.actoresTexto = '';
      this.posterFile = null;
      this.posterPreview = '';
      this.backdropFile = null;
      this.backdropPreview = '';
      this.dialog = true;
    },
    openEdit(row) {
      this.isEdit = true;
      this.currentRow = row;
      this.form = {
        ...emptyForm(),
        ...row,
        studio_nombre: row.studio ? row.studio.nombre : '',
        bucket: row.bucket || 'local',
      };
      this.actoresTexto = (row.actores || []).map((a) => a.nombre).join(', ');
      this.posterFile = null;
      this.posterPreview = '';
      this.backdropFile = null;
      this.backdropPreview = '';
      this.dialog = true;
    },
    openImageDialog(row) {
      this.currentRow = row;
      this.imageDialogCurrent = this.movieImage(row.imagen);
      this.replaceImageFile = null;
      this.replaceImagePreview = '';
      this.imageDialogMode = 'poster';
      this.imageDialog = true;
    },
    openBackgroundDialog(row) {
      this.currentRow = row;
      this.imageDialogCurrent = this.movieImage(row.backdrop_imagen);
      this.replaceImageFile = null;
      this.replaceImagePreview = '';
      this.imageDialogMode = 'background';
      this.imageDialog = true;
    },
    async openLinkProgramDialog(row) {
      this.currentRow = row;
      this.linkProgramDialog = true;
      this.programFilterText = '';
      this.programFilterSala = null;
      this.programFilterTarifa = null;
      await this.loadProgramRows();
      const selectedIds = (row.programas || []).map((p) => p.id);
      this.selectedProgramRows = this.programRows.filter((p) => selectedIds.includes(p.id));
    },
    onPosterSelected(file) {
      this.posterPreview = file ? URL.createObjectURL(file) : '';
    },
    onBackdropSelected(file) {
      this.backdropPreview = file ? URL.createObjectURL(file) : '';
    },
    onReplaceSelected(file) {
      this.replaceImagePreview = file ? URL.createObjectURL(file) : '';
    },
    async uploadImage(file) {
      if (!file) return null;
      const fd = new FormData();
      fd.append('file', file);
      const res = await this.$api.post('upload', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (event) => {
          this.uploadProgress = Math.round((event.loaded / event.total) * 100);
        },
      });
      this.uploadProgress = 0;
      return res.data;
    },
    buildActors() {
      return this.actoresTexto
        .split(',')
        .map((x) => x.trim())
        .filter((x) => !!x)
        .map((x) => ({ nombre: x, imagen: null }));
    },
    async saveRow() {
      try {
        this.loading = true;
        let uploaded = null;
        let uploadedBackdrop = null;
        if (this.posterFile) {
          uploaded = await this.uploadImage(this.posterFile);
        }
        if (this.backdropFile) {
          uploadedBackdrop = await this.uploadImage(this.backdropFile);
        }

        const payload = {
          ...this.form,
          imagen: uploaded || this.form.imagen,
          backdrop_imagen: uploadedBackdrop || this.form.backdrop_imagen,
          trailer_youtube: this.form.url_video_youtube || this.form.trailer_youtube,
          url_video_youtube: this.form.url_video_youtube || this.form.trailer_youtube,
          actores: this.buildActors(),
        };

        const res = this.isEdit
          ? await this.$api.put('webMovie/' + this.form.id, payload)
          : await this.$api.post('webMovie', payload);

        if (this.isEdit) {
          const i = this.rows.findIndex((x) => x.id === res.data.id);
          if (i !== -1) this.rows[i] = res.data;
        } else {
          this.rows.unshift(res.data);
        }
        this.dialog = false;
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Guardado correctamente' });
      } catch (e) {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'No se pudo guardar' });
      } finally {
        this.loading = false;
      }
    },
    async saveImageOnly() {
      if (!this.replaceImageFile) {
        this.$q.notify({ color: 'warning', message: 'Selecciona una imagen primero' });
        return;
      }

      try {
        this.loading = true;
        const uploaded = await this.uploadImage(this.replaceImageFile);
        const row = this.currentRow;
        const payload = {
          ...row,
          studio_nombre: row.studio ? row.studio.nombre : null,
          actores: row.actores || [],
          imagen: this.imageDialogMode === 'poster' ? uploaded : row.imagen,
          backdrop_imagen: this.imageDialogMode === 'background' ? uploaded : row.backdrop_imagen,
        };
        const res = await this.$api.put('webMovie/' + row.id, payload);
        const i = this.rows.findIndex((x) => x.id === res.data.id);
        if (i !== -1) this.rows[i] = res.data;
        this.imageDialog = false;
        this.$q.notify({ color: 'positive', icon: 'done', message: this.imageDialogMode === 'background' ? 'Background actualizado' : 'Imagen actualizada' });
      } catch (e) {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'No se pudo actualizar imagen' });
      } finally {
        this.loading = false;
      }
    },
    async loadProgramRows() {
      const res = await this.$api.get('webMovieProgramaciones');
      this.programRows = res.data || [];
    },
    selectAllFilteredPrograms() {
      this.selectedProgramRows = [...this.filteredProgramRows];
    },
    clearProgramSelection() {
      this.selectedProgramRows = [];
    },
    formatProgramDate(value, horaInicio) {
      if (!value) return '';
      const date = new Date(`${value}T00:00:00`);
      if (Number.isNaN(date.getTime())) return value;
      const dayName = date.toLocaleDateString('es-ES', { weekday: 'long' });
      const formatted = date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      });
      let hourText = '';
      if (horaInicio) {
        const start = new Date(horaInicio);
        if (!Number.isNaN(start.getTime())) {
          hourText = start.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
          });
        }
      }
      return `${dayName.charAt(0).toUpperCase()}${dayName.slice(1)} ${formatted}${hourText ? ` ${hourText}` : ''}`;
    },
    async saveProgramLinks() {
      if (!this.currentRow) return;
      try {
        this.loading = true;
        const programaIds = this.selectedProgramRows.map((p) => p.id);
        const res = await this.$api.post(`webMovie/${this.currentRow.id}/syncProgramaciones`, {
          programa_ids: programaIds,
        });
        const i = this.rows.findIndex((x) => x.id === res.data.id);
        if (i !== -1) this.rows[i] = res.data;
        this.linkProgramDialog = false;
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Programacion vinculada correctamente' });
      } catch (e) {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'No se pudo vincular programacion' });
      } finally {
        this.loading = false;
      }
    },
    removeRow(id) {
      this.$q.dialog({
        title: 'Eliminar',
        message: 'Esta seguro de eliminar esta pelicula?',
        cancel: true,
      }).onOk(async () => {
        await this.$api.delete('webMovie/' + id);
        this.rows = this.rows.filter((x) => x.id !== id);
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Eliminado' });
      });
    },
    searchExternal() {
      if (!this.searchQuery || this.searchQuery.length < 2) {
        this.$q.notify({ color: 'warning', message: 'Ingresa al menos 2 caracteres' });
        return;
      }
      this.searching = true;
      this.$api.post('webMovieSearchExternal', { query: this.searchQuery }).then((res) => {
        this.externalResults = res.data || [];
      }).catch((e) => {
        this.externalResults = [];
        this.$q.notify({ color: 'negative', message: e?.response?.data?.message || 'No se pudo buscar en TMDB' });
      }).finally(() => {
        this.searching = false;
      });
    },
    importExternal(item) {
      this.loadingImportId = item.tmdb_id;
      this.$api.post('webMovieImportExternal', {
        tmdb_id: item.tmdb_id,
        tipo: 'pelicula',
      }).then((res) => {
        const i = this.rows.findIndex((x) => x.id === res.data.id);
        if (i !== -1) this.rows[i] = res.data;
        else this.rows.unshift(res.data);
        this.$q.notify({ color: 'positive', icon: 'done', message: 'Importado y guardado localmente' });
      }).catch((e) => {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'Error al importar' });
      }).finally(() => {
        this.loadingImportId = null;
      });
    },
  },
};
</script>

<style scoped>
.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
