<?php

namespace App\Http\Services;

interface IMusicPlayerService
{
    function play();
    function stop();
    function pause();
    function next();
    function previous();
    function add();
}