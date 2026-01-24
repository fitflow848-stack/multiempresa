<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\DocumentosEmpresa;
use App\Models\GuiaDestinatario;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionProducto;
use Illuminate\Http\Request;

class GuiaRemisionTransporteController extends Controller
{
    public function index()
    {
        return view('guia-transporte.index');
    }

    public function getAll()
    {
        $ingreso = GuiaRemision::where('id_area', session('area'))->get();
        return response()->json($ingreso);
    }

    public function add()
    {
        $documento = DocumentosEmpresa::where(['id_empresa' => 14, 'id_tido' => 11])->first();
        $serie =  $documento->serie;
        $numero =  $documento->numero;
        $departamentos = Departamento::all();
        return view('guia-transporte.add', compact('serie', 'numero', 'departamentos'));
    }

    public function store(Request $request)
    {
        // Crear la guía de remisión
        $guia = GuiaRemision::create($request->except(['detalle', 'destinatarios']));
        $guia->id_area = session('area');
        $guia->save();
        // Guardar los destinatarios en la tabla guia_destinatario
        if ($request->has('destinatarios')) {
            foreach (json_decode($request->destinatarios) as $destinatario) {
                // Guardar cada destinatario en la tabla guia_destinatario
                GuiaDestinatario::create([
                    'id_guia' => $guia->id,
                    'documento' => $destinatario->documento,
                    'datos' => $destinatario->datos,
                ]);
            }
        }

        // Guardar los productos si existen
        if ($request->has('detalle')) {
            foreach ($request->detalle as $detalle) {
                $detalle = json_decode($detalle);
                GuiaRemisionProducto::create([
                    'id_guia' => $guia->id,
                    'cod_sap' => $detalle->cod_sap,
                    'tipo' => $detalle->tipo,
                    'descripcion' => $detalle->descripcion,
                    'serie' => $detalle->serie,
                    'cantidad' => $detalle->cantidad,
                    'unidad_medida' => $detalle->unidad_medida,
                    'peso' => $detalle->peso,
                ]);
            }
        }

        // Retornar la respuesta con el ID de la nueva guía
        return response()->json($guia);
    }
}
