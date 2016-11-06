<template>
  <ul class="database">
    <li v-for="file in files" data-path="{{ file.file }}">
      <div class="db-icon db-browse"
           :class="{ 'db-other': file.Time === undefined, 'db-song' : file.Time !== undefined }">
        <i class="fa fa-music sx db-browse"></i>
      </div>
      <div class="db-action">
        <a href="#notarget"
           title="Actions"
           data-toggle="context"
           data-target="#context-menu">
          <i class="fa fa-ellipsis-v"></i>
        </a>
      </div>
      <div class="db-entry db-browse"
           :class="{ 'db-other': file.Time === undefined, 'db-song' : file.Time !== undefined }" v-on:click="playSong(file)">
        {{ getFileName(file) }}
        <em class="songtime"> {{ file.Time }}</em>
        <span>{{ getAlbumArtist(file) }}</span>
      </div>
    </li>
  </ul>
</template>

<script>
export default {
  computed: {
    files() {
      return this.$store.state.browse.files;
    },
  },
};
</script>