<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SiteController extends Controller
{
    public function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ok', 'message' => 'Conexión a la base de datos exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
