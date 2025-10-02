<?php

namespace App\Http\Controllers; // Pastikan namespace ini benar

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Ini adalah import penting

class Controller extends BaseController // Ini adalah ekstensi penting
{
    use AuthorizesRequests, ValidatesRequests;
}
