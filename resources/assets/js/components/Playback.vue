<template>
  <div class="tab-content">
    <div id="playback" class="tab-pane active">
      <div class="container txtmid">
        <div id="playback-info">
          <span id="currentsong">{{ song.title }}</span>				
          <span id="currentartist">{{ song.artist }}</span>
          <span id="currentalbum">{{ song.album }}</span>
          <span id="currenttype">{{ song.type }}</span>
            <!--<span id="currentalbum"></span>-->
        </div>
        <!-- <span id="playlist-position">&nbsp;</span> -->
        <div class="playback-controls">	
          <button class="btn" title="Previous" @click.prevent="nav('previous')">
            <i class="fa fa-step-backward"></i>
          </button>
          <!--<button id="stop" class="btn btn-cmd" title="Stop"><i class="fa fa-stop"></i></button>-->
          <a id="play" href="#" title="Play/Pause" @click.prevent="playPause()">
            <span class="fa-stack fa-4x">
              <i class="fa fa-circle-thin fa-stack-2x"></i>
              <i class="fa fa-stack-1x"
                 :class="{ 'fa-play': song.state == 'stop' || song.state == 'pause' || !song.state, 'fa-pause': song.state == 'play'}"></i>
            </span>
          </a>
          <button class="btn" title="Next" @click.prevent="nav('next')">
            <i class="fa fa-step-forward"></i>
          </button>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div id="timeknob">
              <div id="countdown" ms-user-select="none">
                <input id="time"
                       class="playbackknob"
                       data-readonly="false"
                       data-min="0"
                       data-max="1000"
                       data-width="100%"
                       data-thickness="0.30"
                       data-bgColor="rgba(0,0,0,0)"
                       data-fgcolor="#007F0B">
              </div>
              <span id="countdown-display" v-ref:countdown-display></span>
              <span id="total"></span>
            </div>
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

          <div class="col-md-4 volume">
            <input id="volume"
                   class="volumeknob"
                   data-width="211"
                   data-cursor="true"
                   data-bgColor="rgba(0,0,0,0)"
                   data-fgColor="#007F0B"
                   data-thickness=".25"
                   data-angleArc="250"
                   data-angleOffset="-125"
                   data-skin="tron"
                   v-model="song.volume">	
            <div class="btn-toolbar floatright">
              <div class="btn-group">
                <a id="volumedn" class="btn">
                  <i class="fa fa-volume-down"></i>
                </a>
                <a id="volumemute" class="btn">
                  <i class="fa fa-volume-off"></i>
                  <i class="fa fa-exclamation"></i>
                </a>
                <a id="volumeup" class="btn">
                  <i class="fa fa-volume-up"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue';
import musicPlayer from '../services/musicPlayerService';

export default {
  data() {
    return {
      countdownDisplay: null,
    };
  },

  computed: {
    song() {
      this.$store.state.currentsong;
    },

    serviceType() {
      return this.song.type;
    },
  },

	methods: {
    playPause() {
      var cmd = '',
          countdownDisplay = $('#countdown-display');

      if (this.song.state == 'play') {
        cmd = 'pause';
        this.countdownDisplay.countdown('pause');
      }
      else if (this.song.state == 'pause') {
        cmd = 'play';
        this.countdownDisplay.countdown('resume');
      }
      else if (this.song.state == 'stop') {
        cmd = 'play';
        this.countdownDisplay.countdown({since: 0, compact: true, format: 'MS'});
      }

      window.clearInterval(GUI.currentKnob);
      switch(cmd) {
        case 'play':
          musicPlayer.play(null, this.serviceType, (data) => {
          });
          break;
        case 'pause':
          musicPlayer.pause(this.serviceType, (data) => {
          });
          break;
      }
      //sendCmd(cmd);
      //sendCommand("spop-goto", { "path": this.song.index });
    },

    nav(direction) {
      GUI.halt = 1;
      this.countdownDisplay.countdown('pause');
      window.clearInterval(GUI.currentKnob);
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

  mounted() {
    Vue.nextTick(() => {
      this.countdownDisplay = $(this.$refs.countdownDisplay);
    });
  },
};
</script>