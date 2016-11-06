import VueRouter from 'vue-router';

import Playback from './components/Playback.vue';
import Browse from './components/Browse.vue';
import BrowseDirectories from './components/BrowseDirectories.vue';

let router = null;

export function getRouter() {
  if (!router) {
    router = new VueRouter({
      linkActiveClass: 'active',
    });

    router.map({
      '/': {
        name: "default",
        component: Playback,
      },

      '/browse': {
        name: 'browse',
        component: Browse,
        subRoutes: {
          '/': {
            component: BrowseDirectories,
          },

          '/:name': {
            name: 'browseservice',
            component: BrowseDirectories,
          },

          '/:name/playlists': {
            name: 'playlists',
            component: BrowsePlaylists,
          },

          '/:name/playlists/:id': {
            name: 'viewplaylist',
            component: BrowsePlaylist,
          },
        },
      },

      '/playlist': {
        name: 'playlist',
        component: Playlist,
      },

      '/playback': {
        name: 'playback',
        component: Playback,
      },

      '/search': {
        name: 'search',
        component: Search,
      }
    });
  }

  return router;
}