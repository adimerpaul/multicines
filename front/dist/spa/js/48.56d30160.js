"use strict";(globalThis["webpackChunkfront"]=globalThis["webpackChunkfront"]||[]).push([[48],{8143:(e,o,a)=>{a.r(o),a.d(o,{default:()=>C});var l=a(9835),t=a(6970);const n={class:"text-subtitle2 text-red"},r=(0,l._)("b",null,"Usuario:",-1);function s(e,o,a,s,i,d){const c=(0,l.up)("q-btn"),p=(0,l.up)("q-toolbar-title"),u=(0,l.up)("q-chip"),b=(0,l.up)("q-toolbar"),x=(0,l.up)("q-header"),k=(0,l.up)("q-img"),g=(0,l.up)("q-item-label"),w=(0,l.up)("q-expansion-item"),f=(0,l.up)("q-list"),_=(0,l.up)("q-drawer"),h=(0,l.up)("router-view"),m=(0,l.up)("q-page-container"),y=(0,l.up)("q-layout");return(0,l.wg)(),(0,l.j4)(y,{view:"lHh Lpr lFf"},{default:(0,l.w5)((()=>[(0,l.Wm)(x,{elevated:""},{default:(0,l.w5)((()=>[(0,l.Wm)(b,null,{default:(0,l.w5)((()=>[(0,l.Wm)(c,{flat:"",dense:"",round:"",icon:"menu","aria-label":"Menu",onClick:d.toggleLeftDrawer},null,8,["onClick"]),(0,l.Wm)(p,{class:"text-h4 text-bold"},{default:(0,l.w5)((()=>[(0,l.Uk)((0,t.zw)(i.store.user.name)+" ",1),(0,l._)("span",n,(0,t.zw)(i.store.user.email),1)])),_:1}),(0,l._)("div",null,[0!=i.store.eventNumber?((0,l.wg)(),(0,l.j4)(u,{key:0,color:"red","text-color":"white",icon:"warning_amber",label:i.store.eventNumber+" Facturas no enviadas"},null,8,["label"])):(0,l.kq)("",!0),r,(0,l.Uk)((0,t.zw)(i.store.user.name)+" ",1),(0,l.Wm)(c,{flat:"",dense:"",round:"",icon:"exit_to_app","aria-label":"Menu",onClick:d.logout},null,8,["onClick"])])])),_:1})])),_:1}),(0,l.Wm)(_,{modelValue:i.leftDrawerOpen,"onUpdate:modelValue":o[0]||(o[0]=e=>i.leftDrawerOpen=e),"show-if-above":"",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(f,{bordered:"",class:"rounded-borders"},{default:(0,l.w5)((()=>[(0,l.Wm)(g,{header:"",class:"text-center q-pa-none q-ma-none",style:{background:"#770050"}},{default:(0,l.w5)((()=>[(0,l.Wm)(k,{src:"logo.png",width:"150px"})])),_:1}),(0,l.Wm)(w,{dense:"",exact:"","expand-separator":"",icon:"o_home",label:"Principal","default-opened":"",to:"/","expand-icon":"null"}),i.store.booluser?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","expand-separator":"",icon:"o_people",label:"Usuarios",to:"usuarios","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolcuis||i.store.boolsincr||i.store.boolcufd||i.store.boolevento?((0,l.wg)(),(0,l.j4)(w,{key:1,"expand-separator":"",dense:"",exact:"",icon:"o_engineering",label:"Siat"},{default:(0,l.w5)((()=>[i.store.boolcuis?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_psychology",label:"Cuis","default-opened":"",to:"cuis","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolsincr?((0,l.wg)(),(0,l.j4)(w,{key:1,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_countertops",label:"sincronizacion","default-opened":"",to:"sincronizacion","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolcufd?((0,l.wg)(),(0,l.j4)(w,{key:2,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"link",label:"Cufd","default-opened":"",to:"cufd","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolevento?((0,l.wg)(),(0,l.j4)(w,{key:3,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"list",label:"Evento significativo","default-opened":"",to:"eventoSignificativo","expand-icon":"null"})):(0,l.kq)("",!0)])),_:1})):(0,l.kq)("",!0),i.store.boolmovie||i.store.booldistrib?((0,l.wg)(),(0,l.j4)(w,{key:2,"expand-separator":"",dense:"",exact:"",icon:"o_movie_filter",label:"Peliculas"},{default:(0,l.w5)((()=>[i.store.boolmovie?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_movie",label:"Peliculas","default-opened":"",to:"peliculas","expand-icon":"null"})):(0,l.kq)("",!0),i.store.booldistrib?((0,l.wg)(),(0,l.j4)(w,{key:1,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_cast_for_education",label:"Distribuidores","default-opened":"",to:"distribuidores","expand-icon":"null"})):(0,l.kq)("",!0)])),_:1})):(0,l.kq)("",!0),i.store.boolsala?((0,l.wg)(),(0,l.j4)(w,{key:3,dense:"",exact:"","expand-separator":"",icon:"o_living",label:"Salas",to:"salas","expand-icon":"null"})):(0,l.kq)("",!0),i.store.booltarifa?((0,l.wg)(),(0,l.j4)(w,{key:4,dense:"",exact:"","expand-separator":"",icon:"o_price_change",label:"Tarifas",to:"tarifas","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolrubro?((0,l.wg)(),(0,l.j4)(w,{key:5,dense:"",exact:"","expand-separator":"",icon:"format_list_bulleted",label:"Rubro",to:"rubro","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolproducto?((0,l.wg)(),(0,l.j4)(w,{key:6,dense:"",exact:"","expand-separator":"",icon:"receipt_long",label:"Producto",to:"productos","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolprogram?((0,l.wg)(),(0,l.j4)(w,{key:7,dense:"",exact:"","expand-separator":"",icon:"calendar_month",label:"Programación",to:"programa","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolboleteria||i.store.boollistbol?((0,l.wg)(),(0,l.j4)(w,{key:8,"expand-separator":"",dense:"",exact:"",icon:"o_local_activity",label:"Venta Boleteria"},{default:(0,l.w5)((()=>[i.store.boolboleteria?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_local_activity",label:"Venta de boletos","default-opened":"",to:"sale","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boollistbol?((0,l.wg)(),(0,l.j4)(w,{key:1,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_cast_for_education",label:"Listado de ventas","default-opened":"",to:"listaVenta","expand-icon":"null"})):(0,l.kq)("",!0)])),_:1})):(0,l.kq)("",!0),i.store.boolcandy||i.store.boollistcandy?((0,l.wg)(),(0,l.j4)(w,{key:9,"expand-separator":"",dense:"",exact:"",icon:"o_store",label:"Candy Bar"},{default:(0,l.w5)((()=>[i.store.boolcandy?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_store",label:"Venta Candy Bar","default-opened":"",to:"candy","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boollistcandy?((0,l.wg)(),(0,l.j4)(w,{key:1,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_cast_for_education",label:"Listado de ventas","default-opened":"",to:"listaVentaCandy","expand-icon":"null"})):(0,l.kq)("",!0)])),_:1})):(0,l.kq)("",!0),i.store.boolcajabol||i.store.boolcajacandy?((0,l.wg)(),(0,l.j4)(w,{key:10,"expand-separator":"",dense:"",exact:"",icon:"o_store",label:"Reporte Caja"},{default:(0,l.w5)((()=>[i.store.boolcajabol?((0,l.wg)(),(0,l.j4)(w,{key:0,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_store",label:"Caja Boleteria","default-opened":"",to:"cajaboleteria","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolcajacandy?((0,l.wg)(),(0,l.j4)(w,{key:1,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_store",label:"Caja Candy","default-opened":"",to:"cajacandy","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolreporte?((0,l.wg)(),(0,l.j4)(w,{key:2,dense:"",exact:"","header-inset-level":1,"expand-separator":"",icon:"o_movie",label:"Reporte Funcion","default-opened":"",to:"reportefuncion","expand-icon":"null"})):(0,l.kq)("",!0)])),_:1})):(0,l.kq)("",!0),i.store.boolalquiler?((0,l.wg)(),(0,l.j4)(w,{key:11,dense:"",exact:"","expand-separator":"",icon:"o_home_work",label:"Factura de Alquiler ",to:"rental","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolcliente?((0,l.wg)(),(0,l.j4)(w,{key:12,dense:"",exact:"","expand-separator":"",icon:"o_people",label:"Clientes",to:"cliente","expand-icon":"null"})):(0,l.kq)("",!0),i.store.boolcortesia?((0,l.wg)(),(0,l.j4)(w,{key:13,dense:"",exact:"","expand-separator":"",icon:"o_book_online",label:"Cortesia",to:"cortesia","expand-icon":"null"})):(0,l.kq)("",!0),(0,l.Wm)(w,{dense:"",exact:"","expand-separator":"",icon:"o_description",label:"factura",to:"factura","expand-icon":"null"})])),_:1})])),_:1},8,["modelValue"]),(0,l.Wm)(m,null,{default:(0,l.w5)((()=>[(0,l.Wm)(h)])),_:1})])),_:1})}var i=a(3285);const d={name:"MainLayout",data(){return{leftDrawerOpen:!1,store:(0,i.c)()}},created(){this.eventSearch()},methods:{logout(){this.$q.dialog({title:"Cerrar sesión",message:"¿Está seguro que desea cerrar sesión?",cancel:!0,persistent:!0}).onOk((()=>{this.$q.loading.show(),this.$api.post("logout").then((()=>{(0,i.c)().user={},localStorage.removeItem("tokenMulti"),(0,i.c)().isLoggedIn=!1,this.$router.push("/login"),this.$q.loading.hide(),(0,i.c)().isLoggedIn=!1,(0,i.c)().booluser=!1,(0,i.c)().boolcuis=!1,(0,i.c)().boolsincr=!1,(0,i.c)().boolcufd=!1,(0,i.c)().boolevento=!1,(0,i.c)().boolmovie=!1,(0,i.c)().booldistrib=!1,(0,i.c)().boolsala=!1,(0,i.c)().booltarifa=!1,(0,i.c)().boolrubro=!1,(0,i.c)().boolproducto=!1,(0,i.c)().boolprogram=!1,(0,i.c)().boolboleteria=!1,(0,i.c)().boollistbol=!1,(0,i.c)().boolcandy=!1,(0,i.c)().boollistcandy=!1,(0,i.c)().boolcajabol=!1,(0,i.c)().boolcajacandy=!1,(0,i.c)().boolalquiler=!1,(0,i.c)().boolcliente=!1,(0,i.c)().boolcortesia=!1}))})).onCancel((()=>{}))},eventSearch(){this.$api.post("eventSearch").then((e=>{this.store.eventNumber=e.data}))},toggleLeftDrawer(){this.leftDrawerOpen=!this.leftDrawerOpen}}};var c=a(1639),p=a(7605),u=a(6602),b=a(1663),x=a(8879),k=a(1973),g=a(7691),w=a(906),f=a(3246),_=a(3115),h=a(335),m=a(1123),y=a(2133),q=a(9984),v=a.n(q);const j=(0,c.Z)(d,[["render",s]]),C=j;v()(d,"components",{QLayout:p.Z,QHeader:u.Z,QToolbar:b.Z,QBtn:x.Z,QToolbarTitle:k.Z,QChip:g.Z,QDrawer:w.Z,QList:f.Z,QItemLabel:_.Z,QImg:h.Z,QExpansionItem:m.Z,QPageContainer:y.Z})}}]);