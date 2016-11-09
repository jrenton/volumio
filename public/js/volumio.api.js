// FUNCTIONS
// ----------------------------------------------------------------------------------------------------
function sendCmd(inputcmd) {
  AjaxUtils.get('player2?cmd=' + inputcmd, {}, function(data) {
    GUI.halt = 1;
  });
}

function backendRequestPandora(gui) {
  AjaxUtils.post("player", {
    serviceType: "Pandora",
    cmd: "status"
  }, function(data) {
    console.log("PANDORA BACKEND REQUEST");
    console.log(data);
    $.each(data, function(index, dataItem) {
      console.log(dataItem);
      setState(dataItem);
    });

    showCoverImage(data);

    $('#loader').hide();
  }, function(a, b, c) {});
}

function backendRequestSpop(gui) {
  var state = gui.SpopState['state'];

  if (state != 'paused' && state != 'playing') {
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
  }, function(a, b, c) {});
}

function toggleActive($ele, $parent) {
  if (!$parent) {
    $parent = $ele.parent();
  }

  $parent.siblings().removeClass('active');

  $parent.addClass('active');
}

// Non-caching version of getPlaylist
function getPlaylist() {
  sendCommand("spop-qls", null, function(data) {
    if (data) {
      window.GUI.playlist.spotifySongs = data.tracks;
    }
  });

  sendCommand("playlist", null, function(data) {
    if (data) {
      console.log(data);
      window.GUI.playlist.mpdSongs = data;
    }
  });
}

function parsePath(str) {
  var songpath = '';

  if (str) {
    var cutpos = str.lastIndexOf("/");

    //-- verify this switch! (Orion)
    if (cutpos !== -1) {
      songpath = str.slice(0, cutpos);
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
    sendCommand("spop-uimage", {
      path: uri,
      p2: 2
    }, function(data) {
      if (data) {
        if (!data.error) {
          var song = {
            base64: data.data
          };
          showCoverImage(song);
        }
      }
    });
  }
}

function showCoverImage(song) {
  if ($.isArray(song) && song.length == 1) {
    song = song[0];
  }

  if (!song || song.state == "stop" || !song.serviceType) {
    return;
  }

  var imgUrl = getImgUrl(song);

  if (!imgUrl) {
    AjaxUtils.post("player", {
      cmd: "image",
      song: song,
      serviceType: song.serviceType
    }, function(data) {
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

  if (visible.length > 0) {
    backgroundSize = "cover";
  }

  $("#dynamicCss").text("#playbackCover.coverImage:after{background:url(" + imgUrl + ") no-repeat 50% 50% fixed;background-size:" + backgroundSize + ";}");
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
  if (data) {
    if (typeof data !== "object") {
      data = {
        path: data
      };
    }
  }

  getDB(command, data, null, null, callback, fail);
}

function getDB(cmd, commandData, browsemode, uplevel, callback, fail) {
  //'filepath', dir.directory, 'file', 0
  var path = commandData;

  if (commandData) {
    if (typeof commandData !== "object") {
      commandData = {
        path: commandData
      };
    }
  }

  if (cmd == 'filepath') {
    callback = function(data) {
      populateDB(data, path, uplevel);
    };
  }

  if (cmd == 'search') {
    var keyword = $('#db-search-keyword').val();
    commandData = {
      'query': keyword
    };
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

function populateDB(data, path, uplevel, keyword) {
  if (path) {
    GUI.currentpath = path;
  }

  GUI.browse.files = [];
  GUI.browse.mpdDirectories = [];
  GUI.browse.spotifyTracks = [];
  GUI.browse.spotifyDirectories = [];
  GUI.browse.directories = [];
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
    console.log(dataItem);
    if (dataItem.Type == 'MpdFile' && dataItem.serviceType == "Mpd") {
      GUI.browse.files.push(dataItem);
    } else if (dataItem.Type == 'MpdDirectory' && dataItem.serviceType == "Mpd") {
      GUI.browse.mpdDirectories.push(dataItem);
    } else if (dataItem.Type == 'SpopTrack' && dataItem.serviceType == "Spotify") {
      GUI.browse.spotifyTracks.push(dataItem);
    } else if (dataItem.Type == 'SpopDirectory' && dataItem.serviceType == "Spotify") {
      GUI.browse.spotifyDirectories.push(dataItem);
    } else if (dataItem.Type == 'PandoraDirectory' || dataItem.Type == 'PandoraStation' && dataItem.serviceType == "Pandora") {
      GUI.browse.pandoraDirectories.push(dataItem);
    } else if (dataItem.type == "Directory") {
      console.log(dataItem);
      GUI.browse.directories.push(dataItem);
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

  GUI.halt = 0;
}

function initializePlaybackKnob() {
  $('.playbackknob').knob({
    inline: false,
    change: function(value) {
      if (GUI.currentsong.state != 'stop') {
        window.clearInterval(GUI.currentKnob);
      } else $('#time').val(0);
    },
    release: function(value) {
      if (GUI.currentsong.state != 'stop') {
        GUI.halt = 1;

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
        $countdownDisplay.countdown({
          since: -seekto,
          compact: true,
          format: 'MS'
        });
      }
    },
    cancel: function() {
      //console.log('cancel : ', this);
    },
    draw: function() {}
  });
}
