<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\DocumentosEmpresa;
use App\Models\GuiaDestinatario;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionProducto;
use App\Services\Sunat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuiaRemisionTransporteController extends Controller
{
    protected $sunatService;

    public function __construct(Sunat $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function index()
    {
        return view('guia-transporte.index');
    }

    public function getAll()
    {
        $ingreso = GuiaRemision::where('id_area', Auth::user()->company_id)->get();
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
        try {
            DB::beginTransaction();

            // 1. Crear la guía de remisión localmente
            $guia = GuiaRemision::create($request->except(['detalle', 'cliente_documento', 'cliente_nombre']));
            $guia->id_area = Auth::user()->company_id;
            $guia->save();

            // 2. Guardar el destinatario
            if ($request->has('cliente_documento')) {
                GuiaDestinatario::create([
                    'id_guia' => $guia->id,
                    'documento' => $request->cliente_documento,
                    'datos' => $request->cliente_nombre,
                ]);
            }

            $items = [];
            // 3. Guardar los productos
            if ($request->has('detalle')) {
                foreach ($request->detalle as $detalleJson) {
                    $detalle = json_decode($detalleJson);
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

                    $items[] = (object) [
                        'num_item' => count($items) + 1,
                        'cod_producto' => $detalle->cod_sap,
                        'unidad' => $detalle->unidad_medida,
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => $detalle->cantidad
                    ];
                }
            }

            // 4. Preparar datos para SUNAT API
            $user = \Illuminate\Support\Facades\Auth::user();
            $company = $user->company;

            $clienteObj = (object) [
                'num_doc' => $request->cliente_documento ?? '00000000',
                'rzn_social' => $request->cliente_nombre ?? 'Clientes Varios',
                'direccion' => $guia->direccion_llegada ?: '-'
            ];

            $transportistaObj = (object) [
                'num_doc' => $guia->transportista_doc ?? '',
                'rzn_social' => $guia->transportista_nombre ?? '',
                'nro_mtc' => $guia->transportista_mtc ?? ''
            ];

            // 5. Generar JSON y Llamar API
            $jsonPayload = $this->sunatService->formatJsonGuiaRemision(
                $guia,
                $company,
                $clienteObj,
                $transportistaObj,
                $items,
                $request->motivo_traslado_codigo ?? '01',
                $request->modalidad_traslado_codigo ?? '01'
            );

            $apiResponse = $this->sunatService->generarGuiaRemision($jsonPayload);
            $apiData = json_decode($apiResponse, true);

            // 6. Manejar respuesta de API
            if (isset($apiData['estado']) && $apiData['estado'] == true) {
                $nombreArchivo = $apiData['data']['nombre_archivo'] ?? null;
                $guia->nombre_archivo = $nombreArchivo;
                $guia->hash = $apiData['data']['hash'] ?? null;
                $guia->save();

                // Guardar XML en Storage
                if (isset($apiData['data']['contenido_xml']) && $nombreArchivo) {
                    Storage::disk('public')->put("xml/guias/{$nombreArchivo}.xml", $apiData['data']['contenido_xml']);
                }

                // 7. Incrementar número correlativo
                DocumentosEmpresa::where(['id_empresa' => 14, 'id_tido' => 11, 'serie' => $guia->serie])
                    ->increment('numero');
            } else {
                Log::warning('Respuesta negativa de API Guia:', ['response' => $apiData]);
            }

            DB::commit();
            return response()->json(array_merge($guia->toArray(), ['api_response' => $apiData]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store GuiaRemision:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function sendSunat($id)
    {
        try {
            $guia = GuiaRemision::findOrFail($id);
            $user = Auth::user();
            $company = $user->company;

            if (!$guia->nombre_archivo) {
                return response()->json(['error' => 'La guía no tiene un archivo generado.'], 400);
            }

            $xmlPath = "xml/guias/{$guia->nombre_archivo}.xml";
            if (!Storage::disk('public')->exists($xmlPath)) {
                return response()->json(['error' => 'El archivo XML no existe en el almacenamiento.'], 404);
            }

            $xmlContent = Storage::disk('public')->get($xmlPath);
            $jsonPayload = $this->sunatService->formatJsonEnviarGuia(
                $company->ruc ?? 10706671817,
                $guia->nombre_archivo,
                $xmlContent
            );
            $apiResponse = $this->sunatService->enviarGuiaRemision($jsonPayload);
            $apiData = json_decode($apiResponse, true);

            if (isset($apiData['estado']) && $apiData['estado'] == true) {
                $guia->ticker = $apiData['ticker'] ?? null;
                $guia->sunat_status = 'PROCESADO';
                $guia->save();
            }

            return response()->json($apiData);
        } catch (\Exception $e) {
            Log::error('Error en sendSunat GuiaRemision: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
