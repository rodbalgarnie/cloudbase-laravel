<?php

Route::get('/', 'HomeController@home');
Route::get("{any}", "HomeController@home")->where("any", ".*");