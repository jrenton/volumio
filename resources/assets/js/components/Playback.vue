<template>
  <div class="tab-content">
    <div id="playback" class="tab-pane active">
      <div class="container text-center">
        <div class="playback-info">
          <h2>{{ song.title }}</h2>
          <h3>{{ song.artist }}</h3>
          <h4>{{ song.album }}</h4>
          <h5>{{ song.type }}</h5>
            <!--<span id="currentalbum"></span>-->
        </div>
        <!-- <span id="playlist-position">&nbsp;</span> -->
        <div class="playback-controls">
          <button class="btn"
                  title="Previous"
                  @click.prevent="nav('previous')">
            <i class="fa fa-step-backward"></i>
          </button>
          <!--<button id="stop" class="btn btn-cmd" title="Stop"><i class="fa fa-stop"></i></button>-->
          <a id="play"
             href="#"
             title="Play/Pause"
             @click.prevent="playPause()">
            <span class="fa-stack fa-4x">
              <i class="fa fa-circle-thin fa-stack-2x"></i>
              <i class="fa fa-stack-1x"
                 :class="{ 'fa-play': song.state == 'stopped' || song.state == 'paused' || !song.state, 'fa-pause': song.state == 'playing' }"></i>
            </span>
          </a>
          <button class="btn" title="Next" @click.prevent="nav('next')">
            <i class="fa fa-step-forward"></i>
          </button>
        </div>
        <div class="row">
          <div class="col-md-4">
            <elapsed-time :elapsed-time="song.elapsed"
                          :total-time="song.time"
                          :state="song.state"></elapsed-time>
            <div class="btn-toolbar">
              <div class="btn-group">
                <a class="btn"
                   title="Repeat"
                   @click.prevent="repeat()"
                   :class="{'btn-success' : repeat === true }">
                  <i class="fa fa-repeat"></i>
                </a>
                <a class="btn"
                   title="Random"
                   @click.prevent="random()"
                   :class="{'btn-success' : random === true }">
                  <i class="fa fa-random"></i>
                </a>
                <a class="btn"
                   title="Single"
                   @click.prevent="single()"
                   :class="{'btn-success' : single === true }">
                  <i class="fa fa-refresh"></i>
                </a>
                <a class="btn"
                   title="Consume Mode"
                   @click.prevent="consume()"
                   :class="{'btn-success' : consume === true }">
                  <i class="fa fa-trash"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="btn-group ratings" role="group">
              <button type="button"
                      class="btn"
                      @click.prevent="rateUp()"
                      :class="{ 'btn-success': song.rating == 'good'}">
                <i class="fa fa-thumbs-up"></i>
              </button>
              <button type="button"
                      class="btn btn-ratedown"
                      @click.prevent="rateDown()"
                      :class="{ 'btn-danger': song.rating == 'bad'}">
                <i class="fa fa-thumbs-down"></i>
              </button>
            </div>
          </div>

          <div class="col-md-4">
            <volume-control></volume-control>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue';
import musicPlayer from '../services/musicPlayerService';

import VolumeControl from './shared/VolumeControl.vue';
import ElapsedTime from './shared/ElapsedTime.vue';

import { SONG_STATES } from '../utils/enums';

export default {
  computed: {
    song() {
      return this.$store.state.currentsong;
    },

    serviceType() {
      return this.song.type;
    },
  },

	methods: {
    playPause() {
      var cmd = '';

      if (this.song.state == SONG_STATES.PLAYING) {
        cmd = SONG_STATES.PAUSED;
      }
      else if (this.song.state == SONG_STATES.PAUSED) {
        cmd = SONG_STATES.PLAYING;
      }
      else if (this.song.state == SONG_STATES.STOPPED) {
        cmd = SONG_STATES.PLAYING;
      }

      switch(cmd) {
        case SONG_STATES.PLAYING:
          musicPlayer.play(null, this.serviceType);
          break;
        case SONG_STATES.PAUSED:
          musicPlayer.pause(this.serviceType);
          break;
      }
      //sendCmd(cmd);
      //sendCommand("spop-goto", { "path": this.song.index });
    },

    nav(direction) {
      switch (direction) {
        case 'next':
          musicPlayer.next(this.serviceType, (data) => {
          });
          break;
        case 'previous':
          musicPlayer.previous(this.serviceType, (data) => {

          });
          break;
      }
    },

    rateUp() {
      musicPlayer.rateUp(this.song, this.song.type);
    },

    rateDown() {
      musicPlayer.rateDown(this.song, this.song.type);
    },

    random() {
      musicPlayer.random(this.song.type);
    },

    single() {
      musicPlayer.single(this.song.type);
    },

    consume() {
      musicPlayer.consume(this.song.type);
    },

    repeat() {
      musicPlayer.repeat(this.song.type);
    },
	},

  components: {
    VolumeControl,
    ElapsedTime,
  },
};
</script>

<style lang="less">
@import "../less/_variables";

.playback-info {
  padding: 10px 0;
	margin: 0;
}

#play {
	text-decoration: none;
}

#play:hover {
	text-decoration: none;
}

.playback-controls {
	text-align: center;
	padding-bottom: 10px;
}

.playback-controls .btn {
	width: 60px;
}

.playback-controls .fa-play,
.playback-controls .fa-pause {
	font-size: 20px;
}

.playback-controls .fa,
.playback-controls .btn {
	background: transparent;
	color: @lightText;
	border: 0;
}

#playback {
	z-index: 1;
	position: absolute;
  width: 100%;
}

#playbackCover.coverImage::after {
  content: "";
  opacity: 0.4;
  z-index: 0;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  position: fixed;
}

#playlist-position {
	color: #8FA7BF;
}
</style>
