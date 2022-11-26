<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-2">
    <q-page-container>
      <q-page>
        <div class="row">
          <div class="col-1 col-sm-4"></div>
          <div class="col-10 col-sm-4">
            <div class="text-subtitle1 text-center q-pt-xs text-grey">CUENTA</div>
            <div class="text-h2 text-center q-pa-xs text-black text-bold">Tu Cuenta</div>
            <q-card flat bordered>
              <q-card-section >
                <q-form class="q-pa-lg" @submit.prevent="login">
                  <div class="row">
                    <div class="col-12">
                      <q-input outlined v-model="email" label="Email" type="email" required />
                    </div>
                    <div class="col-12 q-pt-lg">
                      <q-input outlined v-model="password" label="Contraseña" :type="typePassword?'password':'text'" required >
                        <template v-slot:append>
                          <q-icon @click="typePassword=!typePassword" :name="typePassword?'visibility':'visibility_off'" />
                        </template>
                      </q-input>
                    </div>
                    <div class="col-12 q-py-xs">
                      <q-checkbox v-model="remember" class="grey" label="Recuérdame" size="30px" />
                    </div>
                    <div class="col-12 q-pt-xs">
                      <q-btn size="22px" :loading="loading" class="full-width bold" color="primary" label="Iniciar Sesión" type="submit" no-caps />
                    </div>
                    <div class="col-12 q-pt-xs">
                      <!--                    <q-btn to="signup" size="22px" outline class="full-width bold" color="primary" label="Registrate" type="submit" no-caps />-->
                    </div>
                    <div class="col-12 text-center q-py-none">
                      <a href="" class="text-blue-8 ">¿Olvidaste tu contraseña?</a>
                    </div>
                    <!--                    <div class="col-12 text-center q-py-none text-caption">-->
                    <!--                      <div class="linea"><span>O INICIAR SESIÓN CON</span></div>-->
                    <!--                    </div>-->
                    <!--                    <div class="col-6 q-pt-md q-pr-xs">-->
                    <!--                      <q-btn outline icon="fa-brands fa-facebook" color="primary" class="full-width "/>-->
                    <!--                    </div>-->
                    <!--                    <div class="col-12 q-pt-md q-pl-xs">-->
                    <!--                      <q-btn outline  icon="fa-brands fa-google" color="primary" class="full-width "/>-->
                    <!--                    </div>-->
                  </div>
                </q-form>
              </q-card-section>
            </q-card>
            <div class="text-subtitle1 text-center text-caption q-pt-lg text-grey">AL INICIAR SESIÓN, USTED ACEPTA LOS
              <a href="" class="text-blue-8 ">TÉRMINOS DEL SERVICIO</a> Y <a href="" class="text-blue-8 ">LA POLÍTICA DE PRIVACIDAD</a>
            </div>
          </div>
          <div class="col-1 col-sm-4"></div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import {globalStore} from "stores/globalStore";
// import { Providers} from 'universal-social-auth'
export default {
  name: `Login`,
  data () {
    return {
      email: '',
      password: '',
      remember:false,
      typePassword:true,
      loading:false,
      store:globalStore()
    }
  },
  mounted() {
    if (this.store.isLoggedIn){
      this.$router.push('/')
    }
  },
  methods:{
    // useAuthProvider (provider, proData) {
    //   const pro = proData
    //   const ProData = pro || Providers[provider]
    //   this.$q.loading.show()
    //   this.$Oauth.authenticate(provider, ProData).then((response) => {
    //     if (response.code) {
    //       this.$api.post('sociallogin/'+provider,response).then(res => {
    //         // console.log(response.data)
    //         this.$q.loading.hide()
    //         this.$q.notify({
    //           message: 'Bienvenido',
    //           color: 'positive',
    //           icon: 'check_circle',
    //           position: 'top'
    //         })
    //         this.$router.push('/')
    //         this.store.user=res.data.user
    //         this.store.isLoggedIn=true
    //         this.$api.defaults.headers.common['Authorization'] = 'Bearer '+res.data.token
    //         localStorage.setItem('tokenMulti',res.data.token)
    //       }).catch(err => {
    //         console.log({err:err})
    //       }).finally(() => {
    //         this.$q.loading.hide()
    //       })
    //     }else{
    //       this.$q.loading.hide()
    //     }
    //   }).catch((err) => {
    //     console.log(err)
    //   }).finally(() => {
    //     // this.$q.loading.hide()
    //   })
    // },
    login(){
      this.loading=true
      this.$api.post('login', {
        email: this.email,
        password: this.password,
      }).then(res => {
        this.$q.notify({
          message: 'Bienvenido',
          color: 'positive',
          icon: 'check_circle',
          position: 'top'
        })
        console.log(res.data)
        this.$router.push('/')
        this.store.user=res.data.user
        this.store.isLoggedIn=true
        res.data.user.permisos.forEach(r => {
            if(r.id==1) this.store.booluser=true
            if(r.id==2) this.store.boolcuis=true
            if(r.id==3) this.store.boolsincr=true
            if(r.id==4) this.store.boolcufd=true
            if(r.id==5) this.store.boolevento=true
            if(r.id==6) this.store.boolmovie=true
            if(r.id==7) this.store.booldistrib=true
            if(r.id==8) this.store.boolsala=true
            if(r.id==9) this.store.booltarifa=true
            if(r.id==10) this.store.boolrubro=true
            if(r.id==11) this.store.boolproducto=true
            if(r.id==12) this.store.boolprogram=true
            if(r.id==13) this.store.boolboleteria=true
            if(r.id==14) this.store.boollistbol=true
            if(r.id==15) this.store.boolcandy=true
            if(r.id==16) this.store.boollistcandy=true
            if(r.id==17) this.store.boolcajabol=true
            if(r.id==18) this.store.boolcajacandy=true
            if(r.id==19) this.store.boolalquiler=true
            if(r.id==20) this.store.boolcliente=true
            if(r.id==21) this.store.boolcortesia=true
        });
        this.$api.defaults.headers.common['Authorization'] = 'Bearer '+res.data.token
        localStorage.setItem('tokenMulti',res.data.token)
      }).catch(error => {
        console.log(error)
        this.$q.notify({
          message: error.response.data.message,
          color: 'negative',
          position: 'top',
          timeout: 2000
        })
      }).finally(()=>{
        this.loading=false
      })
    }
  }
}
</script>

<style scoped>
div.linea {
  position: relative;
  z-index: 1;
}
div.linea:before {
  border-top: 1px solid grey;
  content: "";
  margin: 0 auto;
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  bottom: 0;
  width: 95%;
  z-index: -1;
}
div.linea span {
  background: #fff;
  padding: 0 10px;
  color: grey;
}
</style>
