jQuery(document).ready(function($) {
  'use strict';
  if (GUI.state != 'disconnected') {
    $('#loader').hide();
  }

  var $dhcp = $("#dhcp");

  // show / hide static configuration based on select value
  if ($dhcp.length) {
    if ($dhcp.val() == 'false') {
      $('#network-manual-config').show();
    }
    $dhcp.change(function() {
      if ($this.val() == 'true') {
        $('#network-manual-config').hide();
      } else {
        $('#network-manual-config').show();
      }
    });
  }

  var $showAdvancedConfig = $('.show-advanced-config');
  // show advanced options
  if ($showAdvancedConfig.length) {
    $showAdvancedConfig.click(function(e) {
      e.preventDefault();
      var $this = $(this);
      if ($this.hasClass('active')) {
        $('.advanced-config').hide();
        $this.removeClass('active');
        $this.find('i')
          .removeClass('fa fa-minus')
          .addClass('fa fa-plus');
        $this.find('span').html('Show advanced options');
      } else {
        $('.advanced-config').show();
        $this.addClass('active');
        $this.find('i').removeClass('fa fa-plus')
          .addClass('fa fa-minus');
        $this.find('span').html('Hide advanced options');
      }
    });
  }

  // confirm manual data
  if ($('.manual-edit-confirm').length) {
    $this.find('.btn-primary').click(function() {
      $('#mpdconf_editor').show().removeClass('hide');
      $(this).hide();
    });
  }

  var $toggledisplaylib1 = $("#toggledisplaylib1");
  var $toggledisplaylib2 = $("#toggledisplaylib2");
  var $displaylibastabblock = $("#displaylibastabblock");
  if ($toggledisplaylib1.size() > 0 && $toggledisplaylib2.size() > 0 && $displaylibastabblock.size() > 0) {
    if ($toggledisplaylib1.attr("checked") === undefined) {
      $displaylibastabblock.hide("slide");
    }
    $toggledisplaylib1.click(function() {
      $displaylibastabblock.show("slide");
    });
    $toggledisplaylib2.click(function() {
      $displaylibastabblock.hide("slide");
    });
  }
  var $togglespotify1 = $("#togglespotify1");
  var $togglespotify2 = $("#togglespotify2");
  var $displayspotifyblock = $("#displayspotifyblock");
  if ($togglespotify1.size() > 0 && $togglespotify2.size() > 0 && $displayspotifyblock.size() > 0) {
    if ($togglespotify1.attr("checked") === undefined) {
      $displayspotifyblock.hide("slide");
    }
    $togglespotify1.click(function() {
      $displayspotifyblock.show("slide");
    });
    $togglespotify2.click(function() {
      $displayspotifyblock.hide("slide");
    });
  }
});
