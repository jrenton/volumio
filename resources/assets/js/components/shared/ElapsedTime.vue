<template>
  <div class="time-knob">
    <div class="countdown" ms-user-select="none">
      <input class="time playbackknob"
             data-readonly="false"
             data-min="0"
             data-max="1000"
             data-width="100%"
             data-thickness="0.30"
             data-bgcolor="rgba(0,0,0,0)"
             data-fgcolor="#007F0B"
             v-model="initTime"
             ref="timeKnob">
    </div>
    <span class="countdown-display" ref="countdownDisplay"></span>
    <span class="countdown-total"></span>
  </div>
</template>

<script>
import Vue from 'vue';

import { SONG_STATES } from '../../utils/enums';

export default {
  props: {
    elapsedTime: {
      type: Number,
      required: true,
    },

    totalTime: {
      type: Number,
      required: true,
    },

    state: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      countdownDisplay: null,
      countdownTimeout: null,
    };
  },

  computed: {
    percentComplete() {
      if (!this.totalTime) {
        return 0;
      }

      return this.elapsedTime / this.totalTime;
    },

    initTime() {
      return this.percentComplete * 1000;
    },

    delta() {
      return this.totalTime / 1000;
    },
  },

  methods: {
    refresh() {
      // window.clearInterval(GUI.currentKnob)

      // var $time = $("#time");
      // $time.val(this.initTime).trigger('change');
      // if (GUI.currentsong.state == 'play') {
      //     GUI.currentKnob = setInterval(function() {
      //         initTime = initTime + 1;
      //         window.GUI.currentsong.elapsed = parseFloat(window.GUI.currentsong.elapsed) + parseFloat(delta);
      //         window.GUI.currentsong.percentcomplete = window.GUI.currentsong.elapsed / GUI.currentsong.time;
      //         $time.val(initTime).trigger('change');
      //     }, this.delta * 1000);
      // }
    },

    initKnob() {
      const _self = this;
      $('.playbackknob').knob({
        inline: false,
        change(value) {
          if (_self.state != SONG_STATES.STOPPED) {
            clearInterval(_self.countdownTimeout);
  			  }
          else {
            _self.elapsedTime = 0;
          }
        },

        release(value) {
    			if (_self.state != SONG_STATES.STOPPED) {
    				clearInterval(_self.countdownTimeout);

    				var seekto = 0;
    				if (GUI.SpopState['state'] == SONG_STATES.PLAYING
                || GUI.SpopState['state'] == SONG_STATES.PAUSED) {
    					seekto = Math.floor((value * parseInt(GUI.SpopState['time'])) / 1000);
    					// Spop expects input to seek in ms
    					sendCmd('seek ' + seekto * 1000);
    					// Spop idle mode does not detect a seek change, so update UI manually
    					AjaxUtils.get('playerEngineSpop?state=manualupdate', {}, (data) => {
  							if (data != '') {
  								GUI.SpopState = data;
  								renderUI();
  							}
  						});
    				}
            else {
    					seekto = Math.floor((value * parseInt(GUI.MpdState['time'])) / 1000);
    					sendCmd('seek ' + GUI.MpdState['song'] + ' ' + seekto);
    				}
    			}
        },
        cancel() {},
        draw() {},
      });
    },

    updateCountdown() {
      clearInterval(this.countdownTimeout);
      const timeKnob = $(this.$refs.timeKnob);
      timeKnob.trigger('change');
      if (this.state == SONG_STATES.PLAYING) {

        this.countdownTimeout = setInterval(() => {
          this.elapsedTime++;
          timeKnob.trigger('change');
        }, 1000);
      }

      const startFrom = this.elapsedTime;
      const stopTo = this.totalTime;
      const state = this.state;

      if (state == SONG_STATES.PLAYING) {
        this.countdownDisplay.countdown('destroy');
        this.countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
      }
      else if (state == SONG_STATES.PAUSED) {
        this.countdownDisplay.countdown('destroy');
        this.countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
        this.countdownDisplay.countdown('pause');
      }
      else if (state == SONG_STATES.STOPPED) {
        this.countdownDisplay.countdown('destroy');
        this.countdownDisplay.countdown({since: 0, compact: true, format: 'MS'});
        this.countdownDisplay.countdown('pause');
      }
    },
  },

  mounted() {
    Vue.nextTick(() => {
      this.countdownDisplay = $(this.$refs.countdownDisplay);
      this.initKnob();
      this.updateCountdown();
    });
  },

  destroyed() {
    clearInterval(this.countdownTimeout);
  },

  watch: {
    state() {
      console.log('state change!!!');
      this.updateCountdown();
    },

    totalTime() {
      this.updateCountdown();
    },
  },
};
</script>

<style lang="less">
.time-knob {
	position: relative;
	padding: 10px 0;
}
.time-knob.pulse, .time-flow.pulse {
	box-shadow: 0 0 0 0 rgba(0,0,0,0.2);
	background-color: #fff;
	-webkit-transition: box-shadow 0s ease-in-out, background-color .9s ease-in-out;
	-moz-transition: box-shadow 0s ease-in-out, background-color .9s ease-in-out;
	-o-transition: box-shadow 0s ease-in-out, background-color .9s ease-in-out;
	-ms-transition: box-shadow 0s ease-in-out, background-color .9s ease-in-out;
	transition: box-shadow 0s ease-in-out, background-color .9s ease-in-out;
}
.time {
  position: relative;
	visibility: hidden;
}
.countdown {
  position: relative;
	height: 200px;
	color: #fff;

  div {
  	margin: 0 auto;
  }
}

.countdown-display {
	position: absolute;
	top: 50%;
	left: 50%;
	width: 120px;
	margin: -5px 0 0 -60px;
	font-size: 20px;
	line-height: 20px;
	font-weight: bold;
}

.countdown-total {
	position: absolute;
	top: 68%;
	left: 50%;
	width: 60px;
	margin: -10px 0 0 -30px;
}
</style>
