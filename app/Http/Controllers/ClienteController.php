<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiDocumentosController;
use App\Models\Cliente;
use App\Services\PeruConsultasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    protected $peruConsultas;

    // Inyectamos el servicio en el constructor
    public function __construct(PeruConsultasService $peruConsultas)
    {
        $this->peruConsultas = $peruConsultas;
    }

    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        return view('clientes.index', compact('user', 'company'));
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $query = Cliente::where('company_id', $user->company_id)->activos();

        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->buscar($searchValue);
        }

        // Ordenamiento
        if ($request->has('order')) {
            $orderColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderDirection = $request->order[0]['dir'];
            $query->orderBy($orderColumn, $orderDirection);
        } else {
            $query->orderBy('nombre');
        }

        $totalData = $query->count();

        // Paginación
        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->start)->take($request->length);
        }

        $clientes = $query->withSum(['deudas' => function($query) {
            $query->whereIn('estado', ['pendiente', 'parcial']);
        }], 'monto_deuda')->get()->map(function ($cliente) {
            $montoDeuda = $cliente->deudas_sum_monto_deuda ?: 0;
            return [
                'id' => $cliente->id,
                'tipo_documento' => $cliente->tipo_documento,
                'numero_documento' => $cliente->numero_documento,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono ?? '',
                'email' => $cliente->email ?? '',
                'debe' => number_format($montoDeuda, 2),
                'estado' => $cliente->estado ? 'Activo' : 'Inactivo',
                'acciones' => view('clientes.partials.acciones', compact('cliente'))->render()
            ];
        });

        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => $clientes
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $company = $user->company;

        return view('clientes.create', compact('user', 'company'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validación (Si falla en AJAX, Laravel devuelve automáticamente JSON 422)
        $validator = Validator::make($request->all(), [
            'tipo_cliente' => 'required|in:Particular,Empresa',
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'numero_documento' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Verificar existencia (Evitar back() en AJAX)
        $existeCliente = Cliente::where('company_id', $user->company_id)
            ->where('numero_documento', $request->numero_documento)
            ->where('tipo_documento', $request->tipo_documento)
            ->first();

        if ($existeCliente) {
            $msg = 'Ya existe un cliente con este número de documento.';
            return $request->ajax() || $request->pos
                ? response()->json(['success' => false, 'message' => $msg], 400)
                : back()->withErrors(['numero_documento' => $msg])->withInput();
        }

        // 3. Creación del Cliente
        $cliente = Cliente::create([
            'company_id' => $user->company_id,
            'tipo_cliente' => $request->tipo_cliente,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'nombre' => strtoupper($request->nombre),
            'direccion' => $request->direccion,
            'distrito' => $request->distrito,
            'provincia' => $request->provincia,
            'departamento' => $request->departamento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'credito_limite' => $request->credito_limite ?? 0,
            'observaciones' => $request->observaciones
        ]);

        // 4. Respuesta condicionada

        // Caso POS (Punto de Venta)
        if ($request->pos || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado con éxito',
                'data' => [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'tipo_documento' => $cliente->tipo_documento,
                    'numero_documento' => $cliente->numero_documento,
                    'direccion' => $cliente->direccion,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'debe' => $cliente->debe ?? 0.00
                ],
                // Si viene de cotización, incluimos la ruta de retorno
                'redirect' => ($request->has('from_cotizacion')) ? route('cotizaciones.create') : null
            ]);
        }

        // Caso tradicional (Redirección de Blade)
        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(Cliente $cliente)
    {
        // $this->authorize('view', $cliente);
        $user = Auth::user();
        $company = $user->company;

        return view('clientes.show', compact('cliente', 'user', 'company'));
    }

    public function edit(Cliente $cliente)
    {
        // $this->authorize('update', $cliente);
        $user = Auth::user();
        $company = $user->company;

        return view('clientes.edit', compact('cliente', 'user', 'company'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        // $this->authorize('update', $cliente);

        $request->validate([
            'tipo_cliente' => 'required|in:Particular,Empresa',
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'numero_documento' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'distrito' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'credito_limite' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string'
        ]);

        // Verificar si ya existe otro cliente con ese documento
        $existeCliente = Cliente::where('company_id', Auth::user()->company_id)
            ->where('numero_documento', $request->numero_documento)
            ->where('tipo_documento', $request->tipo_documento)
            ->where('id', '!=', $cliente->id)
            ->first();

        if ($existeCliente) {
            return back()->withErrors(['numero_documento' => 'Ya existe otro cliente con este número de documento.']);
        }

        $cliente->update([
            'tipo_cliente' => $request->tipo_cliente,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'nombre' => strtoupper($request->nombre),
            'direccion' => $request->direccion,
            'distrito' => $request->distrito,
            'provincia' => $request->provincia,
            'departamento' => $request->departamento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'credito_limite' => $request->credito_limite ?? 0,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        // $this->authorize('delete', $cliente);

        // En lugar de eliminar, desactivar
        $cliente->update(['estado' => false]);

        return response()->json(['success' => true, 'message' => 'Cliente desactivado exitosamente.']);
    }

    public function crearDesdeReniec(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'documento' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\d{8}$/', $value) && !preg_match('/^\d{11}$/', $value)) {
                        $fail('El documento debe ser un DNI de 8 dígitos o un RUC de 11 dígitos.');
                    }
                }
            ]
        ]);

        $documento = $request->documento;
        $tipoDocumento = strlen($documento) === 8 ? 'DNI' : 'RUC';

        // Verificar si ya existe el cliente
        $existeCliente = Cliente::where('company_id', $user->company_id)
            ->where('numero_documento', $documento)
            ->where('tipo_documento', $tipoDocumento)
            ->first();

        if ($existeCliente) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existeCliente->id,
                    'tipo_documento' => $existeCliente->tipo_documento,
                    'numero_documento' => $existeCliente->numero_documento,
                    'nombre' => $existeCliente->nombre,
                    'direccion' => $existeCliente->direccion,
                    'email' => $existeCliente->email,
                    'telefono' => $existeCliente->telefono
                ],
                'message' => 'El cliente ya existe en el sistema'
            ]);
        }

        try {
            if ($tipoDocumento === 'DNI') {
                $data = $this->peruConsultas->consultarDni($documento);
            } else {
                $data = $this->peruConsultas->consultarRuc($documento);
            }


            if (isset($data['error'])) {
                return response()->json(['error' => $data['error']], 400);
            }

            // Extraer datos según el tipo de documento
            if ($tipoDocumento === 'DNI') {
                $nombre = trim(($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? '') . ' ' . ($data['nombres'] ?? ''));
                $direccion = $data['direccion'] ?? '';
                $tipoCliente = 'Particular';
            } else {
                $nombre = $data['razonSocial'] ?? '';
                $direccion = $data['direccion'] ?? '';
                $tipoCliente = 'Empresa';
            }

            if (empty($nombre)) {
                return response()->json(['error' => 'No se pudieron obtener los datos del documento'], 400);
            }

            // Crear el cliente
            $cliente = Cliente::create([
                'company_id' => $user->company_id,
                'tipo_cliente' => $tipoCliente,
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $documento,
                'nombre' => strtoupper($nombre),
                'direccion' => $direccion,
                'distrito' => '',
                'provincia' => '',
                'departamento' => '',
                'telefono' => '',
                'email' => '',
                'credito_limite' => 0,
                'estado' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $cliente->id,
                    'tipo_documento' => $cliente->tipo_documento,
                    'numero_documento' => $cliente->numero_documento,
                    'nombre' => $cliente->nombre,
                    'direccion' => $cliente->direccion,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar o crear el cliente: ' . $e->getMessage()], 500);
        }
    }

    public function buscarParaPos(Request $request)
    {
        $user = Auth::user();
        $termino = $request->get('q', '');

        $clientes = Cliente::where('company_id', $user->company_id)
            ->activos()
            ->when($termino, function ($query, $termino) {
                $query->buscar($termino);
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'tipo_documento' => $cliente->tipo_documento,
                    'numero_documento' => $cliente->numero_documento,
                    'nombre' => $cliente->nombre,
                    'direccion' => $cliente->direccion,
                    'telefono' => $cliente->telefono,
                    'email' => $cliente->email,
                    'debe' => $cliente->debe
                ];
            });

        return response()->json($clientes);
    }

    /**
     * API: Buscar clientes para cotizaciones
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        $clientes = Cliente::where('company_id', $user->company_id)
            ->where('activo', 1)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                    ->orWhere('numero_documento', 'like', "%{$query}%")
                    ->orWhere('telefono', 'like', "%{$query}%");
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'tipo_doc' => $cliente->tipo_documento,
                    'documento' => $cliente->numero_documento,
                    'direccion' => $cliente->direccion,
                    'telefono' => $cliente->telefono,
                    'email' => $cliente->email
                ];
            });

        return response()->json($clientes);
    }

    public function consultarReniec(Request $request)
    {
        $dni = $request->get('dni') ?? $request->input('documento');

        // Validación básica de entrada
        if (!$dni || strlen($dni) !== 8) {
            return response()->json(['error' => 'El DNI debe tener 8 dígitos'], 400);
        }

        try {
            // Llamamos directamente al método del Service
            $data = $this->peruConsultas->consultarDni($dni);

            // Verificamos si el servicio retornó un error
            if (isset($data['error'])) {
                return response()->json(['error' => $data['error']], 400);
            }

            // Mapeamos la respuesta para que tu frontend reciba siempre el mismo formato
            return response()->json([
                'success' => true,
                'data' => [
                    'dni' => $data['dni'] ?? $dni,
                    'nombres' => $data['nombres'] ?? '',
                    'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                    'apellido_materno' => $data['apellidoMaterno'] ?? '',
                    'nombre_completo' => trim(
                        ($data['apellidoPaterno'] ?? '') . ' ' .
                            ($data['apellidoMaterno'] ?? '') . ' ' .
                            ($data['nombres'] ?? '')
                    )
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al consultar RENIEC: ' . $e->getMessage()
            ], 500);
        }
    }
}
