<?php

namespace App\Http\Controllers;

use App\Support\AppliedRows;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppliedController extends Controller
{
    public function index(Request $request): View
    {
        $rows = AppliedRows::forUser($request->user());

        return view('riwayat', [
            'rows' => $rows,
            'appliedCount' => $rows->count(),
        ]);
    }
}
