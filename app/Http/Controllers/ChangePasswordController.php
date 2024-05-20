<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    public function showChangeForm()
    {
        return redirect()->route('password.change.form');
    }
}
