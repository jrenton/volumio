import VueRouter from 'vue-router';

import Playback from '../components/Playback.vue';
import Playlist from '../components/Playlist.vue';
import Search from '../components/Search.vue';
import Browse from '../components/Browse.vue';
import BrowseDirectories from '../components/BrowseDirectories.vue';
import BrowsePlaylists from '../components/BrowsePlaylists.vue';
import BrowsePlaylist from '../components/BrowsePlaylist.vue';

let router = null;

export function getRouter() {
  if (!router) {

    router = new VueRouter({
      linkActiveClass: 'active',
      mode: 'history',
      scrollBehavior: () => ({ y: 0 }),
      routes: [
        { path: '/', name: 'default', component: Playback },
        { path: '/playlist', name: 'playlist', component: Playlist },
        { path: '/playback', name: 'playback', component: Playback },
        { path: '/search', name: 'search', component: Search },
        { path: '/browse', name: 'browse', component: Browse,
          children: [
            { path: '', component: BrowseDirectories },
            { path: ':name', component: BrowseDirectories, name: 'browseservice' },
            { path: ':name/playlists', component: BrowsePlaylists, name: 'playlists' },
            { path: ':name/playlists/:id', component: BrowsePlaylist, name: 'viewplaylist' },
          ]
        },
      ],
    });
  }

  return router;
}
