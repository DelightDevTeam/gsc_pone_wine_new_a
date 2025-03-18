<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $transferLogs = Auth::user()->transactions()->with('targetUser')
            ->latest()->paginate();

        return view('admin.transaction.index', compact('transferLogs'));
    }
}
