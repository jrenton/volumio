<template>
  <div class="tab-content">
	  <div id="database">
      <ul class="database">
        <li v-for="song in searchResults">
          <div class="db-action">
            <a href="#" title="Actions" data-toggle="context" data-target="#context-menu-song">
              <i class="fa fa-ellipsis-v"></i>
            </a>
          </div>
          <div id="context-menu-song" class="context-menu">
            <ul class="dropdown-menu" role="menu">
              <li>
                <a @click.prevent="play()">
                  <i class="fa fa-share sx"></i>Play
                </a>
              </li>
              <li>
                <a @click.prevent="add()">
                  <i class="fa fa-plus sx"></i>Add to queue
                </a>
              </li>
              <li>
                <a @click.prevent="search(song.title, 'title')">
                  <i class="fa fa-headphones sx"></i>Search title
                </a>
              </li>
              <li>
                <a @click.prevent="search(song.artist, 'artist')">
                  <i class="fa fa-user sx"></i>Search artist
                </a>
              </li>
              <li>
                <a @click.prevent="search(song.album, 'album')">
                  <i class="fa fa-circle sx"></i>Search album
                </a>
              </li>
            </ul>
          </div>
          <div class="db-entry db-browse" @click.prevent="play()">
            {{ song.title }} <em class="songtime"> {{ song.time }}</em>
            <span> {{ song.artist }} - {{ song.album }}</span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import musicPlayer from '../services/musicPlayerService';
import queue from '../services/queueService';

export default {
  computed: {
    playlist() {
      return this.$store.state.playlist;
    },

    song() {
      return this.$store.state.currentsong;
    },
  },

	methods: {
    play() {
      musicPlayer.play(this.song, this.song.serviceType, (data) => {                
        this.$router.push({ name: 'playback' });
        
        queue.addSongs(this.playlist.songs);
  
        getPlaylist();
      });
    },

    search(searchTerm, type) {
      this.$store.dispatch('getSearchResults', searchTerm, type, 'spotify');
    },
  },
};
</script>