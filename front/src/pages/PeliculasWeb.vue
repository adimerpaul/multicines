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
              <q-img :src="movieImage(props.row.imagen)" class="cursor-pointer" style="height: 60px; width: 45px; border-radius: 8px" @click="openImageFull(movieImage(props.row.imagen), props.row.titulo)" />
            </q-td>
          </template>

          <template v-slot:body-cell-backdrop_imagen="props">
            <q-td :props="props" auto-width>
              <q-img :src="movieImage(props.row.backdrop_imagen)" fit="contain" class="img-preview cursor-pointer" style="height: 45px; width: 80px; border-radius: 8px" @click="openImageFull(movieImage(props.row.backdrop_imagen), props.row.titulo)" />
            </q-td>
          </template>

          <template v-slot:body-cell-estudio="props">
            <q-td :props="props">{{ props.row.studio ? props.row.studio.nombre : '' }}</q-td>
          </template>

          <template v-slot:body-cell-estado="props">
            <q-td :props="props">
              <q-toggle
                dense
                color="positive"
                :model-value="props.row.estado === 'ACTIVO'"
                :label="props.row.estado === 'ACTIVO' ? 'Activo' : 'Inactivo'"
                @update:model-value="(val) => toggleEstado(props.row, val)"
              >
                <q-tooltip>{{ props.row.estado === 'ACTIVO' ? 'Se muestra en la web' : 'Oculta en la web' }}</q-tooltip>
              </q-toggle>
            </q-td>
          </template>

          <template v-slot:body-cell-venta_online="props">
            <q-td :props="props" auto-width>
              <q-toggle
                dense
                color="positive"
                :model-value="props.row.venta_online !== false"
                @update:model-value="(val) => toggleVentaOnline(props.row, val)"
              >
                <q-tooltip>{{ props.row.venta_online !== false ? 'Se puede comprar en la web' : 'Solo venta en ventanilla' }}</q-tooltip>
              </q-toggle>
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
<!--                  <q-item clickable v-close-popup @click="openImageDialog(props.row)">-->
<!--                    <q-item-section avatar>-->
<!--                      <q-icon name="image" color="warning" />-->
<!--                    </q-item-section>-->
<!--                    <q-item-section>-->
<!--                      <q-item-label>Actualizar imagen</q-item-label>-->
<!--                    </q-item-section>-->
<!--                  </q-item>-->
<!--                  <q-item clickable v-close-popup @click="openBackgroundDialog(props.row)">-->
<!--                    <q-item-section avatar>-->
<!--                      <q-icon name="wallpaper" color="deep-orange" />-->
<!--                    </q-item-section>-->
<!--                    <q-item-section>-->
<!--                      <q-item-label>Actualizar background</q-item-label>-->
<!--                    </q-item-section>-->
<!--                  </q-item>-->
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
        <q-card-section class="row items-center q-py-sm">
          <div class="text-h6">{{ isEdit ? 'Editar pelicula web' : 'Nueva pelicula web' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="q-pa-sm">
          <q-form @submit.prevent="saveRow">
            <div class="row q-col-gutter-xs">

              <!-- Campos del formulario -->
              <div class="col-12 col-md-9">
                <div class="row q-col-gutter-xs">
                  <div class="col-12"><q-input dense outlined label="Titulo" v-model="form.titulo" :rules="[required]" /></div>

                  <div class="col-12 col-sm-4 row items-center">
                    <q-btn-toggle
                      v-model="form.tipo"
                      spread
                      no-caps
                      unelevated
                      class="full-width"
                      style="border: 1px solid #c2c2c2"
                      toggle-color="primary"
                      :options="[
                        { label: 'Pelicula', value: 'pelicula', icon: 'movie' },
                        { label: 'Proximo', value: 'proximo', icon: 'event' }
                      ]"
                    />
                  </div>
                  <div class="col-12 col-sm-4 row items-center">
                    <q-toggle
                      v-model="form.estado"
                      true-value="ACTIVO"
                      false-value="INACTIVO"
                      color="positive"
                      :label="form.estado === 'ACTIVO' ? 'Activo (se ve en la web)' : 'Inactivo (oculta en la web)'"
                    />
                  </div>
                  <div class="col-12 col-sm-4">
                    <q-select dense outlined label="Carrusel" v-model="form.carrusel_tipo"
                      :options="[
                        { label: 'Ninguno', value: 'ninguno' },
                        { label: 'Carrusel 1 (principal)', value: 'carrusel_1' },
                        { label: 'Carrusel 2 (secundario)', value: 'carrusel_2' },
                        { label: 'Ambos', value: 'ambos' }
                      ]" emit-value map-options />
                  </div>

                  <div class="col-6 col-sm-3"><q-input dense outlined label="Fecha Estreno" v-model="form.fecha_estreno" type="date" /></div>
                  <div class="col-6 col-sm-3"><q-input dense outlined label="Duracion" v-model="form.duracion" /></div>
                  <div class="col-6 col-sm-3"><q-input dense outlined label="Clasificacion" v-model="form.clasificacion" /></div>
                  <div class="col-6 col-sm-3"><q-input dense outlined label="Descuento (%)" v-model.number="form.descuento" type="number" min="0" max="100" step="0.1" /></div>

                  <div class="col-12 col-sm-6"><q-input dense outlined label="Genero" v-model="form.genero" /></div>
                  <div class="col-12 col-sm-6"><q-input dense outlined label="URL YouTube" v-model="form.url_video_youtube" /></div>

                  <div class="col-12">
                    <q-checkbox v-model="form.venta_online" label="Venta online (si se desmarca, la pelicula se ve en la web pero solo se vende en ventanilla)" />
                  </div>

                  <div class="col-12"><q-input dense outlined type="textarea" rows="3" label="Descripcion" v-model="form.descripcion" /></div>

                  <div class="col-12">
                    <q-expansion-item dense switch-toggle-side icon="tune" label="Más datos" caption="TMDB, puntajes, actores, estudio, país..." header-class="text-grey-8">
                      <div class="row q-col-gutter-xs q-pt-xs">
                        <div class="col-4"><q-input dense outlined label="TMDB ID" v-model.number="form.tmdb_id" type="number" /></div>
                        <div class="col-4"><q-input dense outlined label="IMDb ID" v-model="form.imdb_id" /></div>
                        <div class="col-4"><q-input dense outlined label="Anio" v-model.number="form.anio" type="number" /></div>

                        <div class="col-4"><q-input dense outlined label="Estudio" v-model="form.studio_nombre" /></div>
                        <div class="col-4"><q-input dense outlined label="Pais" v-model="form.pais" /></div>
                        <div class="col-4"><q-input dense outlined label="Idioma" v-model="form.idioma" /></div>

                        <div class="col-3"><q-input dense outlined label="Puntaje Web" v-model.number="form.puntaje_web" type="number" step="0.001" /></div>
                        <div class="col-3"><q-input dense outlined label="Puntaje Tomatoes" v-model.number="form.puntaje_tomatoes" type="number" step="0.1" /></div>
                        <div class="col-3"><q-input dense outlined label="Puntaje IBM" v-model.number="form.puntaje_ibm" type="number" step="0.001" /></div>
                        <div class="col-3"><q-input dense outlined label="Metacritic" v-model.number="form.puntaje_metacritic" type="number" step="0.1" /></div>

                        <div class="col-4"><q-input dense outlined label="Votos Total" v-model.number="form.votos_total" type="number" /></div>
                        <div class="col-4"><q-input dense outlined label="Popularidad" v-model.number="form.popularidad" type="number" step="0.001" /></div>
                        <div class="col-4"><q-input dense outlined label="Me gusta" v-model.number="form.me_gusta" type="number" /></div>

                        <div class="col-4"><q-input dense outlined label="Calidad" v-model="form.calidad" /></div>
                        <div class="col-4"><q-input dense outlined label="Bucket" v-model="form.bucket" /></div>
                        <div class="col-4"><q-input dense outlined label="Tele" v-model="form.tele" /></div>

                        <div class="col-6"><q-input dense outlined label="URL oficial" v-model="form.url_oficial" /></div>
                        <div class="col-6"><q-input dense outlined label="Tagline" v-model="form.tagline" /></div>

                        <div class="col-12"><q-input dense outlined label="Premios" v-model="form.premios" /></div>
                      </div>
                    </q-expansion-item>
                  </div>

                  <div class="col-12">
                    <q-expansion-item dense switch-toggle-side icon="groups" label="Reparto" :caption="actoresList.length + ' actores · clic o arrastra una foto para cambiarla'" header-class="text-grey-8">
                      <div class="row q-col-gutter-sm q-pt-xs">
                        <div v-for="(actor, i) in actoresList" :key="actor.key" class="col-6 col-sm-3 col-md-2">
                          <div
                            class="actor-card"
                            :class="{ 'actor-card--drag': actorDragIndex === i }"
                            @dragover.prevent="actorDragIndex = i"
                            @dragleave.prevent="actorDragIndex = null"
                            @drop.prevent="onDropActor($event, i)"
                          >
                            <div class="actor-foto" @click="elegirFotoActor(i)" title="Cambiar foto">
                              <q-img :src="actor.preview || movieImage(actor.imagen)" :ratio="1" fit="cover">
                                <template v-slot:error>
                                  <div class="absolute-full flex flex-center bg-grey-4 text-grey-7 text-h5">{{ (actor.nombre || '?').charAt(0) }}</div>
                                </template>
                              </q-img>
                              <div class="actor-foto-hover"><q-icon name="photo_camera" size="22px" /></div>
                              <q-badge v-if="actor.file" floating color="positive" rounded><q-icon name="check" size="10px" /></q-badge>
                            </div>
                            <q-input v-model="actor.nombre" dense borderless input-class="text-center text-caption" placeholder="Nombre" />
                            <q-btn class="actor-quitar" round dense flat size="xs" icon="close" color="negative" @click="actoresList.splice(i, 1)">
                              <q-tooltip>Quitar</q-tooltip>
                            </q-btn>
                          </div>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                          <div class="actor-card actor-card--nuevo">
                            <q-input v-model="nuevoActor" dense outlined placeholder="Nuevo actor" @keyup.enter="agregarActor">
                              <template v-slot:append><q-btn round dense flat icon="add" color="primary" @click="agregarActor" /></template>
                            </q-input>
                          </div>
                        </div>
                      </div>
                      <input ref="actorFileInput" type="file" accept="image/*" style="display: none" @change="onActorFileChange" />
                    </q-expansion-item>
                  </div>
                </div>
              </div>

              <!-- Panel de imagenes -->
              <div class="col-12 col-md-3 column q-gutter-xs">
                <div class="row items-center">
                  <div class="text-caption text-grey-7">Poster — arrastra para cambiar</div>
                  <q-space />
                  <q-btn flat round dense icon="open_in_new" size="xs" color="grey-7"
                    @click.stop="openImageFull(posterPreview || movieImage(form.imagen), 'Poster')"
                    title="Ver imagen completa" />
                </div>
                <div
                  class="img-drop-zone"
                  :class="{ 'img-drop-zone--active': isDraggingPoster }"
                  @dragover.prevent="isDraggingPoster = true"
                  @dragleave.prevent="isDraggingPoster = false"
                  @drop.prevent="onDropPoster"
                >
                  <q-img :src="posterPreview || movieImage(form.imagen)" fit="contain" class="img-preview" style="height: 280px"
                    @click="openImageFull(posterPreview || movieImage(form.imagen), 'Poster')" />
                  <div v-if="isDraggingPoster" class="img-drop-overlay">
                    <div class="text-center"><q-icon name="image" size="32px" /><br>Soltar imagen</div>
                  </div>
                  <div v-if="posterPreview" class="img-drop-badge">
                    <q-icon name="check_circle" color="positive" size="20px" />
                  </div>
                </div>

                <div class="row items-center q-mt-xs">
                  <div class="text-caption text-grey-7">Background — arrastra para cambiar</div>
                  <q-space />
                  <q-btn flat round dense icon="open_in_new" size="xs" color="grey-7"
                    @click.stop="openImageFull(backdropPreview || movieImage(form.backdrop_imagen), 'Background')"
                    title="Ver imagen completa" />
                </div>
                <div
                  class="img-drop-zone"
                  :class="{ 'img-drop-zone--active': isDraggingBackdrop }"
                  @dragover.prevent="isDraggingBackdrop = true"
                  @dragleave.prevent="isDraggingBackdrop = false"
                  @drop.prevent="onDropBackdrop"
                >
                  <q-img :src="backdropPreview || movieImage(form.backdrop_imagen)" fit="contain" class="img-preview" style="height: 170px"
                    @click="openImageFull(backdropPreview || movieImage(form.backdrop_imagen), 'Background')" />
                  <div v-if="isDraggingBackdrop" class="img-drop-overlay">
                    <div class="text-center"><q-icon name="wallpaper" size="32px" /><br>Soltar imagen</div>
                  </div>
                  <div v-if="backdropPreview" class="img-drop-badge">
                    <q-icon name="check_circle" color="positive" size="20px" />
                  </div>
                </div>

                <div v-if="uploadProgress > 0 && uploadProgress < 100" class="q-mt-xs">
                  <q-linear-progress :value="uploadProgress / 100" color="primary" rounded size="6px" />
                </div>

                <q-space />
                <q-btn class="full-width q-mt-sm" color="positive" icon="save" type="submit" :loading="loading" label="Guardar" />
              </div>

            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="imageDialog" persistent>
      <q-card style="min-width: 720px; max-width: 92vw">
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
              <q-img :src="imageDialogCurrent" fit="contain" class="img-preview" style="height: 260px; border-radius: 12px" />
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2 q-mb-sm">Nueva imagen</div>
              <div
                class="drop-zone rounded-borders q-pa-md text-center"
                :class="{ 'drop-zone--active': isDraggingImage }"
                @dragover.prevent="isDraggingImage = true"
                @dragleave.prevent="isDraggingImage = false"
                @drop.prevent="onDropImage"
              >
                <q-icon name="cloud_upload" size="36px" :color="isDraggingImage ? 'primary' : 'grey-5'" />
                <div class="text-grey-6 q-mt-xs q-mb-sm">Arrastra una imagen aqui o</div>
                <q-file dense outlined v-model="replaceImageFile" accept=".jpg,.jpeg,.png,.webp" label="Seleccionar archivo" @update:model-value="onReplaceSelected" />
                <q-img v-if="replaceImagePreview" fit="contain" class="q-mt-md img-preview" :src="replaceImagePreview" style="height: 180px; border-radius: 12px" />
              </div>
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
              <q-select
                dense
                outlined
                v-model="programFilterMovie"
                label="Pelicula (selecciona sus funciones)"
                :options="programMovieOptionsFiltradas"
                option-value="id"
                option-label="nombre"
                emit-value
                map-options
                use-input
                input-debounce="0"
                clearable
                @filter="filtrarPeliculasPrograma"
                @update:model-value="onProgramMovieSelected"
              >
                <template v-slot:prepend>
                  <q-avatar v-if="programMovieSeleccionada" rounded size="28px">
                    <img :src="movieImage(programMovieSeleccionada.imagen)" style="object-fit: cover" />
                  </q-avatar>
                  <q-icon v-else name="search" />
                </template>
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section avatar>
                      <q-img :src="movieImage(scope.opt.imagen)" style="width: 36px; height: 54px; border-radius: 4px" fit="cover" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.funciones }} funciones · {{ scope.opt.salas }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:no-option>
                  <q-item><q-item-section class="text-grey">Sin peliculas con funciones</q-item-section></q-item>
                </template>
              </q-select>
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
              <q-td :props="props">
                <div class="row items-center no-wrap q-gutter-sm">
                  <q-img
                    :src="movieImage(props.row.movie ? props.row.movie.imagen : null)"
                    style="width: 32px; height: 48px; border-radius: 4px"
                    fit="cover"
                  />
                  <span>{{ props.row.movie ? props.row.movie.nombre : '' }}</span>
                </div>
              </q-td>
            </template>
            <template v-slot:body-cell-sala="props">
              <q-td :props="props">{{ props.row.sala ? props.row.sala.nombre : '' }}</q-td>
            </template>
            <template v-slot:body-cell-venta_online="props">
              <q-td :props="props" auto-width>
                <q-toggle
                  dense
                  color="positive"
                  :disable="!isProgramSelected(props.row)"
                  :model-value="!programSinVenta[props.row.id]"
                  @update:model-value="(val) => setProgramVenta(props.row, val)"
                  :label="!programSinVenta[props.row.id] ? 'Web' : 'Solo boleteria'"
                >
                  <q-tooltip v-if="!isProgramSelected(props.row)">Primero selecciona la funcion</q-tooltip>
                </q-toggle>
              </q-td>
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

    <q-dialog v-model="imageViewer.open">
      <q-card class="bg-black text-white" style="max-width: 95vw; width: auto">
        <q-card-section class="row items-center q-py-xs">
          <div class="text-subtitle1">{{ imageViewer.title }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-pa-none flex flex-center">
          <img :src="imageViewer.src" style="display: block; max-width: 95vw; max-height: 85vh; object-fit: contain" />
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
  venta_online: true,
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
      isDraggingImage: false,
      isDraggingPoster: false,
      isDraggingBackdrop: false,
      imageViewer: { open: false, src: '', title: '' },
      actoresList: [],
      nuevoActor: '',
      actorFotoIndex: null,
      actorDragIndex: null,
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
      programSinVenta: {},
      programFilterMovie: null,
      programMovieSearch: '',
      programFilterSala: null,
      programFilterTarifa: null,
      columns: [
        { label: 'Opciones', field: 'opciones', name: 'opciones' },
        { label: 'Imagen', field: 'imagen', name: 'imagen' },
        { label: 'BG', field: 'backdrop_imagen', name: 'backdrop_imagen' },
        { label: 'Titulo', field: 'titulo', name: 'titulo', sortable: true },
        { label: 'Estado', field: 'estado', name: 'estado', sortable: true },
        { label: 'Venta online', field: 'venta_online', name: 'venta_online', sortable: true },
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
        { label: 'Venta online', field: 'venta_online', name: 'venta_online', align: 'left' },
      ],
    };
  },
  created() {
    this.loadRows();
  },
  computed: {
    filteredProgramRows() {
      return (this.programRows || []).filter((row) => {
        const salaName = row.sala?.nombre || '';
        const tarifa = row.price?.precio != null ? String(row.price.precio) : '';

        const byMovie = this.programFilterMovie ? row.movie_id === this.programFilterMovie : true;
        const bySala = this.programFilterSala ? salaName === this.programFilterSala : true;
        const byTarifa = this.programFilterTarifa ? tarifa === String(this.programFilterTarifa) : true;

        return byMovie && bySala && byTarifa;
      });
    },
    // Peliculas (de programacion) con funciones en la ventana, para el buscador del dialogo
    programMovieOptions() {
      const map = new Map();
      (this.programRows || []).forEach((row) => {
        if (!row.movie) return;
        const m = map.get(row.movie_id) || { id: row.movie_id, nombre: row.movie.nombre, imagen: row.movie.imagen, funciones: 0, salasSet: new Set() };
        m.funciones += 1;
        if (row.sala?.nombre) m.salasSet.add(row.sala.nombre);
        map.set(row.movie_id, m);
      });
      return [...map.values()]
        .map(({ salasSet, ...m }) => ({ ...m, salas: [...salasSet].join(', ') }))
        .sort((a, b) => a.nombre.localeCompare(b.nombre));
    },
    programMovieOptionsFiltradas() {
      const q = (this.programMovieSearch || '').toLowerCase();
      if (!q) return this.programMovieOptions;
      return this.programMovieOptions.filter((m) => m.nombre.toLowerCase().includes(q));
    },
    programMovieSeleccionada() {
      return this.programMovieOptions.find((m) => m.id === this.programFilterMovie) || null;
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
      this.actoresList = [];
      this.nuevoActor = '';
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
      this.actoresList = (row.actores || []).map((a) => this.nuevoItemActor(a.nombre, a.imagen));
      this.nuevoActor = '';
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
      this.programFilterMovie = null;
      this.programMovieSearch = '';
      this.programFilterSala = null;
      this.programFilterTarifa = null;
      await this.loadProgramRows();
      const selectedIds = (row.programas || []).map((p) => p.id);
      this.selectedProgramRows = this.programRows.filter((p) => selectedIds.includes(p.id));
      // Funciones vinculadas marcadas como "solo boleteria"
      this.programSinVenta = {};
      (row.programas || []).forEach((p) => {
        if (p.pivot && !p.pivot.venta_online) this.programSinVenta[p.id] = true;
      });

      // Preseleccionar la pelicula: la de las funciones ya vinculadas, o la que tenga el mismo titulo
      const vinculada = this.selectedProgramRows[0]?.movie_id;
      const titulo = (row.titulo || '').trim().toLowerCase();
      const porTitulo = this.programMovieOptions.find((m) => (m.nombre || '').trim().toLowerCase() === titulo);
      this.programFilterMovie = vinculada || porTitulo?.id || null;
    },
    isProgramSelected(row) {
      return this.selectedProgramRows.some((p) => p.id === row.id);
    },
    setProgramVenta(row, habilitada) {
      if (habilitada) delete this.programSinVenta[row.id];
      else this.programSinVenta[row.id] = true;
    },
    filtrarPeliculasPrograma(val, update) {
      update(() => {
        this.programMovieSearch = val;
      });
    },
    onProgramMovieSelected(movieId) {
      this.programMovieSearch = '';
      if (!movieId) return;
      // Al elegir la pelicula se seleccionan todas sus funciones (respetando filtros de sala/tarifa)
      this.selectedProgramRows = [...this.filteredProgramRows];
    },
    openImageFull(src, title = '') {
      this.imageViewer = { open: true, src, title };
    },
    onDropPoster(event) {
      this.isDraggingPoster = false;
      const file = event.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        this.posterFile = file;
        this.posterPreview = URL.createObjectURL(file);
      }
    },
    onDropBackdrop(event) {
      this.isDraggingBackdrop = false;
      const file = event.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        this.backdropFile = file;
        this.backdropPreview = URL.createObjectURL(file);
      }
    },
    onReplaceSelected(file) {
      this.replaceImagePreview = file ? URL.createObjectURL(file) : '';
    },
    onDropImage(event) {
      this.isDraggingImage = false;
      const file = event.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        this.replaceImageFile = file;
        this.replaceImagePreview = URL.createObjectURL(file);
      }
    },
    async uploadImage(file, maxWidth = 1280) {
      if (!file) return null;
      const fd = new FormData();
      fd.append('file', file);
      fd.append('webp', 1);
      fd.append('max_width', maxWidth);
      const res = await this.$api.post('upload', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (event) => {
          this.uploadProgress = Math.round((event.loaded / event.total) * 100);
        },
      });
      this.uploadProgress = 0;
      return res.data;
    },
    nuevoItemActor(nombre, imagen = null) {
      return { key: Math.random().toString(36).slice(2), nombre, imagen, file: null, preview: '' };
    },
    agregarActor() {
      // Permite pegar varios separados por coma
      const nombres = (this.nuevoActor || '').split(',').map((x) => x.trim()).filter(Boolean);
      nombres.forEach((n) => this.actoresList.push(this.nuevoItemActor(n)));
      this.nuevoActor = '';
    },
    elegirFotoActor(i) {
      this.actorFotoIndex = i;
      this.$refs.actorFileInput.value = '';
      this.$refs.actorFileInput.click();
    },
    setFotoActor(i, file) {
      if (!file || !file.type.startsWith('image/') || !this.actoresList[i]) return;
      this.actoresList[i].file = file;
      this.actoresList[i].preview = URL.createObjectURL(file);
    },
    onActorFileChange(event) {
      this.setFotoActor(this.actorFotoIndex, event.target.files[0]);
      this.actorFotoIndex = null;
    },
    onDropActor(event, i) {
      this.actorDragIndex = null;
      this.setFotoActor(i, event.dataTransfer.files[0]);
    },
    async buildActors() {
      // Sube solo las fotos nuevas; las demas conservan su imagen actual
      const actores = [];
      for (const a of this.actoresList) {
        const nombre = (a.nombre || '').trim();
        if (!nombre) continue;
        const imagen = a.file ? await this.uploadImage(a.file, 300) : a.imagen;
        actores.push({ nombre, imagen: imagen || null });
      }
      return actores;
    },
    async saveRow() {
      try {
        this.loading = true;
        const uploadedPoster = this.posterFile ? await this.uploadImage(this.posterFile, 780) : null;
        const uploadedBackdrop = this.backdropFile ? await this.uploadImage(this.backdropFile, 1280) : null;
        const payload = {
          ...this.form,
          imagen: uploadedPoster || this.form.imagen,
          backdrop_imagen: uploadedBackdrop || this.form.backdrop_imagen,
          trailer_youtube: this.form.url_video_youtube || this.form.trailer_youtube,
          url_video_youtube: this.form.url_video_youtube || this.form.trailer_youtube,
          actores: await this.buildActors(),
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
    async toggleEstado(row, activo) {
      try {
        const res = await this.$api.post('webMovie/' + row.id + '/estado', { estado: activo ? 'ACTIVO' : 'INACTIVO' });
        const i = this.rows.findIndex((x) => x.id === row.id);
        if (i !== -1) this.rows[i] = res.data;
        this.$q.notify({
          color: activo ? 'positive' : 'warning',
          icon: activo ? 'visibility' : 'visibility_off',
          message: (activo ? 'Activa en la web: ' : 'Oculta en la web: ') + row.titulo,
        });
      } catch (e) {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'No se pudo cambiar el estado' });
      }
    },
    async toggleVentaOnline(row, value) {
      try {
        const res = await this.$api.post('webMovie/' + row.id + '/ventaOnline', { venta_online: value });
        const i = this.rows.findIndex((x) => x.id === row.id);
        if (i !== -1) this.rows[i] = res.data;
        this.$q.notify({
          color: value ? 'positive' : 'warning',
          icon: value ? 'shopping_cart' : 'storefront',
          message: value ? 'Venta online habilitada: ' + row.titulo : 'Solo ventanilla: ' + row.titulo,
        });
      } catch (e) {
        this.$q.notify({ color: 'negative', icon: 'error', message: e?.response?.data?.message || 'No se pudo cambiar la venta online' });
      }
    },
    async saveImageOnly() {
      if (!this.replaceImageFile) {
        this.$q.notify({ color: 'warning', message: 'Selecciona una imagen primero' });
        return;
      }

      try {
        this.loading = true;
        const uploaded = await this.uploadImage(this.replaceImageFile, this.imageDialogMode === 'background' ? 1280 : 780);
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
          sin_venta_online_ids: programaIds.filter((id) => this.programSinVenta[id]),
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
.drop-zone {
  border: 2px dashed #ccc;
  transition: border-color 0.2s, background 0.2s;
  min-height: 120px;
}
.drop-zone--active {
  border-color: #1976d2;
  background: rgba(25, 118, 210, 0.06);
}
.img-drop-zone {
  position: relative;
  border: 2px dashed transparent;
  border-radius: 8px;
  transition: border-color 0.2s;
  overflow: hidden;
  cursor: pointer;
}
.actor-card {
  position: relative;
  border: 2px dashed transparent;
  border-radius: 10px;
  padding: 6px;
  transition: border-color 0.2s, background 0.2s;
}
.actor-card:hover {
  background: rgba(0, 0, 0, 0.03);
}
.actor-card--drag {
  border-color: #1976d2;
  background: rgba(25, 118, 210, 0.06);
}
.actor-card--nuevo {
  height: 100%;
  display: flex;
  align-items: center;
}
.actor-foto {
  position: relative;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  width: 80%;
  margin: 0 auto;
}
.actor-foto-hover {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.45);
  color: white;
  opacity: 0;
  transition: opacity 0.2s;
}
.actor-foto:hover .actor-foto-hover {
  opacity: 1;
}
.actor-quitar {
  position: absolute;
  top: 2px;
  right: 2px;
  opacity: 0;
  transition: opacity 0.2s;
}
.actor-card:hover .actor-quitar {
  opacity: 1;
}
.img-preview {
  border-radius: 6px;
  background: #1d1d1d;
}
.img-drop-zone:hover {
  border-color: #9e9e9e;
}
.img-drop-zone--active {
  border-color: #1976d2;
}
.img-drop-overlay {
  position: absolute;
  inset: 0;
  background: rgba(25, 118, 210, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  font-weight: 600;
  z-index: 1;
}
.img-drop-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: white;
  border-radius: 50%;
  z-index: 2;
}
</style>
