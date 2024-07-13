<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserVisitController;

Route::get('/members', [UserVisitController::class, 'getMembers']);

Route::get('/member_visits', [UserVisitController::class, 'getMemberVisits']);
