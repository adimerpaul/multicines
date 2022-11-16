<template>
  <q-page>
    <q-table :rows="users" :columns="userColums" :filter="search">
      <template v-slot:top-right>
        <q-toolbar>
          <q-btn flat icon="add_circle_outline" @click="showAddUserDialog = true;userCrear=true" />
          <q-input v-model="search"  outlined  dense placeholder="Buscar..." />
        </q-toolbar>
      </template>
      <template v-slot:body-cell-role="props">
        <q-td :props="props" auto-width >
          <q-badge dense text-color="white" :color="props.row.role=='ADMINISTRADOR'?'red':props.row.role=='INSCRIPCION'?'green':props.row.role=='ACREDITACION'?'blue':'cyan'">{{props.row.role.substring(0,4)}}</q-badge>
        </q-td>
      </template>
      <template v-slot:body-cell-option="props">
        <q-td :props="props" auto-width >
          <q-btn flat dense icon="o_edit" @click="userEdit(props.row)" />
          <q-btn flat dense v-if="props.row.id!=1" icon="o_delete" @click="userDelete(props.row)" />
          <q-btn flat dense v-if="props.row.id!=1" icon="o_key" @click="updatePassword(props.row)" />
        </q-td>
      </template>
    </q-table>
    <q-dialog v-model="showAddUserDialog" >
      <q-card style="width: 700px;max-width: 85vw">
        <q-card-section class="row items-center">
          <div class="text-h6">Agregar Usuario</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="userCreate">
            <q-input v-model="user.name" hint="" required outlined label="Nombre" />
            <q-input v-model="user.email" hint="" required outlined label="Email" />
            <q-input v-model="user.password" type="password" hint="" required outlined label="Password" />
            <q-select v-model="user.role" hint="" required outlined label="Rol" :options="roles" />
            <q-btn :loading="loading" type="submit" color="primary" icon="add_circle_outline" label="Guardar" class="full-width" />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="showUpdateUserDialog" >
      <q-card style="width: 700px;max-width: 85vw">
        <q-card-section class="row items-center">
          <div class="text-h6">Agregar Usuario</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="userUpdate">
            <q-input v-model="user.name" hint="" required outlined label="Nombre" />
            <q-input v-model="user.email" hint="" required outlined label="Email" />
            <q-select v-model="user.role" hint="" required outlined label="Rol" :options="roles" />
            <q-btn :loading="loading" type="submit" color="primary" icon="add_circle_outline" label="Guardar" class="full-width" />
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: `User`,
  data () {
    return {
      roles: [
        'INSCRIPCION',
        'ACREDITACION',
        'REFRIGERIO',
        'ADMINISTRADOR',
      ],
      showAddUserDialog: false,
      showUpdateUserDialog: false,
      search: '',
      users: [],
      user:{},
      loading: false,
      userCrear: true,
      userColums:[
        {name: 'option', field: 'option', label: 'Opciones', align: 'left', sortable: true},
        {name: 'role', field: 'role', label: 'Rol', align: 'left', sortable: true},
        {name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true},
        {name: 'name', label: 'Nombre', field: 'name', align: 'left', sortable: true},
        {name: 'email', label: 'Email', field: 'email', align: 'left', sortable: true},
      ]
    }
  },
  methods:{
    userUpdate(){
      this.loading = true
      this.$api.put(`user/${this.user.id}`,this.user)
        .then(res=>{
          this.loading = false
          this.showUpdateUserDialog = false
          this.$q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'check_circle',
            message: 'Usuario actualizado'
          })
          this.user = {}
          this.usersGet()
        })
        .catch(err=>{
          this.loading = false
          this.$q.notify({
            color: 'red-4',
            textColor: 'white',
            icon: 'error',
            message: 'Error al actualizar usuario'
          })
        })
    },
    usersGet(){
      this.$q.loading.show()
      this.$api.get('user').then(response => {
        this.users = response.data
        this.$q.loading.hide()
      })
    },
    userCreate(){
      if (this.userCrear){
        this.loading = true
        this.$api.post('user', this.user).then(response => {
          this.usersGet()
          this.showAddUserDialog = false
          this.user = {}
          this.loading = false
        }).catch(error => {
          this.loading = false
          this.$q.notify({
            color: 'negative',
            message: error.response.data.message,
            icon: 'report_problem',
            position: 'top'
          })
        })
      }else{
        this.loading = true
        this.$api.put('user/'+this.user.id, this.user).then(response => {
          this.usersGet()
          this.showAddUserDialog = false
          this.user = {}
          this.loading = false
        }).catch(error => {
          this.loading = false
          this.$q.notify({
            color: 'negative',
            message: error.response.data.message,
            icon: 'report_problem',
            position: 'top'
          })
        })
      }
    },
    userEdit(user){
      this.user = user
      this.userCrear = false
      this.showUpdateUserDialog = true
    },
    updatePassword(user){
      this.$q.dialog({
        title: 'Cambiar contraseña',
        message: 'Ingrese la nueva contraseña',
        prompt: {
          model: '',
          type: 'password'
        },
        cancel: true,
        persistent: true
      }).onOk(data => {
        this.$api.put(`updatePassword/${user.id}`,{password:data})
          .then(res=>{
            this.$q.notify({
              color: 'green-4',
              textColor: 'white',
              icon: 'check_circle',
              position: 'top',
              message: 'Contraseña actualizada'
            })
          })
          .catch(err=>{
            this.$q.notify({
              color: 'red-4',
              textColor: 'white',
              icon: 'error',
              position: 'top',
              message: err.response.data.message
            })
          })
      }).onCancel(() => {
        console.log('Cancel')
      }).onDismiss(() => {
        console.log('Dismissed')
      })
    },
    userDelete(user){
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Está seguro de eliminar el usuario?',
        ok: 'Si',
        cancel: 'No'
      }).onOk(() => {
        this.$q.loading.show()
        this.$api.delete('user/'+user.id).then(response => {
          this.usersGet()
        })
      })
    }
  },
  created() {
    this.usersGet()
  }
}
</script>

<style scoped>
</style>
