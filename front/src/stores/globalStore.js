import { defineStore } from 'pinia';

export const globalStore = defineStore('global', {
  state: () => ({
    counter: 0,
    movies: [],
    user: {},
    cine: {},
    eventNumber: 0,
    movieSingleTon: false,
    isLoggedIn: !!localStorage.getItem('tokenMulti'),
    distributors: [],
    distributor: {},
    distributorSingleTon: false,
    salas: [],
    salaSingleTon: false,
    prices: [],
    priceSingleTon: false,
    rubros: [],
    rubroSingleTon: false,
    rubro: {},
    productos: [],
    productoSingleTon: false,
    detallecandy:[],
    salas2:[],
    salas3:[],
    movies2:[],
    movies3:[],
    prices2:[],
    prices3:[],

    booluser:false,
    boolcuis:false,
    boolsincr:false,
    boolcufd:false,
    boolevento:false,
    boolmovie:false,
    booldistrib:false,
    boolsala:false,
    booltarifa:false,
    boolrubro:false,
    boolproducto:false,
    boolprogram:false,
    boolboleteria:false,
    boollistbol:false,
    boolcandy:false,
    boollistcandy:false,
    boolcajabol:false,
    boolcajacandy:false,
    boolalquiler:false,
    boolcliente:false,
    boolcortesia:false,
    boolreporte:false,
  }),
  getters: {
    doubleCount: (state) => state.counter * 2,
  },
  actions: {
    increment() {
      this.counter++;
    },
  },
});
