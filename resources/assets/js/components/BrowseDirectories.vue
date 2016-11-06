<template>
  <ul class="database">
    <li v-for="dir in directories">
      <div class="db-icon db-folder db-browse">
        <i class="fa sx"
           :class="{'icon-root': ['WEBRADIO', 'USB', 'SPOTIFY', 'RAMPLAY', 'NAS', 'PANDORA'].indexOf(dir.directory) !== -1, 'fa-microphone' : dir.directory == 'WEBRADIO', 'fa-code-fork': dir.directory == 'NAS', 'fa-usb': dir.directory == 'USB', 'fa-spinner': dir.directory == 'RAMPLAY'}"></i>
      </div>
      <div class="db-action">
        <a href="#notarget"
            title="Actions"
            data-toggle="context"
            data-target="#context-menu">
            <i class="fa fa-ellipsis-v"></i>
        </a>
      </div>
      <div class="db-entry db-folder db-browse"
           @click.prevent="openDirectory(dir)">
          <template v-if="dir.DisplayName">
            {{ dir.DisplayName }}
          </template>
          <template v-if="!dir.DisplayName">
            {{ dir.directory.substr(dir.directory.lastIndexOf('/') + 1, dir.directory.length - dir.directory.lastIndexOf('/') + 1); }}
          </template>
      </div>
    </li>
    <li v-if="isLibrary" id="#db-plug-lib" class="db-plugin" onclick="showLibraryView()">
      <div class="db-icon db-other">
        <i class="fa fa-columns icon-root sx"></i>
      </div>
      <div class="db-entry db-other">
        LIBRARY
      </div>
    </li>
  </ul>
</template>

<script>
export default {
  computed: {
    directories() {
      return this.$store.state.browse.directories;
    },
  },

  methods: {
    openDirectory(dir) {
      getDB('filepath', dir.directory, 'file', 0);
    },
  },
};
</script>