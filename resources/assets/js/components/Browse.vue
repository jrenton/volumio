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
import BrowseDirectories from './BrowseDirectories';
import Spotify from './Spotify';
import Pandora from './Pandora';

export default {
	data() {
    return store.state.browse;
	},

  ready() {
    musicPlayer.getServices((services) => {
      store.state.browse.directories = services; 
    });
  },

	methods: {
    openDirectory(dir) {
      var serviceType = dir.serviceType.toLowerCase();
      switch (dir.type) {
        case "Directory":
          this.$router.go({ 
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

  components: {
    default: BrowseDirectories,
    spotify: Spotify,
    pandora: Pandora,
  },
};
</script>