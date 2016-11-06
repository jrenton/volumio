<template>
  <ul class="database">
    <li v-for="song in playlist.songs">
      <!--<div class="db-icon db-browse">
          <i class="fa fa-spotify sx db-browse"></i>
      </div>-->
      <div class="db-action">
        <a href="#" title="Actions" data-toggle="context" data-target="#context-menu-song">
          <i class="fa fa-ellipsis-v"></i>
        </a>
      </div>
      <div id="context-menu-song" class="context-menu">
        <ul class="dropdown-menu" role="menu">
          <li>
            <a @click.prevent="play(song)">
              <i class="fa fa-share sx"></i>Play
            </a>
          </li>
          <li>
            <a @click.prevent="add(song)">
              <i class="fa fa-plus sx"></i>Add to queue
            </a>
          </li>
          <li>
            <a @click.prevent="search(song.title, 'title', song.serviceType)">
              <i class="fa fa-headphones sx"></i>Search title
            </a>
          </li>
          <li>
            <a @click.prevent="search(song.artist, 'artist', song.serviceType)">
              <i class="fa fa-user sx"></i>Search artist
            </a>
          </li>
          <li>
            <a @click.prevent="search(song.album, 'album', song.serviceType)">
              <i class="fa fa-circle sx"></i>Search album
            </a>
          </li>
        </ul>
      </div>
      <div class="db-entry db-browse" @click.prevent="play(song)">
        {{ song.title }} <em class="songtime"> {{ song.time }}</em>
        <span> {{ song.artist }} - {{ song.album }}</span>
      </div>
    </li>
  </ul>
</template>

<script>
import store from '../store';
import musicPlayer from '../services/musicPlayerService';
import queue from '../services/queueService';

export default {
  computed: {
    playlist() {
      return this.$store.state.playlist;
    },
  },

  methods: {
    play(song) {
      musicPlayer.play(song, song.serviceType, (data) => {                
        this.$router.push({ name: 'playback' });
        
        queue.addSongs(this.playlist.songs);

        // getPlaylist();
      });
    },

    add(song) {
      musicPlayer.add(song);
    },

    search(searchTerm, type, serviceType) {
      musicPlayer.search(searchTerm, type, serviceType);
    },
  },

  watch: {
    '$route'() {
      this.$store.dispatch('getPlaylist', { id: this.$route.params.id, name: this.$route.params.name });
    }
  },
};
</script>