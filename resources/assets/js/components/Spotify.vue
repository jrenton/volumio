<template>
  <div>
    <h2>Spotify</h2>
    <ul class="database">
      <li v-for="track in spotifyTracks">
        <div class="db-icon db-browse">
          <i class="fa fa-spotify sx db-browse"></i>
        </div>
        <div class="db-action">
          <a href="#"
             title="Actions"
             data-toggle="context"
             data-target="#context-menu-spotifytrack">
            <i class="fa fa-ellipsis-v"></i>
          </a>
        </div>
        <div id="context-menu-spotifytrack" class="context-menu">
          <ul class="dropdown-menu" role="menu">
            <li>
              <a @click.prevent="play(track)">
                <i class="fa fa-share sx"></i>Play
              </a>
            </li>
            <li>
              <a @click.prevent="add(track)">
                <i class="fa fa-plus sx"></i>Add to queue
              </a>
            </li>
            <li>
              <a @click.prevent="search(track.title, 'title')">
                <i class="fa fa-headphones sx"></i>Search title
              </a>
            </li>
            <li>
              <a @click.prevent="search(track.artist, 'artist')">
                <i class="fa fa-user sx"></i>Search artist
              </a>
            </li>
            <li>
              <a @click.prevent="search(track.album, 'album')">
                <i class="fa fa-circle sx"></i>Search album
              </a>
            </li>
          </ul>
        </div>
        <div class="db-entry db-browse" @click.prevent="play(track)">
            {{ track.title }} <em class="songtime"> {{ track.Time }}</em>
            <span> {{ track.artist }} - {{ track.album }}</span>
        </div>
      </li>		
      <li v-for="dir in spotifyPlaylists">
        <div class="db-icon db-folder db-browse">
            <i class="fa sx" v-bind:class="{ 'fa-folder-open' : dir.SpopPlaylistIndex === undefined && dir.directory !== 'SPOTIFY', 'fa-list-ol': dir.SpopPlaylistIndex !== undefined, 'fa-spotify' : dir.directory == 'SPOTIFY', 'icon-root' : dir.directory == 'SPOTIFY'}"></i>
        </div>
        <template v-if="dir.SpopPlaylistIndex !== undefined">
            <div class="db-action">
                <a href="#notarget" title="Actions" data-toggle="context" data-target="#context-menu-spotifyplaylist">
                    <i class="fa fa-ellipsis-v"></i>
                </a>
            </div>
        </template>
        <a class="db-entry db-folder db-browse" @click.prevent="getDirectory(dir)">
            {{ dir.DisplayName === undefined ? dir.directory : dir.DisplayName }}
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import musicPlayer from '../services/musicPlayerService';

export default {
  computed: {
    spotifyPlaylists() {
      return this.$store.state.browse.spotifyPlaylists;
    },

    spotifyTracks() {
      return this.$store.state.browse.spotifyTracks;
    },
  },

  methods: {
    openDirectory(dir) {
      this.$router.go({ name: dir.serviceType.toLowerCase() });
    },
  },

  route: {
    activate(transition) {
      musicPlayer.openService('spotify').then((playlists) => {
        this.$store.commit('SET_SPOTIFY_PLAYLISTS', playlists);
        done(); 
      });
    },
  },
};
</script>