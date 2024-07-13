<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserVisit;

class UserVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getMembers()
    {
        return User::select('name', 'created_at')->get();
    }

    public function getMemberVisits()
    {
        return UserVisit::with('user')->orderBy('created_at', 'DESC')->get();
    }
}
