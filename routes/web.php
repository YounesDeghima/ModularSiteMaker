<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/memory_card_game', function () {
    return view('memory_card_game');
})->name('memory_card_game');

Route::get('/snake_game', function () {
    return view('snake');
})->name('snake_game');
