@extends('layout.app')

@section('content')
    <style>
        .cliente-form-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .cliente-form-header {
            background: #ffc107;
            color: black;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-full {
            grid-column: 1 / -1;
        }

        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-warning {
            background: #ffc107;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
    </style>

    <div class="cliente-form-card">
        <!-- Header -->
        <div class="cliente-form-header">
            <div>
                <h3 style="margin: 0; font-size: 18px;">✏️ Editar Cliente</h3>
                <small style="opacity: 0.8;">Modificar datos del cliente</small>
            </div>
            <a href="{{ route('clientes.index') }}" class="btn-secondary" style="padding: 8px 15px; font-size: 12px;">
                ← Volver al listado
            </a>
        </div>

        <!-- Formulario -->
        <div style="padding: 25px;">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>¡Error!</strong> Por favor corrige los siguientes errores:
                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Información actual -->
            <div class="info-section">
                <h4 style="margin: 0 0 15px 0; color: #6c757d; font-size: 16px;">📋 Información Actual</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <strong>Tipo:</strong> {{ $cliente->tipo_cliente }}
                    </div>
                    <div>
                        <strong>Documento:</strong> {{ $cliente->tipo_documento }} - {{ $cliente->numero_documento }}
                    </div>
                    <div>
                        <strong>Estado:</strong> 
                        <span style="color: {{ $cliente->estado ? '#28a745' : '#dc3545' }}; font-weight: 600;">
                            {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-grid">
                    <div>
                        <label class="form-label">Tipo Cliente:</label>
                        <select name="tipo_cliente" class="form-control" required>
                            <option value="Particular" {{ old('tipo_cliente', $cliente->tipo_cliente) == 'Particular' ? 'selected' : '' }}>Particular</option>
                            <option value="Empresa" {{ old('tipo_cliente', $cliente->tipo_cliente) == 'Empresa' ? 'selected' : '' }}>Empresa</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tipo Documento:</label>
                        <select name="tipo_documento" class="form-control" required>
                            <option value="DNI" {{ old('tipo_documento', $cliente->tipo_documento) == 'DNI' ? 'selected' : '' }}>DNI</option>
                            <option value="RUC" {{ old('tipo_documento', $cliente->tipo_documento) == 'RUC' ? 'selected' : '' }}>RUC</option>
                            <option value="CE" {{ old('tipo_documento', $cliente->tipo_documento) == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                            <option value="Pasaporte" {{ old('tipo_documento', $cliente->tipo_documento) == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Nro. Documento:</label>
                        <input type="text" name="numero_documento" value="{{ old('numero_documento', $cliente->numero_documento) }}" class="form-control" required>
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Nombre:</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" class="form-control" required>
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Dirección:</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Distrito:</label>
                        <input type="text" name="distrito" value="{{ old('distrito', $cliente->distrito) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Provincia:</label>
                        <input type="text" name="provincia" value="{{ old('provincia', $cliente->provincia) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Departamento:</label>
                        <input type="text" name="departamento" value="{{ old('departamento', $cliente->departamento) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Teléfono:</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Límite de Crédito:</label>
                        <input type="number" name="credito_limite" value="{{ old('credito_limite', $cliente->credito_limite) }}" step="0.01" min="0" class="form-control">
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Observaciones:</label>
                        <textarea name="observaciones" rows="3" class="form-control">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <button type="submit" class="btn-warning">✏️ Actualizar Cliente</button>
                    <a href="{{ route('clientes.show', $cliente) }}" class="btn-primary">👁️ Ver Cliente</a>
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-focus en el campo nombre al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="nombre"]').focus();
        });
    </script>
@endsection