<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\UserVisit;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/members', function (Request $request) {
    return User::select('name', 'created_at')->get();
});

Route::get('/member_visits', function (Request $request) {
    return UserVisit::with('user')->orderBy('created_at', 'DESC')->get();
});