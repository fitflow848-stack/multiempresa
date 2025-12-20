<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    /** * Store a newly created proveedor in storage. * Devuelve JSON cuando la petición es AJAX. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ruc' => [
                'nullable',
                'string',
                'max:11',
                Rule::unique('proveedores', 'ruc')->ignore($request->input('id'))
            ],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'nombre_legal' => ['nullable', 'string', 'max:255'],
            'direccion' =>
            ['nullable', 'string', 'max:1000'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'ubigeo' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
        ]);
        // Si tienes empresa/usuario, añade id_empresa u otros campos según necesites // 
        $data['id_empresa'] = Auth::user()->company_id;
        $proveedor = Proveedor::create($data);
        // Siempre devolver JSON si es petición AJAX 
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($proveedor, 201);
        }
        return redirect()->back()->with('success', 'Proveedor creado correctamente');
    }
    
    /** * Endpoint para Select2: devuelve lista de proveedores que coincidan con q */
    public function select(Request $request)
    {
        $q = $request->get('q', '');
        $query = Proveedor::query();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_comercial', 'like', "%{$q}%")->orWhere('nombre_legal', 'like', "%{$q}%")->orWhere('ruc', 'like', "%{$q}%");
            });
        }
        $items = $query->orderBy('nombre_comercial')->limit(25)->get(['id', 'ruc', 'nombre_comercial', 'nombre_legal']);
        return response()->json($items);
    }
}
