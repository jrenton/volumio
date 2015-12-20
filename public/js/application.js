// Some general UI pack related JS

$(function () {
    // Custom selects
    //$("select").dropkick();
	$("select").selectpicker();
});

$(document).ready(function() {
    // Todo list
    $(".todo li").click(function() {
        $(this).toggleClass("todo-done");
    });

    // Init tooltips
    $("[data-toggle=tooltip]").tooltip("show");

    // Init tags input
    $("#tagsinput").tagsInput();

    // JS input/textarea placeholder
    $("input, textarea").placeholder();

    // Make pagination demo work
    $(".pagination a").click(function() {
        var $parent = $(this).parent();
        if (!$parent.hasClass("previous") && !$parent.hasClass("next")) {
            $parent.siblings("li").removeClass("active");
            $parent.addClass("active");
        }
    });

    $(".btn-group a").click(function() {
        $(this).siblings().removeClass("active");
        $(this).addClass("active");
    });

    // Disable link click not scroll top
    $("a[href='#']").click(function() {
        return false
    });
});

