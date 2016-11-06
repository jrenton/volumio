<template>
  <ul class="database">
    <li v-for="dir in directories">
      <div class="db-icon db-browse folder">
        <i class="fa sx db-browse"
           :class="{ 'fa-product-hunt': dir.Type == 'PandoraDirectory', 'fa-music': dir.Type != 'PandoraDirectory'}"></i>
      </div>
      <div class="db-action">
        <a href="#"
           title="Actions"
           data-toggle="context"
           data-target="#context-menu">
          <i class="fa fa-ellipsis-v"></i>
        </a>
      </div>
      <div class="db-entry db-browse db-folder" @click.prevent="openPandora(dir)">
        {{ dir.Name }}
      </div>
    </li>
  </ul>
</template>

<script>
export default {
  computed: {
    directories() {
      return this.$store.state.browse.pandoraDirectories;
    },
  },
};
</script>