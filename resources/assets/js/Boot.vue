<template>
  <div>
    <router-view></router-view>
  </div>
</template>

<script>
import store from './store';

export default {
  store,

  ready() {
    this.$store.dispatch('getCurrentSong');
  },

  methods: {
    goBack() {
      this.$router.go(window.history.back());
    },

    search(searchTerm) {
      this.$store.dispatch('getSearchResults', searchTerm, '', 'spotify');

      this.$router.go({ name: 'search' });
    },
  },
};
</script>