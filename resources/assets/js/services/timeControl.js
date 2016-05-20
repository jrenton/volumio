var store = require('../store');

module.exports = {
    refreshKnob: function() {
        window.clearInterval(GUI.currentKnob)
        window.GUI.currentsong.percentcomplete = GUI.currentsong.elapsed / GUI.currentsong.time;
        var initTime = window.GUI.currentsong.percentcomplete * 1000;
        var delta = GUI.currentsong.time / 1000;
        var $time = $("#time");
        $time.val(initTime).trigger('change');
        if (GUI.currentsong.state == 'play') {
            GUI.currentKnob = setInterval(function() {
                initTime = initTime + 1;
                window.GUI.currentsong.elapsed = parseFloat(window.GUI.currentsong.elapsed) + parseFloat(delta);
                window.GUI.currentsong.percentcomplete = window.GUI.currentsong.elapsed / GUI.currentsong.time;
                $time.val(initTime).trigger('change');
            }, delta * 1000);
        }
        // window.clearInterval(store.state.currentKnob)
        // var elapsedTime = parseInt(store.state.currentsong.elapsed);
        // var totalTime = parseInt(store.state.currentsong.time);
        
        // store.state.currentsong.percentcomplete = elapsedTime / totalTime;        
        // var initTime = store.state.currentsong.percentcomplete * 1000;
        // var delta = totalTime / 1000;
        // var $time = $("#time");
        // $time.val(initTime).trigger('change');
        // if (store.state.currentsong.state == 'play' && store.state.currentsong.elapsed != 'NaN') {
        //     store.state.currentKnob = setInterval(function() {
        //         initTime = initTime + 1;
        //         store.state.currentsong.elapsed = parseFloat(store.state.currentsong.elapsed.toString()) + parseFloat(delta.toString());
        //         store.state.currentsong.percentcomplete = parseInt(store.state.currentsong.elapsed) / parseInt(store.state.currentsong.time);        
        //         $time.val(initTime).trigger('change');
        //     }, delta * 1000);
        // }
    },
    refreshTimer: function() {
        // var $countdownDisplay = $('#countdown-display');
        // if (state == 'play') {
        //     $countdownDisplay.countdown('destroy');
        //     $countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
        // } else if (state == 'pause') {
        //     $countdownDisplay.countdown('destroy');
        //     $countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
        //     $countdownDisplay.countdown('pause');
        // } else if (state == 'stop') {
        //     $countdownDisplay.countdown('destroy');
        //     $countdownDisplay.countdown({since: 0, compact: true, format: 'MS'});
        //     $countdownDisplay.countdown('pause');
        // }
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