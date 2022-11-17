import { defineStore } from 'pinia';

export const globalStore = defineStore('global', {
  state: () => ({
    counter: 0,
    movies: [],
    user: {},
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
