var store = require('../store');

module.exports = {
    refreshKnob: function() {
        window.clearInterval(store.state.currentKnob)
        var initTime = (store.state.currentsong.elapsed / store.state.currentsong.time) * 1000;
        var delta = store.state.currentsong.time / 1000;
        var $time = $("#time");
        $time.val(initTime).trigger('change');
        if (store.state.currentsong.state == 'play' && store.state.currentsong.elapsed != 'NaN') {
            store.state.currentKnob = setInterval(function() {
                initTime = initTime + 1;
                store.state.currentsong.elapsed = parseFloat(store.state.currentsong.elapsed.toString()) + parseFloat(delta.toString());
                $time.val(initTime).trigger('change');
            }, delta * 1000);
        }
    },
    refreshTimer: function() {
        var $countdownDisplay = $('#countdown-display');
        if (store.state.currentsong.state == 'play') {
            $countdownDisplay.countdown('destroy');
            $countdownDisplay.countdown({since: -(store.state.currentsong.elapsed), compact: true, format: 'MS'});
        } else if (store.state.currentsong.state == 'pause') {
            $countdownDisplay.countdown('destroy');
            $countdownDisplay.countdown({since: -(store.state.currentsong.elapsed), compact: true, format: 'MS'});
            $countdownDisplay.countdown('pause');
        } else if (store.state.currentsong.state == 'stop') {
            $countdownDisplay.countdown('destroy');
            $countdownDisplay.countdown({since: 0, compact: true, format: 'MS'});
            $countdownDisplay.countdown('pause');
        }
    }
}