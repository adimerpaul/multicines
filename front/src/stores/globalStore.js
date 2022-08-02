import { defineStore } from 'pinia';

export const globalStore = defineStore('global', {
  state: () => ({
    counter: 0,
    movies: [],
    movieSingleTon: false,
    distributors: [],
    distributor: {},
    distributorSingleTon: false,
    salas: [],
    salaSingleTon: false,
    prices: [],
    priceSingleTon: false,
    rubros: [],
    rubroSingleTon: false,

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
