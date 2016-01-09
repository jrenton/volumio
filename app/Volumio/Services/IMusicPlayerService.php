<?php

namespace App\Volumio\Services;

interface IMusicPlayerService
{
    function play($song = null);
    function stop();
    function pause();
    function next();
    function previous();
    function status();
    function image($song = null);
    function repeat();
    function shuffle();
    function search($query, $searchType);
    function getQueue();
    function clearQueue();
    function playPlaylist($playlist, $song = null);
    function add($song);
    function addPlaylist($playlist, $song = null);
    function getPlaylist($playlist);
    function getPlaylists();
    function rateUp($song);
    function rateDown($song);
    function removeQueue($song);
    function removePlaylist($song);
    function openService();
}