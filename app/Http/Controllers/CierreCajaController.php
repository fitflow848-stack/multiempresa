<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use Illuminate\Http\Request;

class CierreCajaController extends Controller
{
    public function index()
    {
        $cierres = CierreCaja::latest()->paginate(20);
        return view('cierres.index', compact('cierres'));
    }

    public function create()
    {
        return view('cierres.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_cierre' => 'nullable|date',
            'monto_apertura' => 'required|numeric',
            'monto_cierre' => 'required|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        CierreCaja::create($data);

        return redirect()->route('cierre-caja.index')->with('success', 'Cierre de caja registrado.');
    }

    public function show(CierreCaja $cierre)
    {
        return view('cierres.show', ['cierre' => $cierre]);
    }
}
