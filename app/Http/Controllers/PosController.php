<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        
        return view('pos.index', compact('user', 'company'));
    }
    
    public function getProducts(Request $request)
    {
        // TODO: Implementar búsqueda de productos
        return response()->json([]);
    }
    
    public function createSale(Request $request)
    {
        // TODO: Implementar creación de venta
        return response()->json(['success' => true]);
    }
}