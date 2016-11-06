jQuery(document).ready(function($){ 'use strict';

    // BUTTONS
    // ----------------------------------------------------------------------------------------------------
    // playback
    $('.btn-cmd').click(function(){
        var cmd;
        var $this = $(this),
            id = $this.attr('id');

        if ($this.hasClass('btn-volume')) {
            if (GUI.volume == null ) {
                GUI.volume = $('#volume').val();
            }

            if (id == 'volumedn') {
                var vol = parseInt(GUI.volume) - 1;
                GUI.volume = vol;
                $('#volumemute').removeClass('btn-primary');
            }
            else if (id == 'volumeup') {
                var vol = parseInt(GUI.volume) + 1;
                GUI.volume = vol;
                $('#volumemute').removeClass('btn-primary');
            }
            else if (id == 'volumemute') {
                var $volume = $('#volume');
                if ($volume.val() != 0 ) {
                    GUI.volume = $volume.val();
                    $this.addClass('btn-primary');
                    var vol = 0;
                }
                else {
                    $this.removeClass('btn-primary');
                    var vol = GUI.volume;
                }
            }
            //console.log('volume = ', GUI.volume);
            sendCmd('setvol ' + vol);
            return;
        }

        //toggle buttons
        if ($this.hasClass('btn-toggle')) {
            if ($this.hasClass('btn-primary')) {
                cmd = id + ' 0';
            } else {
                cmd = id + ' 1';
            }
        // send command
        } else {
            cmd = id;
        }
        
        sendCmd(cmd);
    });

    initializePlaybackKnob();
    initializeVolumeKnob();

    // PLAYLIST
    // ----------------------------------------------------------------------------------------------------

    var $playlist = $('.playlist');
    // click on playlist entry
    $playlist.on('click', '.pl-entry', function() {
        // var pos = $('.playlist .pl-entry').index(this);
        // var cmd = 'play ' + pos;
        // sendCmd(cmd);
        // GUI.halt = 1;

        // $('.playlist li').removeClass('active');
        // $(this).parent().addClass('active');
    });

    // click on playlist actions
    $playlist.on('click', '.pl-action', function(event) {
        // event.preventDefault();
        // var pos = $('.playlist .pl-action').index(this);
        // var cmd = 'trackremove&songid=' + pos;
        // notify('remove', '');
        // sendPLCmd(cmd);
    });

    // click on playlist save button
    $('#pl-controls').on('click', '#pl-btnSave', function(event) {
    	var plname = $("#pl-saveName").val();
    	if (plname) {
    	        sendPLCmd('savepl&plname=' + plname);
    		notify('savepl', plname);
    	} else {
    		notify('needplname', '');
    	}
    });

    // click on playlist tab
    $('#open-panel-dx a').click(function(){
        var current = parseInt(GUI.MpdState['song']);
        customScroll('pl', current, 200); // runs when tab ready!
        getPlaylist();
    });

    // DATABASE
    // ----------------------------------------------------------------------------------------------------

    // click on database "back"
    // $('#db-back').click(function() {
    //     --GUI.currentDBpos[10];
    //     var path = GUI.currentpath;
    //     var cutpos=path.lastIndexOf("/");
    //     if (cutpos !=-1) {
    //         var path = path.slice(0,cutpos);
    //     }  else {
    //         path = '';
    //     }
    //     getDB('filepath', path, GUI.browsemode, 1);
    // });

    var $database = $("#database").find(".database");

    // click search results in DB
    $database.on('click', '.search-results', function() {
        sendCommand('filepath', GUI.currentpath);
    });

    $('.context-menu a').click(function(){
        var path = GUI.DBentry[0];
        var title = GUI.DBentry[3];
        var artist = GUI.DBentry[4];
        var album = GUI.DBentry[5];
        GUI.DBentry[0] = '';
        var $this = $(this),
            cmd = $this.data('cmd');
        var validCommands = ['add', 'addplay', 'addreplaceplay',
                             'update', 'spop-uplay', 'spop-uadd',
                             'spop-playplaylistindex',
                             'spop-addplaylistindex', 'spop-stop']

        if (validCommands.indexOf(cmd) !== -1) {
            sendCommand(cmd, path);
            notify(cmd, path);
        }

        if (cmd == 'addreplaceplay') {
            if (path.indexOf("/") == -1) {
	            $("#pl-saveName").val(path);
            }
            else {
	            $("#pl-saveName").val("");
			}
        }

        if (cmd == 'spop-searchtitle') {
			$('#db-search-keyword').val('track:' + title);
			getDB('search', '', 'file');
        }
        else if (cmd == 'spop-searchartist') {
			$('#db-search-keyword').val('artist:' + artist);
			getDB('search', '', 'file');
        }
        else if (cmd == 'spop-searchalbum') {
			$('#db-search-keyword').val('album:' + album);
			getDB('search', '', 'file');
        }
    });

    // multipurpose debug buttons
    $('#db-debug-btn').click(function(){
        var scrollTop = $(window).scrollTop();
        // console.log('scrollTop = ', scrollTop);
    });
    $('#pl-debug-btn').click(function(){
        randomScrollPL();
    });

    // open tab from external link
    var url = document.location.toString();
    if (url.match('#') && !url.match('#!')) {
        $('#menu-bottom a[href=#'+url.split('#')[1]+']').tab('show') ;
    }

    // do not scroll with HTML5 history API
    $('#menu-bottom a').on('shown', function (e) {
        if(history.pushState) {
            //history.pushState(null, null, e.target.hash);
        }
        else {
            window.location.hash = e.target.hash; //Polyfill for old browsers
        }
    });

    // playlist search
    $("#pl-filter").keyup(function() {
        $.scrollTo(0 , 500);
        var filter = $(this).val(), count = 0;
        $(".playlist li").each(function() {
            var $this = $(this);
            if ($this.text().search(new RegExp(filter, "i")) < 0) {
                $this.hide();
            } else {
                $this.show();
                count++;
            }
        });
        var numberItems = count;
        var s = (count == 1) ? '' : 's';
        if (filter != '') {
            $('#pl-filter-results').html('<i class="fa fa-search sx"></i> ' + (+count) + ' result' + s + ' for "<em class="keyword">' + filter + '</em>"');
        } else {
            $('#pl-filter-results').html('');
        }
    });

    // tooltips
    var $toolTip = $('.ttip');
    if( $toolTip.length ){
        $toolTip.tooltip();
    }
});

// check active tab
(function() {
    hidden = 'hidden';
    // Standards:
    if (hidden in document)
        document.addEventListener('visibilitychange', onchange);
    else if ((hidden = 'mozHidden') in document)
        document.addEventListener('mozvisibilitychange', onchange);
    else if ((hidden = "webkitHidden") in document)
        document.addEventListener('webkitvisibilitychange', onchange);
    else if ((hidden = "msHidden") in document)
        document.addEventListener('msvisibilitychange', onchange);
    // IE 9 and lower:
    else if ('onfocusin' in document)
        document.onfocusin = document.onfocusout = onchange;
    // All others:
    else
        window.onpageshow = window.onpagehide
            = window.onfocus = window.onblur = onchange;

    function onchange (evt) {
        var v = 'visible', h = 'hidden',
            evtMap = {
                focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h
            };

        evt = evt || window.event;
        if (evt.type in evtMap) {
            document.body.className = evtMap[evt.type];
            // console.log('boh? = ', evtMap[evt.type]);
        } else {
            document.body.className = this[hidden] ? 'hidden' : 'visible';
            if (this[hidden]) {
                GUI.visibility = 'hidden';
                // console.log('focus = hidden');
            } else {
                GUI.visibility = 'visible';
                // console.log('focus = visible');
            }
        }
    }
})();
