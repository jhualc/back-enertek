<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Support\Facades\DB;

class SponsorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllSponsor(){
       $sponsor = DB::table('sponsor')->get();
       return response()->json([
        'message' => 'Respuesta Ok',
        'sponsor' => $sponsor
        ], 201);
    }
}
