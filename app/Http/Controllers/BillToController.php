<?php

namespace App\Http\Controllers;

use App\Models\BillTo;
use Illuminate\Http\Request;

class BillToController extends Controller
{
    public function index()
    {
        return view('bill-to.index');
    }

    public function create()
    {
        return view('bill-to.create');
    }

    public function edit(BillTo $billTo)
    {
        return view('bill-to.edit', compact('billTo'));
    }
}
