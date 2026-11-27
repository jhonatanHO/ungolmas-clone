<script setup lang="ts">
import { onMounted } from 'vue';

/* LIFECYCLE */
onMounted(() => {
  console.log('admin login init');
});
</script>

<template>
  <form id="">
    <input
      id="email"
      name="email"
      type="email"
      autocomplete="username"
      placeholder="nombre@ejemplo.com"
      class=""
    />
    <input id="password" name="password" type="password" placeholder="*****" class="" />

    <button class="admin-button primary">Iniciar sesión</button>
  </form>
</template>
