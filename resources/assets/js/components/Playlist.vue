<template>
  <div class="tab-content">
    <div id="playlist">
      <ul class="playlist">
        <li id="pl-{{ song.index }}" v-for="song in playlist.songs">
          <div class="pl-action">
            <a title="Remove song from playlist" @click.prevent="removeFromPlaylist(song)">
              <i class="fa fa-trash"></i>
            </a>
          </div>
          <div class="pl-entry" @click.prevent="play(song)">
            {{ song.title }}
            <span>
              {{ song.artist }} - {{ song.album }}
            </span>
          </div>
        </li>
        <li id="pl-{{ song.index }}" v-for="song in queue.songs">
          <div class="pl-action">
            <a title="Remove song from playlist" @click.prevent="removeFromQueue(song)">
              <i class="fa fa-trash"></i>
            </a>
          </div>
          <div class="pl-entry" @click.prevent="play(song)">
            {{ song.title }}
            <span>
              {{ song.artist }} - {{ song.album }}
            </span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import musicPlayer from '../services/musicPlayerService';

export default {
  computed: {
    currentSong() {
      return this.$store.state.currentsong;
    },

    playlist() {
      return this.$store.state.playlist;
    },

    queue() {
      return this.$store.state.queue;
    },
  },

	methods: {
    play(song) {
      musicPlayer.play(song, song.serviceType);
    },

    removeFromQueue(song) {
      musicPlayer.removeQueue(song, song.serviceType);
    },

    removeFromPlaylist(song) {
      musicPlayer.removePlaylist(song, song.serviceType);            
    },
	},

  created() {
    this.$store.dispatch('getQueue');
  },
}
</script>