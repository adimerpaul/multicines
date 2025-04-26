<script setup>
import HelloWorld from './components/HelloWorld.vue'
import Footer from "./components/Footer.vue";
import Header from "./components/Header.vue";
import Home from "./views/Home.vue";
</script>

<template>
  <Header />
  <router-view />
  <Footer />
</template>

