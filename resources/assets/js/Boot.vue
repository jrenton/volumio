<template>
  <div>
    <div id="menu-top" class="ui-header ui-bar-f ui-header-fixed slidedown" data-position="fixed" data-role="header" role="banner">
      <div class="dropdown">
        <a class="dropdown-toggle" id="menu-settings" role="button" data-toggle="dropdown" data-target="#" href="index.php"><i class="fa fa-th-list dx"></i></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="menu-settings">
          <li>
            <router-link :to="{ name: 'playback' }">
              <i class="fa fa-play sx"></i> Main
            </router-link>
          </li>
                <li>
                    <a href="https://accounts.spotify.com/authorize/?response_type=code&client_id=ab6fd2e9ddd04857947ea58e3e44678a&redirect_uri=http://homestead.app:8000&scope=user-follow-modify playlist-modify-private user-library-modify user-library-read"><i class="fa fa-spotify sx"></i> Spotify Connect</a>
                </li>
          <li>
                    <a href="/sources">
                        <i class="fa fa-folder-open sx"></i> Library
                    </a>
                </li>
          <li>
                    <a href="/mpdconfig">
                        <i class="fa fa-cogs sx"></i> Playback
                    </a>
                </li>
          <li>
                    <a href="/netconfig">
                        <i class="fa fa-sitemap sx"></i> Network
                    </a>
                </li>
          <li>
                    <a href="/settings">
                        <i class="fa fa-wrench sx"></i> System
                    </a>
                </li>
          <li>
                    <a href="/credits">
                        <i class="fa fa-trophy sx"></i> Credits
                    </a>
                </li>
          <li>
                    <a href="#poweroff-modal" data-toggle="modal">
                        <i class="fa fa-power-off sx"></i> Turn off
                    </a>
                </li>
        </ul>
      </div>
      <div id="db-back">
        <a class="back-btn" v-on:click="goBack()">
          <i class="fa fa-chevron-left sx"></i>
        </a>
        <span id="webradio-add">
          <a href="#webradio-modal" data-toggle="modal" title="Add New WebRadio">
            <button class="btn">
              <i class="fa fa-plus"></i>
              <em id="webradio-add-text"></em>
            </button>
          </a>
        </span>
      </div>
      <form id="db-search" @submit.prevent="search(searchTerm)">
        <div class="input-group">
          <input id="db-search-keyword" type="text" v-model="searchTerm" value="" placeholder="Search">
          <!--<input class="form-control" type="text" v-model="searchTerm" value="" placeholder="Search">-->
                <span class="input-group-btn">
                    <button class="btn" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
        </div>
      </form>
    </div>
    <div id="menu-bottom" class="ui-footer ui-bar-f ui-footer-fixed slidedown" data-position="fixed" data-role="footer"  role="banner">
      <ul>
        <li>
          <router-link :to="{ name: 'browse' }">
            <i class="fa fa-music sx"></i> Browse
          </router-link>
        </li>
        <li v-if="showLibrary">
          <router-link :to="{ name: 'library' }">
            <i class="fa fa-columns sx"></i> Library
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'playback' }">
            <i class="fa fa-play sx"></i> Playback
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'playlist' }">
            <i class="fa fa-list sx"></i> Playlist
          </router-link>
        </li>
      </ul>
    </div>
    <div id="main-container">
      <router-view>
        Content should go here
      </router-view>
    </div>
    <form class="form-horizontal" action="settings" method="post">
      <div id="poweroff-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="poweroff-modal-label" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="poweroff-modal-label">Turn off the player</h3>
        </div>
        <div class="modal-body">
          <button id="syscmd-poweroff" name="syscmd" value="poweroff" class="btn btn-primary btn-lg btn-block"><i class="fa fa-power-off sx"></i> Power off</button>
          <button id="syscmd-reboot" name="syscmd" value="reboot" class="btn btn-primary btn-lg btn-block"><i class="fa fa-refresh sx"></i> Reboot</button>
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        </div>
      </div>
    </form>
    <form class="form-horizontal" action="" method="post">
      <div id="webradio-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="webradio-modal-label" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="webradio-modal-label">Add New WebRadio</h3>
        </div>
        <div class="modal-body">
          <form action="settings.php" method="POST">	
        <input name="radio-name" type="text" placeholder="WebRadio Name" />
        <input name="radio-url" type="text" placeholder="WebRadio URL"/>
        </form>
        </div>
        <div class="modal-footer">
          <div class="form-actions">
                <button class="btn btn-lg" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <button type="submit" class="btn btn-primary btn-lg" name="save" value="save">Add</button>
            </div>
        </div>
      </div>
    </form>
    <form class="form-horizontal" action="" method="post">
      <div id="update-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="update-modal-label" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="update-modal-label">Add New WebRadio</h3>
        </div>
        <div class="modal-body">
          <form action="updates/check_updates.php" method="POST">	
        Cose varie da dire
        </form>
        </div>
        <div class="modal-footer">
          <div class="form-actions">
                <button class="btn btn-lg" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <button type="submit" class="btn btn-primary btn-lg" name="save" value="save">Add</button>
            </div>
        </div>
      </div>
    </form>

    <!-- loader -->
    <div id="loader">
      <div id="loaderbg"></div>
      <div id="loadercontent">
        <i class="fa fa-refresh fa-spin"></i>
        connecting...
      </div>
    </div>
    <div class="modal" id="errorResponseModal">
        <h2>Ajax error occurred</h2>
        <p id="errorResponseUrl"></p>
        <div id="errorResponseContent"></div>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    goBack() {
      this.$router.push(window.history.back());
    },

    search(searchTerm) {
      this.$store.dispatch('getSearchResults', searchTerm, '', 'spotify');

      this.$router.push({ name: 'search' });
    },
  },

  created() {
    this.$store.dispatch('getCurrentSong');
  },
};
</script>