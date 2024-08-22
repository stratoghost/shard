<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Volt::route('home', 'pages.home')
    ->middleware(['auth', 'verified'])
    ->name('home');

Volt::route('people', 'pages.people')
    ->middleware(['auth', 'verified'])
    ->name('people');

Volt::route('collections', 'pages.collections')
    ->middleware(['auth', 'verified'])
    ->name('collections');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
