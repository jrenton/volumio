$(function () {
	$('select').selectpicker();

  $('.btn-group a').click(function() {
		var $this = $(this);
    $this.siblings().removeClass('active');
    $this.addClass('active');
  });
});
