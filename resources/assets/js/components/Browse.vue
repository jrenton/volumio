<template>
  <div class="tab-content">
    <div id="database">
      <component :is="$route.params.name || 'default'"></component>
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import store from '../store';
import musicPlayer from '../services/musicPlayerService';
import queue from '../services/queueService';
import BrowseDirectories from './BrowseDirectories.vue';
import Spotify from './Spotify.vue';
import Pandora from './Pandora.vue';
import Playlist from './Playlist.vue';
import Playback from './Playback.vue';

export default {
	methods: {
    openDirectory(dir) {
      var serviceType = dir.serviceType.toLowerCase();
      switch (dir.type) {
        case "Directory":
          this.$router.push({ 
            name: "playlists",
            params: {
              name: serviceType,
            },
          });
          break;
      }
    },

    getFileName(file) {
      var title = file.title;
      
      if (!title) {
        title = file.Name;
        
        if (!title) {
          var lastIndex = file.file.lastIndexOf('/') + 1;
          title = file.file.substr(lastIndex, file.file.length - lastIndex - 4);
        }
      }
      
      return title;
    },

    getAlbumArtist(file) {
      var albumArtist = "";
      
      if (file.artist && file.album) {
        albumArtist = file.artist + " - " + file.album;
      }
      else if (file.artist) {
        albumArtist = file.artist;
      }
      
      return albumArtist;
    }
	},

  created() {
    this.$store.dispatch('getServices');
  },

  components: {
    default: BrowseDirectories,
    spotify: Spotify,
    pandora: Pandora,
    Playlist,
    Playback,
  },
};
</script>