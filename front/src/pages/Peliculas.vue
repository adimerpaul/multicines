<template>
  <q-page class="q-pa-xs">
    <div class="row">
      <div class="col-12">
        <q-table dense flat bordered class="tabla-peliculas" title="Gestionar peliculas" v-model:pagination="moviePagination" :rows-per-page-options="[10,20,50,0]" :rows="store.movies" :columns="movieColumns" :filter="movieFilter">

          <template v-slot:top-right>
            <q-btn
              color="green"
              icon="add_circle"
              label="Agregar"
              @click="movieDialog=true"/>
            <q-input outlined dense debounce="300" v-model="movieFilter" placeholder="Buscar">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body-cell-imagen="props">
            <q-td auto-width :props="props">
              <div
                class="drop-img"
                :class="{ 'drop-img--over': dragOverId === props.row.id }"
                @dragover.prevent="dragOverId = props.row.id"
                @dragleave.prevent="dragOverId = null"
                @drop.prevent="onDropImagen($event, props.row)"
              >
                <q-img :src="url+'../imagen/'+props.row.imagen" style="height: 34px; width: 34px;"/>
                <q-inner-loading :showing="uploadingId === props.row.id">
                  <q-spinner size="20px" color="primary" />
                </q-inner-loading>
                <q-tooltip>Arrastra una imagen aquí para cambiarla</q-tooltip>
              </div>
            </q-td>
          </template>
          <template v-slot:body-cell-nombre="props">
            <q-td :props="props" class="celda-nombre">
                {{ props.row.nombre }}
            </q-td>
          </template>
          <template v-slot:body-cell-formato="props">
            <q-td auto-width :props="props">
              <q-badge :color="props.row.formato=='3D' ? 'purple' : 'teal'" :label="props.row.formato" />
            </q-td>
          </template>
          <template v-slot:body-cell-pie="props">
            <q-td auto-width :props="props">
              <div
                class="drop-pie row no-wrap items-center q-gutter-xs cursor-pointer"
                :class="{ 'drop-img--over': pieDragOverId === props.row.id }"
                @click="abrirPie(props.row)"
                @dragover.prevent="pieDragOverId = props.row.id"
                @dragleave.prevent="pieDragOverId = null"
                @drop.prevent="onDropPie($event, props.row)"
              >
                <img
                  v-for="img in (props.row.footer_images || [])"
                  :key="img.id"
                  :src="url+'../imagen/'+img.imagen"
                  class="pie-mini"
                />
                <q-btn v-if="!(props.row.footer_images || []).length" flat dense size="sm" icon="add_photo_alternate" color="grey" />
                <q-inner-loading :showing="pieUploadingId === props.row.id">
                  <q-spinner size="20px" color="primary" />
                </q-inner-loading>
                <q-tooltip>Arrastra imagenes aquí para agregarlas al pie de boleto</q-tooltip>
              </div>
            </q-td>
          </template>
          <template v-slot:body-cell-opciones="props">
            <q-td auto-width :props="props">
              <q-btn-dropdown color="primary" dropdown-icon="more_vert" no-caps dense size="10px" class="q-px-xs">
                <q-list dense>
                  <q-item clickable v-close-popup @click="movieUpdateDialog=true;movie2=props.row;this.store.distributor=props.row.distributor">
                    <q-item-section avatar><q-icon name="edit" color="orange" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Actualizar</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="dialog_img=true;movie2=props.row;">
                    <q-item-section avatar><q-icon name="image" color="primary" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Actualizar Imagen</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="abrirPie(props.row)">
                    <q-item-section avatar><q-icon name="collections" color="teal" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Imagenes pie de boleto</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="movieDelete(props.row.id)">
                    <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>
        </q-table>
        <!--<q-markup-table flat dense bordered>
          <thead>
            <tr>
              <th>Opciones</th>
              <th>Nombre</th>
              <th>Duracion</th>
              <th>Formato</th>
              <th>Imagen</th>
              <th>Distributor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(movie,index) in store.movies" :key="index">
              <td>
                <q-btn-dropdown color="primary" label="Opciones" dropdown-icon="more_vert" no-caps dense size="10px">
                  <q-list>
                    <q-item clickable v-close-popup @click="movieUpdateDialog=true;movie2=movie;this.store.distributor=movie.distributor">
                      <q-item-section avatar><q-icon name="edit" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Actualizar</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="dialog_img=true;movie2=movie;">
                      <q-item-section avatar><q-icon name="picture_as_pdf" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Actualizar Imagen</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="movieDelete(movie.id,index)">
                      <q-item-section avatar><q-icon name="delete" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Eliminar</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>
                <div style="max-width: 140px; word-wrap: break-word; white-space: normal; line-height: 0.9;">
                  {{ $filters.textCapitalize(movie.nombre) }}
                </div>
              </td>
              <td>{{ movie.duracion }}</td>
              <td>{{ movie.formato }}</td>
              <td>
                <a :href="url+'../imagen/'+movie.imagen" target="_blank">
                  <q-img :src="url+'../imagen/'+movie.imagen" class="q-pa-lg" style="border:0px solid black;height: 50px; max-width: 50px;"/>
                </a>
              </td>
              <td>{{ movie.distributor.nombre }}</td>
            </tr>
          </tbody>
        </q-markup-table>-->
      </div>
    </div>
    <q-dialog v-model="movieDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="movieCreate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="nombre" v-model="movie.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="duracion" v-model="movie.duracion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="paisOrigen" v-model="movie.paisOrigen" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="genero" v-model="movie.genero" :options="['Accion','Animacion','Animada','Aventura','Aventuras','Ciencia Ficcion','Comedia','Deporte','Documental','Drama','Historica','Infantil','Musical','Romantica','Suspenso','Terror','Thiller','Western','Otros']"/>
              </div>
              <div class="col-3"><q-toggle v-model="movie.formato" color="green" :label="movie.formato" size="xl"  false-value="2D" true-value="3D"/> </div>

              <div class="col-3">
                <q-input dense outlined label="sipnosis" v-model="movie.sipnosis" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="urlTrailer" v-model="movie.urlTrailer" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="clasificacion" v-model="movie.clasificacion" :options="['+13','+13 C/R','+16','+18','ATP','ATP C/R','R',]"/>
              </div>
              <div class="col-3">
                <q-input dense outlined type="date" label="fechaEstreno" v-model="movie.fechaEstreno" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="distributor_id" v-model="store.distributor" :options="store.distributors" option-label="nombre"/>
              </div>
              <div class="col-2">
                <q-input dense outlined label="ratingPublic" v-model="movie.ratingPublic" type="number" />
              </div>
              <div class="col-2">
                <q-input dense outlined label="ratingPublic" v-model="movie.ratingCritica" type="number" />
              </div>
              <div class="col-12 text-center flex flex-center">
                <q-uploader
                  accept=".jpg, .png"
                  @added="uploadFile"
                  auto-upload
                  max-files="1"
                  label="Ingresar Imagen"
                  flat
                  max-file-size="2000000"
                  @rejected="onRejected"
                  bordered
                />
              </div>
              <div class="col-12">
                <q-btn :loading="loading" color="green" icon="add_circle" class="full-width" type="submit" label="Guardar" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="movieUpdateDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Modificar Pelicula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="movieUpdate">
            <div class="row">
              <div class="col-3">
                <q-input dense outlined label="nombre" v-model="movie2.nombre" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="duracion" v-model="movie2.duracion" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="paisOrigen" v-model="movie2.paisOrigen" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="genero" v-model="movie2.genero" :options="['Accion','Animacion','Animada','Aventura','Aventuras','Ciencia Ficcion','Comedia','Deporte','Documental','Drama','Historica','Infantil','Musical','Romantica','Suspenso','Terror','Thiller','Western','Otros']"/>
              </div>
              <div class="col-3"><q-toggle v-model="movie2.formato" color="green" :label="movie2.formato" size="xl"  false-value="2D" true-value="3D"/> </div>

              <div class="col-3">
                <q-input dense outlined label="sipnosis" v-model="movie2.sipnosis" />
              </div>
              <div class="col-3">
                <q-input dense outlined label="urlTrailer" v-model="movie2.urlTrailer" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="clasificacion" v-model="movie2.clasificacion" :options="['+13','+13 C/R','+16','+18','ATP','ATP C/R','R',]"/>
              </div>
              <div class="col-3">
                <q-input dense outlined type="date" label="fechaEstreno" v-model="movie2.fechaEstreno" />
              </div>
              <div class="col-3">
                <q-select dense outlined label="distributor_id" v-model="store.distributor" :options="store.distributors" option-label="nombre"/>
              </div>
              <div class="col-2">
                <q-input dense outlined label="ratingCritica" v-model="movie2.ratingCritica" type="number" />
              </div>
              <div class="col-2">
                <q-input dense outlined label="ratingPublic" v-model="movie2.ratingPublic" type="number" />
              </div>
              <div class="col-12">
                <q-btn color="yellow-8" icon="edit" class="full-width" type="submit" label="Modificar" :loading="loading" />
              </div>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="pieDialog">
      <q-card style="width: 560px; max-width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Pie de boleto: {{ pieMovie.nombre }} <q-badge :label="pieMovie.formato" /></div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <div class="text-caption text-grey-7 q-mb-sm">
            Imagenes que se imprimen abajo del boleto (una al azar). Se comprimen y se guardan en WebP automaticamente.
          </div>
          <div v-if="!pieImagenes.length" class="text-grey text-center q-pa-md">Sin imagenes</div>
          <q-list bordered separator v-else>
            <q-item v-for="(img, i) in pieImagenes" :key="img.id">
              <q-item-section avatar>
                <div class="pie-preview"><img :src="url+'../imagen/'+img.imagen" /></div>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ img.nombre || img.imagen }}</q-item-label>
                <q-item-label caption>Posicion {{ i + 1 }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="row no-wrap">
                  <q-btn flat round dense icon="arrow_upward" :disable="i===0" @click="moverPie(i,-1)" />
                  <q-btn flat round dense icon="arrow_downward" :disable="i===pieImagenes.length-1" @click="moverPie(i,1)" />
                  <q-btn flat round dense icon="delete" color="negative" @click="eliminarPie(img)" />
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
        <q-card-section class="flex flex-center">
          <q-uploader
            ref="pieUploader"
            accept=".png, .jpg, .jpeg, .webp"
            multiple
            @added="subirPie"
            hide-upload-btn
            label="Arrastra o selecciona imagenes (PNG, JPG, WebP)"
            flat
            bordered
            max-file-size="30000000"
            @rejected="onRejected"
            style="width: 100%;"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="dialog_img">
      <q-card>
        <q-card-section class="bg-amber-14 text-white">
          <div class="text-h6">Modificar imagen</div>
        </q-card-section>
        <q-card-section class="q-pt-xs">
          <q-form
            @submit="onModImagen"
            class="q-gutter-md"
          >
            <div class="col-12 text-center flex flex-center">
              <q-uploader
                accept=".jpg, .png"
                @added="uploadFile"
                auto-upload
                max-files="1"
                label="Ingresar Imagen"
                flat
                max-file-size="2000000"
                @rejected="onRejected"
                bordered
              />
            </div>
            <div>
              <q-btn label="Modificar" type="submit" color="positive" icon="add_circle"/>
              <q-btn  label="Cancelar" icon="delete" color="negative" v-close-popup />
            </div>

          </q-form>
        </q-card-section>


      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import {globalStore} from "stores/globalStore";
import {date} from "quasar";

const TIPOS_PIE = ['image/png', 'image/jpeg', 'image/webp']
const PIE_LADO_MAXIMO = 800

// Reduce la imagen a PIE_LADO_MAXIMO px por lado y la pasa a WebP (conserva
// la transparencia). Si el navegador no puede, se sube el archivo original.
async function comprimirWebp(file) {
  try {
    const bitmap = await createImageBitmap(file)
    const escala = Math.min(1, PIE_LADO_MAXIMO / Math.max(bitmap.width, bitmap.height))
    const canvas = document.createElement('canvas')
    canvas.width = Math.max(1, Math.round(bitmap.width * escala))
    canvas.height = Math.max(1, Math.round(bitmap.height * escala))
    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height)
    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/webp', 0.85))
    if (!blob || blob.type !== 'image/webp') return file
    return new File([blob], file.name.replace(/\.\w+$/, '') + '.webp', { type: 'image/webp' })
  } catch (e) {
    return file
  }
}

export default {
  name: `Peliculas`,
  data() {
    return {
      loading: false,
      movieFilter:'',
      moviePagination:{ rowsPerPage:10 },
      url:process.env.API,
      dialog_img:false,
      dragOverId:null,
      pieDialog:false,
      pieDragOverId:null,
      pieUploadingId:null,
      pieMovie:{},
      uploadingId:null,
      movie:{
        clasificacion:'+13',
        formato:'2D',
        genero:'Accion',
        fechaEstreno:date.formatDate(new Date(),'YYYY-MM-DD'),
      },
      movie2:{},
      movieDialog:false,
      movieUpdateDialog:false,
      store: globalStore(),
      foto:'',
      movieColumns:[
        {label:'opciones',field:'opciones',name:'opciones'},
        {label:'nombre',field:'nombre',name:'nombre',sortable:true},
        {label:'duracion',field:'duracion',name:'duracion',sortable:true},
        // {label:'paisOrigen',field:'paisOrigen',name:'paisOrigen',sortable:true},
        // {label:'genero',field:'genero',name:'genero',sortable:true},
        {label:'formato',field:'formato',name:'formato',sortable:true},
        // {label:'clasificacion',field:'clasificacion',name:'clasificacion',sortable:true},
        {label:'fechaEstreno',field:'fechaEstreno',name:'fechaEstreno',sortable:true},
        {label:'Imagen',field:'imagen',name:'imagen',sortable:true},
        {label:'Pie de boleto',field:row=>(row.footer_images||[]).length,name:'pie',align:'left'},
        {label:'distributor_id',field:row=>row.distributor.nombre,name:'distributor_id',sortable:true},
      ]
    }
  },
  created() {
    if(!this.store.movieSingleTon) {
      this.$q.loading.show()
      this.store.movieSingleTon=true
      this.$api.get('movie').then(res=>{
        res.data.forEach(p=>{
          p.label=p.nombre+' ' + p.formato
        })
        this.store.movies2=res.data
        this.store.movies3=res.data
        this.store.movies=res.data
        this.$q.loading.hide()
      })
    }
    if(!this.store.distributorSingleTon){
      this.$q.loading.show()
      this.store.distributorSingleTon=true
      this.$api.get('distributor').then(res=>{
        this.store.distributors=res.data
        this.store.distributor=res.data[0]
        this.$q.loading.hide()
      })
    }
  },
  computed: {
    pieImagenes(){
      return this.pieMovie.footer_images || []
    }
  },
  methods: {
    abrirPie(row){
      if (!row.footer_images) row.footer_images = []
      this.pieMovie = row
      this.pieDialog = true
    },
    async onDropPie(event, row){
      this.pieDragOverId = null
      const files = Array.from(event.dataTransfer.files || [])
      if (!files.length) return
      const validos = files.filter(f => TIPOS_PIE.includes(f.type))
      if (validos.length < files.length) {
        this.$q.notify({ type: 'negative', message: 'Solo se aceptan imagenes PNG, JPG o WebP; se omitieron ' + (files.length - validos.length) + ' archivo(s)' })
      }
      if (!validos.length) return
      if (!row.footer_images) row.footer_images = []
      this.pieUploadingId = row.id
      const agregadas = await this.agregarPies(row, validos)
      this.pieUploadingId = null
      if (agregadas) {
        this.$q.notify({ type: 'positive', icon: 'done', message: agregadas + ' imagen(es) de pie agregada(s) a ' + row.nombre })
      }
    },
    async subirPie(files){
      await this.agregarPies(this.pieMovie, files)
      this.$refs.pieUploader.reset()
    },
    async agregarPies(movie, files){
      let agregadas = 0
      for (const file of files) {
        try {
          // Se comprime en el navegador para no chocar con el limite de 2MB
          // de PHP; el servidor igual la vuelve a reducir y guarda en WebP.
          const comprimida = await comprimirWebp(file)
          const fd = new FormData()
          fd.append('file', comprimida, comprimida.name)
          fd.append('movie_id', movie.id)
          fd.append('nombre', file.name.replace(/\.(png|jpe?g|webp)$/i, ''))
          const res = await this.$api.post('movie-footer-image', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
          movie.footer_images.push(res.data)
          agregadas++
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'Error al subir ' + file.name + ': ' + (e.response?.data?.message || e.message) })
        }
      }
      return agregadas
    },
    moverPie(i, dir){
      const lista = this.pieMovie.footer_images
      const [img] = lista.splice(i, 1)
      lista.splice(i + dir, 0, img)
      this.$api.post('movie/' + this.pieMovie.id + '/footer-images/ordenar', { ids: lista.map(x => x.id) })
        .catch(() => this.$q.notify({ type: 'negative', message: 'No se pudo guardar el orden' }))
    },
    eliminarPie(img){
      this.$q.dialog({
        title: 'Eliminar imagen',
        message: '¿Quitar esta imagen del pie de boleto?',
        cancel: true,
      }).onOk(() => {
        this.$api.delete('movie-footer-image/' + img.id).then(() => {
          const lista = this.pieMovie.footer_images
          const index = lista.findIndex(x => x.id === img.id)
          if (index !== -1) lista.splice(index, 1)
        })
      })
    },
    onDropImagen(event, row){
      this.dragOverId = null
      const file = event.dataTransfer.files[0]
      if (!file) return
      if (!['image/jpeg','image/png'].includes(file.type)) {
        this.$q.notify({ type: 'negative', message: 'Solo se permiten imagenes .jpg o .png' })
        return
      }
      if (file.size > 2000000) {
        this.$q.notify({ type: 'negative', message: 'La imagen no debe superar 2MB' })
        return
      }
      const fd = new FormData()
      fd.append('file', file)
      this.uploadingId = row.id
      this.$api.post('upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(res => this.$api.post('upimagenmovie', { id: row.id, imagen: res.data }))
        .then(res => {
          let index = this.store.movies.findIndex(m => m.id == res.data.id);
          if (index !== -1) this.store.movies[index] = res.data
          this.$q.notify({ type: 'positive', message: 'Imagen actualizada: ' + res.data.nombre, icon: 'done' })
        })
        .catch(() => {
          this.$q.notify({ type: 'negative', message: 'Error al subir la imagen' })
        })
        .finally(() => {
          this.uploadingId = null
        })
    },
    onModImagen(){
      this.movie2.imagen=this.foto
      this.$q.loading.show()
      this.$api.post('upimagenmovie',this.movie2).then(res=>{
        this.$q.loading.hide()
        console.log(res.data)
        this.movie2={}
        this.foto=''
        let index = this.store.movies.findIndex(m => m.id == res.data.id);
        this.store.movies[index]=res.data
        this.dialog_img=false
      })
    },
    uploadFile (file) {
      let dialog = this.$q.dialog({
        message: 'Subiendo... 0%',
      })
      let percentage = 0
      const fd = new FormData()
      fd.append('file', file[0])
      return new Promise((resolve, reject) => {
        this.$api.post('upload',
          fd,
          {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
              percentage = Math.round((progressEvent.loaded / progressEvent.total) * 100)
              dialog.update({
                message: `Subiendo... ${percentage}%`
              })
              if (percentage>=100){
                dialog.hide()
              }
            }
          })
          .then(res => {
            this.foto=res.data
            resolve(file)
          })
          .catch(err => reject(err))
      })
    },
    onRejected (rejectedEntries) {
      this.$q.notify({
        type: 'negative',
        message: `${rejectedEntries.length} el archivo paso las restricciones de validación`
      })
    },
    movieCreate(){
      this.movie.imagen=this.foto
      this.loading=true
      this.movie.distributor_id=this.store.distributor.id
      this.$api.post('movie',this.movie).then(res=>{
        this.loading=false
        this.store.movies.push(res.data)
        this.foto=''
        this.movieDialog=false
        this.movie={
          clasificacion:'+13',
            formmato:'2D',
            genero:'Accion',
            fechaEstreno:date.formatDate(new Date(),'YYYY-MM-DD'),
        }
      })
    },
    movieUpdate(){
      // this.$q.loading.show()
      this.loading=true
      this.$api.put('movie/'+this.movie2.id,this.movie2).then(res=>{
        // this.$q.loading.hide()
        console.log(res.data)
        this.movie2={}
        let index = this.store.movies.findIndex(m => m.id == res.data.id);
        this.store.movies[index]=res.data
        this.movieUpdateDialog=false
      }).finally(()=>{
        this.loading=false
      })
    },
    movieDelete(id){
      this.$q.dialog({
        title: 'Eliminar Pelicula',
        message: '¿Esta seguro de eliminar la pelicula?',
        ok: {
          push: true
        },
        cancel: {
          push: true,
          color: 'negative'
        },
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('movie/'+id).then(res=>{
          let index = this.store.movies.findIndex(m => m.id == id);
          if (index !== -1) this.store.movies.splice(index,1)
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Pelicula eliminada',
            color: 'positive',
            icon: 'done'
          })
        })
      })
    }
  }
}
</script>

<style scoped>
.drop-img {
  position: relative;
  width: 40px;
  height: 40px;
  padding: 1px;
  border: 2px dashed transparent;
  border-radius: 6px;
  transition: border-color .15s, background-color .15s;
}
.drop-img:hover {
  border-color: #bdbdbd;
}
.drop-pie {
  position: relative;
  min-width: 40px;
  min-height: 30px;
  padding: 1px;
  border: 2px dashed transparent;
  border-radius: 6px;
  transition: border-color .15s, background-color .15s;
}
.drop-pie:hover {
  border-color: #bdbdbd;
}
.pie-mini {
  height: 24px;
  width: 24px;
  object-fit: contain;
}
.pie-preview {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  /* cuadriculado para ver la transparencia del PNG */
  background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 12px 12px;
  border-radius: 6px;
}
.pie-preview img {
  max-width: 52px;
  max-height: 52px;
  object-fit: contain;
}
.tabla-peliculas :deep(td) {
  padding-top: 2px;
  padding-bottom: 2px;
}
.celda-nombre {
  max-width: 260px;
  white-space: normal;
  line-height: 1.1;
}
.drop-img--over {
  border-color: #21ba45;
  background-color: rgba(33, 186, 69, .15);
}

</style>
