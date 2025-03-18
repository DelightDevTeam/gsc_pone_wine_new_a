<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function count()
    {
        $count = Auth::user()->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }
}