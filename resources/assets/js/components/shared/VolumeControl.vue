<template>
  <div class="volume">
    <input id="volume"
           class="volumeknob"
           data-width="211"
           data-cursor="true"
           data-bgcolor="rgba(0,0,0,0)"
           data-fgcolor="#007F0B"
           data-thickness=".25"
           data-anglearc="250"
           data-angleoffset="-125"
           data-skin="tron"
           v-model="volume"
           ref="volumeControl">
    <div class="btn-toolbar floatright">
      <div class="btn-group">
        <a class="btn" @click.prevent="volumeDown">
          <i class="fa fa-volume-down"></i>
        </a>
        <a class="btn"
           :class="{ 'btn-primary': isMuted }"
           @click.prevent="volumeMute">
          <i class="fa fa-volume-off"></i>
          <i class="fa fa-exclamation"></i>
        </a>
        <a class="btn" @click.prevent="volumeUp">
          <i class="fa fa-volume-up"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue';

export default {
  data() {
    return {
      volume: 100,
      isMuted: false,
    };
  },

  computed: {
  },

  methods: {
    volumeUp() {

    },

    volumeDown() {

    },

    volumeMute() {
      this.isMuted = !this.isMuted;
    },

    setVolume(value) {
      this.volume = value;
      // GUI.volume = val;
      //
      // // Push volume updates into the MPD state array, since we opted not to get
      // // volume change updates from MPD daemon
      // if ("volume" in GUI.MpdState) {
      //   GUI.MpdState.volume = val;
      // }
      //
      // sendCmd('setvol ' + val);
    },

    init() {
      const _self = this;
      // volume knob
      const volumeControl = $(this.$refs.volumeControl);
      const volumeKnob = volumeControl[0];
      // if (volumeKnob.length > 0) {
      //         volumeKnob[0].isSliding = function() {
      //     return volumeKnob[0].knobEvents.isSliding;
      // }
      volumeKnob.setSliding = (sliding) => {
        volumeKnob.knobEvents.isSliding = sliding;
      };

      volumeKnob.knobEvents = {
        isSliding: false,
        // on release => set volume
        release(value) {
          if (this.hTimeout != null) {
            clearTimeout(this.hTimeout);
            this.hTimeout = null;
          }
          volumeKnob.setSliding(false);
          _self.setVolume(value);
        },

        hTimeout: null,
        // on change => set volume only after a given timeout, to avoid flooding with volume requests
        change(value) {
          volumeKnob.setSliding(true);
          var that = this;
          if (this.hTimeout == null) {
            this.hTimeout = setTimeout(() => {
              clearTimeout(that.hTimeout);
              that.hTimeout = null;
              _self.setVolume(value);
            }, 200);
          }
        },

        cancel() {
          volumeKnob.setSliding(false);
        },

        draw() {
          // "tron" case
          if (this.$.data('skin') == 'tron') {

            var a = this.angle(this.cv)  // Angle
                , sa = this.startAngle          // Previous start angle
                , sat = this.startAngle         // Start angle
                , ea                            // Previous end angle
                , eat = sat + a                 // End angle
                , r = true;

            this.g.lineWidth = this.lineWidth;

            this.o.cursor
                && (sat = eat - 0.05)
                && (eat = eat + 0.05);

            if (this.o.displayPrevious) {
              ea = this.startAngle + this.angle(this.value);
              this.o.cursor
                  && (sa = ea - 0.1)
                  && (ea = ea + 0.1);
              this.g.beginPath();
              this.g.strokeStyle = this.previousColor;
              this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
              this.g.stroke();
            }

            this.g.beginPath();
            this.g.strokeStyle = r ? this.o.fgColor : this.fgColor ;
            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
            this.g.stroke();

            this.g.lineWidth = 2;
            this.g.beginPath();
            this.g.strokeStyle = this.o.fgColor;
            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 10 + this.lineWidth * 2 / 3, 0, 20 * Math.PI, false);
            this.g.stroke();

            return false;
          }
        }
      };

      volumeControl.knob(volumeKnob.knobEvents);
    },
  },

  mounted() {
    Vue.nextTick(() => {
      this.init();
    });
  },
};
</script>
