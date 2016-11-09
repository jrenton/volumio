<template>
  <div>
    <form @submit.prevent="addPlaylist(newPlaylist); return false;">
      <div class="input-group">
        <input type="text"
               v-model="newPlaylist"
               class="form-control"
               placeholder="Add new playlist">
        <span class="input-group-btn">
          <button class="btn" type="submit">
            <i class="fa fa-plus"></i>
          </button>
        </span>
      </div>
    </form>
    <ul class="database">
      <li v-for="dir in playlists">
        <div class="db-icon db-folder db-browse">
          <i class="fa sx"
             :class="{ 'fa-folder-open' : dir.type === 'folder', 'fa-list-ol': dir.type === 'playlist', 'fa-spotify' : dir.directory == 'SPOTIFY', 'icon-root' : dir.directory == 'SPOTIFY'}"></i>
        </div>
        <template v-if="dir.type === 'playlist'">
          <div class="db-action">
            <a href="#notarget"
               title="Actions"
               data-toggle="context"
               data-target="#context-menu-spotifyplaylist">
              <i class="fa fa-ellipsis-v"></i>
            </a>
          </div>
        </template>
        <div class="db-entry db-folder db-browse" @click.prevent="openDirectory(dir)">
          {{ dir.name === undefined ? dir.directory : dir.name }}
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import musicPlayer from '../services/musicPlayerService';
import queue from '../services/queueService';

export default {
  data() {
    return {
      newPlaylist: null,
    };
  },

  computed: {
    playlists() {
      return this.$store.playlists;
    },
  },

  methods: {
    addPlaylist(playlist) {
      musicPlayer.addPlaylist(playlist, this.$route.params.name);
    },

    openDirectory(dir) {
      console.log("open this directory");
      console.log(dir);
      if (dir.type === 'folder') {

      }
      else if (dir.type === 'playlist') {
        this.$router.push({
          name: 'viewplaylist',
          params : {
            name: dir.serviceType.toLowerCase(),
            id: dir.id,
          },
        });
      }
      else if (dir.type === 'RadioStation') {
        musicPlayer.playPlaylist(dir, dir.serviceType, () => {
          this.$router.push({ name: 'playback' });
        });
      }
    },
  },

  watch: {
    '$route'() {
      this.$store.dispatch('getPlaylists', this.$route.params.name);
    },
  },
};
</script>
