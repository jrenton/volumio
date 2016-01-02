// FUNCTIONS
// ----------------------------------------------------------------------------------------------------

$(function() {
    if (Notification) {
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    }
    
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
    
    window.volumio.songChanger = new WebSocket('ws://192.168.10.10:8082');
    window.volumio.songChanger.onopen = function(e) {
        console.log("Notifier Connection established!");
    };

    window.volumio.songChanger.onmessage = function(e) {
        var data = JSON.parse(e.data);
        console.log(data);
        setState(data);
    };
});

function setState(song) {
    notifyUser(song);
    
    if (song.id) {
        window.GUI.currentsong.id = song.id;
    }
    
    if (song.artist) {
        window.GUI.currentsong.artist = song.artist;
    }
    
    if (song.album) {              
        window.GUI.currentsong.album = song.album;
    }
    
    if (song.title) {              
        window.GUI.currentsong.title = song.title;
    }
    
    if (song.state && (window.GUI.currentsong.type == song.serviceType && song.serviceType == "Pandora" || song.state != "stop")) {
        window.GUI.currentsong.state = song.state;
        if (typeof song.elapsed != "undefined" && typeof song.time != "undefined") {
            
            window.GUI.currentsong.elapsed = parseFloat(song.elapsed);
            window.GUI.currentsong.time = parseFloat(song.time);
            refreshTimer(song.elapsed, song.time, song.state);
            refreshKnob();
        }
    }
    
    if (song.serviceType && song.state != "stop") {
        window.GUI.currentsong.type = song.serviceType;
    }
    
    showCoverImage(song);
}

function notifyUser(song) {
    var artist = window.GUI.currentsong.artist;
    var album = window.GUI.currentsong.album;
    var title = window.GUI.currentsong.title;
    
    if (song.state == "play" && (song.artist != artist || song.title != title)) {
        if (!song.artist) {
            song.artist = artist;
        }
        
        if (!song.title) {
            song.title = title;
        }
        
        showNotification(song.title, "by " + song.artist, song.serviceType);
    }
}

function showNotification(title, message, type) {
    if (!Notification) {
        return;
    }

    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    } else {
        var notification = new Notification(title, {
            icon: "/images/" + type + ".png",
            body: message,
        });

        notification.onclick = function () {
            window.focus();
            notification.close();
        };
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
        
        showCoverImage(data);
        
        $('#loader').hide();
    }, function(a, b, c) {
    });
}

function backendRequest(gui) {
    AjaxUtils.get("playerEngine?state=" + gui.MpdState['state'], {}, function(data) {
        console.log("BACKEND REQUEST");
        console.log(data);
        gui.MpdState = data;
        showCoverImage(data);
        renderUI(gui);
        $('#loader').hide();
    }, function(a, b, c) {
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
                data.serviceType = "Spotify";
                gui.SpopState = data;
                getSpopImage(data.uri);
                renderUI(gui);
                setState(data);
            }
        }
    }, function(a, b, c) {
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
                    var song = { base64: data.data };
                    showCoverImage(song);
                }
            }
        });
    }
}

function showCoverImage(song) {
    console.log("show cover image");
    console.log(song);
    if ($.isArray(song) && song.length == 1) {
        song = song[0];
    }
    
    if (!song || song.state == "stop" || !song.serviceType) {
        return;
    }
    
    var imgUrl = getImgUrl(song);
    
    if (!imgUrl) {
        console.log("no imgUrl");
        AjaxUtils.post("player", { cmd: "image", song: song, serviceType: song.serviceType }, function(data) {
            imgUrl = getImgUrl(data);
            setCoverImage(imgUrl);
        });
    }
    
    setCoverImage(imgUrl);
}

function setCoverImage(imgUrl) {
    if (!imgUrl) {
        return;
    }
    
    var visible = $(".visible-phone:visible");
    var backgroundSize = "contain";
    
    if(visible.length > 0) {
        backgroundSize = "cover";
    }
    
    $("#dynamicCss").text("#playbackCover.coverImage:after{background:url(" + imgUrl + ") no-repeat 50% 0% fixed;background-size:" + backgroundSize + ";}");
    $("#playbackCover").addClass("coverImage");  
}

function getImgUrl(song) {
    
    if (!song) {
        return "";
    }
    
    var imgUrl = "";
    
    if (song.base64) {
        imgUrl = "data:image/gif;base64," + song.base64;
    }
    
    if (song.coverart) {
        imgUrl = song.coverart;
    }
    
    return imgUrl;
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
        if (dataItem.Type == 'MpdFile' && dataItem.serviceType == "Mpd") {
            GUI.browse.files.push(dataItem);
        } else if (dataItem.Type == 'MpdDirectory' && dataItem.serviceType == "Mpd")  {
            GUI.browse.mpdDirectories.push(dataItem);            
        } else if (dataItem.Type == 'SpopTrack' && dataItem.serviceType == "Spotify") {
            GUI.browse.spotifyTracks.push(dataItem);            
        } else if (dataItem.Type == 'SpopDirectory' && dataItem.serviceType == "Spotify") {
            GUI.browse.spotifyDirectories.push(dataItem);            
        } else if (dataItem.Type == 'PandoraDirectory' || dataItem.Type == 'PandoraStation' && dataItem.serviceType == "Pandora") {
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
    
    GUI.currentsong.artist = objectInputState['artist'];
    GUI.currentsong.title = objectInputState['title'];

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

function initializePlaybackKnob() {
    $('.playbackknob').knob({
        inline: false,
		change : function (value) {
            if (GUI.currentsong.state != 'stop') {
				// console.log('GUI.halt (Knobs)= ', GUI.halt);
				window.clearInterval(GUI.currentKnob)
				//$('#time').val(value);
				//console.log('click percent = ', value);
				// implementare comando
			} else $('#time').val(0);
        },
        release : function (value) {
			if (GUI.currentsong.state != 'stop') {
				//console.log('release percent = ', value);
				GUI.halt = 1;
				// console.log('GUI.halt (Knobs2)= ', GUI.halt);
				window.clearInterval(GUI.currentKnob);

				var seekto = 0;
				if (GUI.SpopState['state'] == 'play' || GUI.SpopState['state'] == 'pause') {
					seekto = Math.floor((value * parseInt(GUI.SpopState['time'])) / 1000);
					// Spop expects input to seek in ms
					sendCmd('seek ' + seekto * 1000);
					// Spop idle mode does not detect a seek change, so update UI manually
					AjaxUtils.get('playerEngineSpop?state=manualupdate', {}, function(data) {
							if (data != '') {
								GUI.SpopState = data;
								renderUI();
							}
						});

				} else {
					seekto = Math.floor((value * parseInt(GUI.MpdState['time'])) / 1000);
					sendCmd('seek ' + GUI.MpdState['song'] + ' ' + seekto);

				}

                var $countdownDisplay = $('#countdown-display');
				$('#time').val(value);
				$countdownDisplay.countdown('destroy');
				$countdownDisplay.countdown({since: -seekto, compact: true, format: 'MS'});
			}
        },
        cancel : function () {
            //console.log('cancel : ', this);
        },
        draw : function () {}
    });
}

function initializeVolumeKnob() {
        // volume knob
    var volumeKnob = $('#volume');
    if (volumeKnob.length > 0) {
            volumeKnob[0].isSliding = function() {
        return volumeKnob[0].knobEvents.isSliding;
    }
    volumeKnob[0].setSliding = function(sliding) {
        volumeKnob[0].knobEvents.isSliding = sliding;
    }
    volumeKnob[0].knobEvents = {
        isSliding: false,
        // on release => set volume
    	release: function (value) {
    	    if (this.hTimeout != null) {
                clearTimeout(this.hTimeout);
                this.hTimeout = null;
    	    }
    	    volumeKnob[0].setSliding(false);
            adjustKnobVolume(value);
    	    setVolume(value);
        },
    	hTimeout: null,
    	// on change => set volume only after a given timeout, to avoid flooding with volume requests
    	change: function (value) {
            volumeKnob[0].setSliding(true);
            var that = this;
            if (this.hTimeout == null) {
                this.hTimeout = setTimeout(function(){
                    clearTimeout(that.hTimeout);
                    that.hTimeout = null;
                    setVolume(value);
                }, 200);
            }
        },
        cancel : function () {
            volumeKnob[0].setSliding(false);
        },
        draw : function () {
            // "tron" case
            if(this.$.data('skin') == 'tron') {

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
    volumeKnob.knob(volumeKnob[0].knobEvents);
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