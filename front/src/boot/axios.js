import { boot } from 'quasar/wrappers'
import axios from 'axios'
import {globalStore} from "stores/globalStore";
// Be careful when using SSR for cross-request state pollution
// due to creating a Singleton instance here;
// If any client changes this (global) instance, it might be a
// good idea to move this instance creation inside of the
// "export default () => {}" function below (which runs individually
// for each client)
const api = axios.create({ baseURL: process.env.API })

export default boot(({ app,router }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  const token = localStorage.getItem('tokenMulti')
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
    api.post('me').then((response) => {
      console.log(response.data)
      globalStore().user = response.data
      globalStore().isLoggedIn = true
      response.data.permisos.forEach(r => {
          if(r.id==1) globalStore().booluser=true
          if(r.id==2) globalStore().boolcuis=true
          if(r.id==3) globalStore().boolsincr=true
          if(r.id==4) globalStore().boolcufd=true
          if(r.id==5) globalStore().boolevento=true
          if(r.id==6) globalStore().boolmovie=true
          if(r.id==7) globalStore().booldistrib=true
          if(r.id==8) globalStore().boolsala=true
          if(r.id==9) globalStore().booltarifa=true
          if(r.id==10) globalStore().boolrubro=true
          if(r.id==11) globalStore().boolproducto=true
          if(r.id==12) globalStore().boolprogram=true
          if(r.id==13) globalStore().boolboleteria=true
          if(r.id==14) globalStore().boollistbol=true
          if(r.id==15) globalStore().boolcandy=true
          if(r.id==16) globalStore().boollistcandy=true
          if(r.id==17) globalStore().boolcajabol=true
          if(r.id==18) globalStore().boolcajacandy=true
          if(r.id==19) globalStore().boolalquiler=true
          if(r.id==20) globalStore().boolcliente=true
          if(r.id==21) globalStore().boolcortesia=true
      });
    }).catch((error) => {
      app.config.globalProperties.$api.defaults.headers.common['Authorization']=''
      globalStore().user={}
      localStorage.removeItem('tokenMulti')
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
      router.push('/login')
    })
  }else {
    router.push('/login')
    globalStore().user={}
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
    localStorage.removeItem('tokenMulti')
    globalStore().isLoggedIn=false
  }
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
