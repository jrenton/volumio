// FUNCTIONS
// ----------------------------------------------------------------------------------------------------

$(function() {
    window.volumio = window.volumio || {};
    
    window.volumio.conn = new WebSocket('ws://192.168.10.10:8081');
    window.volumio.conn.onopen = function(e) {
        console.log("Connection established!");
    };

    window.volumio.conn.onmessage = function(e) {
        var data = JSON.parse(e.data);
        console.log(data);
        
        $.each(data, function(index, dataItem) {
            setState(dataItem);
        });
    };
});

function setState(data) {
    if (data.artist) {
        window.GUI.currentsong.artist = data.artist;
    }
    
    if (data.album) {              
        window.GUI.currentsong.album = data.album;
    }
    
    if (data.title) {              
        window.GUI.currentsong.title = data.title;
    }
    
    if (data.state && window.GUI.currentsong.type != data.ServiceType) {
        
        window.GUI.currentsong.state = data.state;
        if (typeof data.elapsed != "undefined" && typeof data.time != "undefined") {
            window.GUI.currentsong.elapsed = parseFloat(data.elapsed);
            window.GUI.currentsong.time = parseFloat(data.time);
            refreshTimer(data.elapsed, data.time, data.state);
            refreshKnob();
        }
    }
    
    if (data.ServiceType) {
        window.GUI.currentsong.type = data.ServiceType;
    }
    
    if (data.coverart) {
        showCoverImage(data.coverart);
    }
}
  
function sendCmd(inputcmd) {
    AjaxUtils.get('player2?cmd=' + inputcmd, {}, function(data) {
        GUI.halt = 1;
    });
}

function sendPLCmd(inputcmd) {
    AjaxUtils.get("sendCommand?cmd=" + inputcmd, {}, function(data) {
        GUI.halt = 1;
    });
}

function backendRequestPandora(gui) {
    AjaxUtils.post("player", { serviceType: "Pandora", cmd: "status" }, function(data) {
        console.log("PANDORA BACKEND REQUEST");
        console.log(data);
        $.each(data, function(index, dataItem) {
            console.log(dataItem);
            setState(dataItem);
        });
        
        if(data.base64) {
            showCoverImageFromBase64(data.base64);
        }
        
        $('#loader').hide();
    }, function(a, b, c) {
        // setTimeout(function() {
        //     GUI.state = 'disconnected';
        //     $('#loader').show();
        //     $('#countdown-display').countdown('pause');
        //     window.clearInterval(GUI.currentKnob);
        //     backendRequest();
        // }, 2000);
    });
}

function backendRequest(gui) {
    AjaxUtils.get("playerEngine?state=" + gui.MpdState['state'], {}, function(data) {
        console.log("BACKEND REQUEST");
        console.log(data);
        gui.MpdState = data;
        if(data.base64) {
            showCoverImageFromBase64(data.base64);
        }
        renderUI(gui);
        $('#loader').hide();
        //backendRequest(gui);
    }, function(a, b, c) {
        // setTimeout(function() {
        //     GUI.state = 'disconnected';
        //     $('#loader').show();
        //     $('#countdown-display').countdown('pause');
        //     window.clearInterval(GUI.currentKnob);
        //     backendRequest();
        // }, 2000);
    });
}

function backendRequestSpop(gui) {
    var state = gui.SpopState['state'];
    
    if (state != 'pause' && state != 'play') {
        removeCoverImage();
    }
    
    AjaxUtils.get("playerEngineSpop?state=" + state, {}, function(data) {
        if (data != '') {
            if (data && data.state == "play") {
                console.log("SPOTIFY BACKEND");
                console.log(data);
                data.ServiceType = "Spotify";
                gui.SpopState = data;
                getSpopImage(data.uri);
                renderUI(gui);
                setState(data);
            }
        } else {
            // setTimeout(function() {
            //     backendRequestSpop();
            // }, 5000);
        }
    }, function(a, b, c) {
        // setTimeout(function() {
        //         backendRequestSpop();
        //     }, 5000);
    });
}

function toggleActive($ele, $parent) {
    if(!$parent) {
    	$parent = $ele.parent();
    }
    
    $parent.siblings().removeClass('active');

    $parent.addClass('active');
}

// Non-caching version of getPlaylist
function getPlaylist() {
    sendCommand("spop-qls", null, function(data) {
        if(data) {
            window.GUI.playlist.spotifySongs = data.tracks; 
        }
    });
    
    sendCommand("playlist", null, function(data) {
        if(data) {
            console.log(data);
            window.GUI.playlist.mpdSongs = data;
        }
    });
}

function parsePath(str) {
	var songpath = '';

	if(str) {
		var cutpos = str.lastIndexOf("/");

		//-- verify this switch! (Orion)
		if (cutpos !== -1) {
	        songpath = str.slice(0,cutpos);
		} else {
			songpath = '';
		}
	}
	
	return songpath;
}

function removeCoverImage() {
    //$("#playbackCover").removeClass("coverImage");
}

function getSpopImage(uri) {
    if (uri) {
        sendCommand("spop-uimage", { path: uri, p2: 2 }, function(data) {
            if (data) {
                if (!data.error) {
                    showCoverImageFromBase64(data.data);
                }
            }
        });
    }
}

function showCoverImageFromBase64(base64) {
    showCoverImage("data:image/gif;base64," + base64);
}

function showCoverImage(imgUrl) {
    var visible = $(".visible-phone:visible");
    var backgroundSize = "contain";
    
    if(visible.length > 0) {
        backgroundSize = "cover";
    }
    
    $("#dynamicCss").text("#playbackCover.coverImage:after{background:url(" + imgUrl + ") no-repeat 50% 0% fixed;background-size:" + backgroundSize + ";}");
    $("#playbackCover").addClass("coverImage");
}

function gotoPlayback(track) {
    $("#open-playback").find("a").click();
}

function sendCommands(commands, callback, fail) {
    $.each(commands, function(index, data) {
        sendCommand(data.name, data.data, callback, fail);
    });
}

function sendCommand(command, data, callback, fail) {
    if(data) {
        if(typeof data !== "object") {
            data = { path: data };
        }
    }
    
    getDB(command, data, null, null, callback, fail);
}

function getDB(cmd, commandData, browsemode, uplevel, callback, fail) {
    
    var path = commandData;
    
    if(commandData) {
        if(typeof commandData !== "object") {
            commandData = { path: commandData };
        }
    }
    
	if (cmd == 'filepath') {
		callback = function(data) {
			populateDB(data, path, uplevel);
		};
	}

	if (cmd == 'search') {
		var keyword = $('#db-search-keyword').val();
		commandData = { 'query': keyword };
		cmd = "search&querytype=" + browsemode;
		callback = function(data) {
			populateDB(data, path, uplevel, keyword);
			$("#open-panel-sx a").click();
		};
	}
    
    var uri = "sendCommand?cmd=" + cmd;
    
    AjaxUtils.post(uri, commandData, function(data) {
        if (typeof callback === "function") {
            callback(data);
        }
    });
}

function populateDB(data, path, uplevel, keyword){
	if (path)  {
        GUI.currentpath = path;
    }
    
    GUI.browse.files = [];
    GUI.browse.mpdDirectories = [];
    GUI.browse.spotifyTracks = [];
    GUI.browse.spotifyDirectories = [];
    GUI.browse.pandoraDirectories = [];
    GUI.browse.pandoraSongs = [];
    GUI.browse.isLibrary = false;

	if (!keyword || path == '') {
        if (library && library.isEnabled && !library.displayAsTab) {
            GUI.browse.isLibrary = true;
        }
    }

	for (var i = 0; i < data.length; i++) {
        var dataItem = data[i];
        if (dataItem.Type == 'MpdFile' && dataItem.ServiceType == "Mpd") {
            GUI.browse.files.push(dataItem);
        } else if (dataItem.Type == 'MpdDirectory' && dataItem.ServiceType == "Mpd")  {
            GUI.browse.mpdDirectories.push(dataItem);            
        } else if (dataItem.Type == 'SpopTrack' && dataItem.ServiceType == "Spotify") {
            GUI.browse.spotifyTracks.push(dataItem);            
        } else if (dataItem.Type == 'SpopDirectory' && dataItem.ServiceType == "Spotify") {
            GUI.browse.spotifyDirectories.push(dataItem);            
        } else if (dataItem.Type == 'PandoraDirectory' || dataItem.Type == 'PandoraStation' && dataItem.ServiceType == "Pandora") {
            GUI.browse.pandoraDirectories.push(dataItem);            
        }
	}

	if (typeof data[0].DisplayPath != 'undefined') {
		$('#db-currentpath span').html(data[0].DisplayPath);

	} else {
		$('#db-currentpath span').html(path);
	}

	if (uplevel) {
		$('#db-' + GUI.currentDBpos[GUI.currentDBpos[10]]).addClass('active');
		customScroll('db', GUI.currentDBpos[GUI.currentDBpos[10]]);
	} else {
		customScroll('db', 0, 0);
	}
}

function renderUI(gui) {
    console.log("does the ui render?");
    console.log(gui);
    if (gui.SpopState['state'] == 'play' || gui.SpopState['state'] == 'pause') {
       // If Spop is playing, temporarily redirect button control and title display to Spop
        gui.currentsong.state = gui.SpopState['state'];

        // Combine the Spop state array with the Mpd state array - any state variable defined by Spop will overwrite the corresponding Mpd state variable
        var objectCombinedState = $.extend({}, gui.MpdState, gui.SpopState);
        updateGUI(objectCombinedState);
        refreshTimer(parseInt(objectCombinedState['elapsed']), parseInt(objectCombinedState['time']), objectCombinedState['state']);
        refreshKnob(objectCombinedState);
    } else {
       // Else UI should be connected to MPD status
        gui.currentsong.state = gui.MpdState['state'];
        updateGUI(gui.MpdState);
        refreshTimer(parseInt(gui.MpdState['elapsed']), parseInt(gui.MpdState['time']), GUI.MpdState['state']);
        refreshKnob(gui.MpdState);
    }

    if (gui.state != 'disconnected') {
        $('#loader').hide();
    }

    if (gui.MpdState['playlist'] != gui.playlist) {
        //GUI.MpdState
        getPlaylist();
        //gui.playlist = gui.MpdState['playlist'];
    }

    gui.halt = 0;
}

// update interface
function updateGUI(objectInputState) {
    console.log("updating ui with state");
    console.log(objectInputState);

	var current = parseInt(objectInputState['song']) + 1;

	if (!isNaN(current)) {
		$('.playlist li:nth-child(' + current + ')').addClass('active');
	}

	// show UpdateDB icon
	if (typeof GUI.MpdState['updating_db'] != 'undefined') {
		$('.open-panel-sx').html('<i class="fa fa-refresh fa-spin"></i> Updating');
	} else {
		$('.open-panel-sx').html('<i class="fa fa-music sx"></i> Browse');
	}

    // check song update
    if (GUI.currentsong != objectInputState['currentsong']) {
        if ($('#panel-dx').hasClass('active')) {
            var current = parseInt(objectInputState['song']);
            customScroll('pl', current);
        }
    }
    // common actions

    // Don't update the knob if it's currently being changed
    var volume = $('#volume');
    if (volume[0] && (volume[0].knobEvents === undefined || !volume[0].knobEvents.isSliding)) {
        volume.val((objectInputState['volume'] == '-1') ? 100 : objectInputState['volume']).trigger('change');
    }
    console.log(objectInputState);
    //PlaybackVol.song = { Artist: objectInputState['currentartist'], Title: objectInputState['currentsong'] };
    GUI.currentsong.artist = objectInputState['currentartist'];
    GUI.currentsong.title = objectInputState['currentsong'];

    if (objectInputState['repeat'] == 1) {
        $('#repeat').addClass('btn-primary');
    } else {
        $('#repeat').removeClass('btn-primary');
    }
    if (objectInputState['random'] == 1) {
        $('#random').addClass('btn-primary');
    } else {
        $('#random').removeClass('btn-primary');
    }
    if (objectInputState['consume'] == 1) {
        $('#consume').addClass('btn-primary');
    } else {
        $('#consume').removeClass('btn-primary');
    }
    if (objectInputState['single'] == 1) {
        $('#single').addClass('btn-primary');
    } else {
        $('#single').removeClass('btn-primary');
    }

    GUI.halt = 0;

	//Change Name according to Now Playing
	if (GUI.currentsong.artist && GUI.currentsong.title) {
		document.title = GUI.currentsong.title + ' - ' + GUI.currentsong.artist + ' - ' + 'Volumio';
	} else {
		document.title = 'Volumio - Audiophile Music Player';
    }
}

// update countdown
function refreshTimer(startFrom, stopTo, state){
	var $countdownDisplay = $('#countdown-display');
    if (state == 'play') {
        $countdownDisplay.countdown('destroy');
        $countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
    } else if (state == 'pause') {
        $countdownDisplay.countdown('destroy');
        $countdownDisplay.countdown({since: -(startFrom), compact: true, format: 'MS'});
        $countdownDisplay.countdown('pause');
    } else if (state == 'stop') {
        $countdownDisplay.countdown('destroy');
        $countdownDisplay.countdown({since: 0, compact: true, format: 'MS'});
        $countdownDisplay.countdown('pause');
    }
}

// update time knob
function refreshKnob() {
    window.clearInterval(GUI.currentKnob)
    var initTime = (GUI.currentsong.elapsed / GUI.currentsong.time) * 1000;
    var delta = GUI.currentsong.time / 1000;
    var $time = $("#time");
    $time.val(initTime).trigger('change');
    if (GUI.currentsong.state == 'play') {
        GUI.currentKnob = setInterval(function() {
            initTime = initTime + 1;
            window.GUI.currentsong.elapsed = parseFloat(window.GUI.currentsong.elapsed) + parseFloat(delta);
            $time.val(initTime).trigger('change');
        }, delta * 1000);
    }
}

// time conversion
function timeConvert(seconds) {
    var display;
    if (isNaN(seconds)) {
    	display = '';
    } else {
    	var minutes = Math.floor(seconds / 60);
    	seconds -= minutes * 60;
    	var mm = (minutes < 10) ? ('0' + minutes) : minutes;
    	var ss = (seconds < 10) ? ('0' + seconds) : seconds;
    	display = mm + ':' + ss;
    }
    
    return display;
}

// set volume with knob
function setVolume(val) {
    GUI.volume = val;

	// Push volume updates into the MPD state array, since we opted not to get
	// volume change updates from MPD daemon
	if ("volume" in GUI.MpdState) {
		GUI.MpdState.volume = val;
	}

    GUI.halt = 1;

    $('#volumemute').removeClass('btn-primary');
    sendCmd('setvol ' + val);
}

// adjust knob with volume
function adjustKnobVolume(val) {
    $('#volume').val(val);
}

// scrolling
function customScroll(list, destination, speed) {
    if (typeof(speed) === 'undefined') speed = 500;

    var $window = $(window);
    var entryheight = parseInt(1 + $('#' + list + '-1').height());
    var centerheight = parseInt($window.height()/2);
    var scrolltop = $window.scrollTop();
    if (list == 'db') {
        var scrollcalc = parseInt((destination)*entryheight - centerheight);
        var scrolloffset = scrollcalc;
    } else if (list == 'pl') {
        //var scrolloffset = parseInt((destination + 2)*entryheight - centerheight);
        var scrollcalc = parseInt((destination + 2)*entryheight - centerheight);
        if (scrollcalc > scrolltop) {
            var scrolloffset = '+=' + Math.abs(scrollcalc - scrolltop) + 'px';
        } else {
            var scrolloffset = '-=' + Math.abs(scrollcalc - scrolltop) + 'px';
        }
    }
    if (scrollcalc > 0) {
        $.scrollTo( scrolloffset , speed );
    } else {
        $.scrollTo( 0 , speed );
    }
}

function randomScrollPL() {
    var n = $(".playlist li").size();
    var random = 1 + Math.floor(Math.random() * n);
    customScroll('pl', random);
}

function randomScrollDB() {
    var n = $(".database li").size();
    var random = 1 + Math.floor(Math.random() * n);
    customScroll('db', random);
}