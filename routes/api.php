<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserVisitController;

Route::get('/members', [UserVisitController::class, 'getMembers']);

Route::get('/member_visits', [UserVisitController::class, 'getMemberVisits']);

Route::get('/unique_visits_by_hour', [UserVisitController::class, 'getUniqueVisitsByHour']);
