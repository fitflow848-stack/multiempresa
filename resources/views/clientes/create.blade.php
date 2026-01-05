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
            background: #28a745;
            color: white;
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

        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-success:hover {
            background: #218838;
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

        .dni-search-section {
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
                <h3 style="margin: 0; font-size: 18px;">👤 Nuevo Cliente</h3>
                <small style="opacity: 0.9;">Datos administrativos del cliente</small>
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

            <!-- Sección de búsqueda por DNI -->
            <div class="dni-search-section">
                <h4 style="margin: 0 0 15px 0; color: #007bff; font-size: 16px;">🔍 Búsqueda por DNI (RENIEC)</h4>
                <div style="display: flex; gap: 10px; align-items: end;">
                    <div style="flex: 1;">
                        <label class="form-label">Número de DNI:</label>
                        <input type="text" id="dni-search" placeholder="Ingrese 8 dígitos" maxlength="8" class="form-control">
                    </div>
                    <button type="button" onclick="buscarEnReniec()" class="btn-success" style="height: 42px;">
                        🔍 Buscar en RENIEC
                    </button>
                </div>
                <div id="reniec-result" style="margin-top: 15px; display: none;"></div>
            </div>

            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                
                <div class="form-grid">
                    <div>
                        <label class="form-label">Tipo Cliente:</label>
                        <select name="tipo_cliente" class="form-control" required>
                            <option value="Particular" {{ old('tipo_cliente') == 'Particular' ? 'selected' : '' }}>Particular</option>
                            <option value="Empresa" {{ old('tipo_cliente') == 'Empresa' ? 'selected' : '' }}>Empresa</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tipo Documento:</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                            <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                            <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                            <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                            <option value="Pasaporte" {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Nro. Documento:</label>
                        <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', request('dni')) }}" class="form-control" required>
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-control" required>
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Dirección:</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Distrito:</label>
                        <input type="text" name="distrito" value="{{ old('distrito') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Provincia:</label>
                        <input type="text" name="provincia" value="{{ old('provincia') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Departamento:</label>
                        <input type="text" name="departamento" value="{{ old('departamento') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Teléfono:</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label">Límite de Crédito:</label>
                        <input type="number" name="credito_limite" value="{{ old('credito_limite', 0) }}" step="0.01" min="0" class="form-control">
                    </div>

                    <div class="form-grid-full">
                        <label class="form-label">Observaciones:</label>
                        <textarea name="observaciones" rows="3" class="form-control">{{ old('observaciones') }}</textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <button type="submit" class="btn-primary">💾 Registrar Cliente</button>
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Si viene con DNI prellenado, enfocar el nombre
        document.addEventListener('DOMContentLoaded', function() {
            const dni = '{{ request("dni") }}';
            if (dni) {
                document.getElementById('dni-search').value = dni;
                document.getElementById('numero_documento').value = dni;
                buscarEnReniec();
            }
        });

        function buscarEnReniec() {
            const dni = document.getElementById('dni-search').value.trim();
            
            if (dni.length !== 8) {
                alert('El DNI debe tener 8 dígitos');
                return;
            }
            
            if (!/^\d{8}$/.test(dni)) {
                alert('El DNI debe contener solo números');
                return;
            }

            const resultDiv = document.getElementById('reniec-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div style="color: #007bff; font-weight: 600;">🔍 Consultando RENIEC...</div>';

            fetch(`{{ route('clientes.buscar-dni') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ documento: dni })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Llenar los campos del formulario
                    document.getElementById('tipo_documento').value = 'DNI';
                    document.getElementById('numero_documento').value = data.data.numero_documento;
                    document.getElementById('nombre').value = data.data.nombre_completo;
                    
                    resultDiv.innerHTML = `
                        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; border: 1px solid #c3e6cb;">
                            <strong>✅ Datos encontrados en RENIEC:</strong><br>
                            <strong>DNI:</strong> ${data.data.numero_documento}<br>
                            <strong>Nombre:</strong> ${data.data.nombre_completo}
                        </div>
                    `;
                    
                    // Enfocar el campo de dirección
                    document.querySelector('input[name="direccion"]').focus();
                    
                } else {
                    resultDiv.innerHTML = `
                        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;">
                            <strong>❌ Error:</strong> ${data.error}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;">
                        <strong>❌ Error de conexión:</strong> No se pudo consultar RENIEC
                    </div>
                `;
                console.error('Error:', error);
            });
        }

        // Permitir búsqueda con Enter
        document.getElementById('dni-search').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                buscarEnReniec();
            }
        });
    </script>
@endsection