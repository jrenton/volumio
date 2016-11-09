jQuery(document).ready(function($) {
  'use strict';

  $('.context-menu a').click(function() {
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
      'spop-addplaylistindex', 'spop-stop'
    ]

    if (validCommands.indexOf(cmd) !== -1) {
      sendCommand(cmd, path);
      notify(cmd, path);
    }

    if (cmd == 'addreplaceplay') {
      if (path.indexOf("/") == -1) {
        $("#pl-saveName").val(path);
      } else {
        $("#pl-saveName").val("");
      }
    }

    if (cmd == 'spop-searchtitle') {
      $('#db-search-keyword').val('track:' + title);
      getDB('search', '', 'file');
    } else if (cmd == 'spop-searchartist') {
      $('#db-search-keyword').val('artist:' + artist);
      getDB('search', '', 'file');
    } else if (cmd == 'spop-searchalbum') {
      $('#db-search-keyword').val('album:' + album);
      getDB('search', '', 'file');
    }
  });

  // open tab from external link
  var url = document.location.toString();
  if (url.match('#') && !url.match('#!')) {
    $('#menu-bottom a[href=#' + url.split('#')[1] + ']').tab('show');
  }

  // tooltips
  var $toolTip = $('.ttip');
  if ($toolTip.length) {
    $toolTip.tooltip();
  }
});
