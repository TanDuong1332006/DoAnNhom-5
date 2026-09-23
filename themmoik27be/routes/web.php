<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['status' => 1, 'message' => 'ShopVN API Server'];
});



