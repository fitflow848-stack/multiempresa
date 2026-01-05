@extends('layout.app')

@section('content')
    <style>
        .cliente-detail-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .cliente-detail-header {
            background: #17a2b8;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-section {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .detail-value {
            font-size: 15px;
            color: #333;
            padding: 8px 0;
        }

        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-primary:hover {
            background: #0056b3;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: black;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-warning:hover {
            background: #e0a800;
            color: black;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .stats-section {
            background: #f8f9fa;
            padding: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .empty-value {
            color: #6c757d;
            font-style: italic;
        }
    </style>

    <div class="cliente-detail-card">
        <!-- Header -->
        <div class="cliente-detail-header">
            <div>
                <h3 style="margin: 0; font-size: 18px;">👁️ Detalles del Cliente</h3>
                <small style="opacity: 0.9;">Información completa del cliente</small>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn-warning">✏️ Editar</a>
                <a href="{{ route('clientes.index') }}" class="btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Información básica -->
        <div class="detail-section">
            <h4 style="margin: 0 0 20px 0; color: #495057; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                📋 Información Básica
                <span class="badge {{ $cliente->estado ? 'badge-success' : 'badge-danger' }}">
                    {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                </span>
            </h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Tipo de Cliente</span>
                    <div class="detail-value">
                        {{ $cliente->tipo_cliente === 'Particular' ? '👤 Particular' : '🏢 Empresa' }}
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tipo de Documento</span>
                    <div class="detail-value">{{ $cliente->tipo_documento }}</div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Número de Documento</span>
                    <div class="detail-value" style="font-family: monospace; font-size: 16px; font-weight: 600;">
                        {{ $cliente->numero_documento }}
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nombre</span>
                    <div class="detail-value" style="font-weight: 600; font-size: 16px;">
                        {{ $cliente->nombre }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de contacto -->
        <div class="detail-section">
            <h4 style="margin: 0 0 20px 0; color: #495057; font-size: 16px;">📞 Información de Contacto</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Teléfono</span>
                    <div class="detail-value">
                        {{ $cliente->telefono ?: 'No registrado' }}
                        @if($cliente->telefono)
                            <a href="tel:{{ $cliente->telefono }}" style="margin-left: 10px; color: #007bff; text-decoration: none;">📱</a>
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <div class="detail-value">
                        {{ $cliente->email ?: 'No registrado' }}
                        @if($cliente->email)
                            <a href="mailto:{{ $cliente->email }}" style="margin-left: 10px; color: #007bff; text-decoration: none;">✉️</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de ubicación -->
        <div class="detail-section">
            <h4 style="margin: 0 0 20px 0; color: #495057; font-size: 16px;">📍 Ubicación</h4>
            <div class="detail-grid">
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Dirección</span>
                    <div class="detail-value">
                        {{ $cliente->direccion ?: 'No registrada' }}
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Distrito</span>
                    <div class="detail-value">{{ $cliente->distrito ?: 'No registrado' }}</div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Provincia</span>
                    <div class="detail-value">{{ $cliente->provincia ?: 'No registrada' }}</div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Departamento</span>
                    <div class="detail-value">{{ $cliente->departamento ?: 'No registrado' }}</div>
                </div>
            </div>
        </div>

        <!-- Información comercial -->
        <div class="detail-section">
            <h4 style="margin: 0 0 20px 0; color: #495057; font-size: 16px;">💰 Información Comercial</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Límite de Crédito</span>
                    <div class="detail-value" style="font-weight: 600; color: #28a745;">
                        S/ {{ number_format($cliente->credito_limite, 2) }}
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Debe Actual</span>
                    <div class="detail-value" style="font-weight: 600; color: {{ $cliente->debe > 0 ? '#dc3545' : '#28a745' }};">
                        S/ {{ number_format($cliente->debe, 2) }}
                    </div>
                </div>
            </div>
            @if($cliente->observaciones)
                <div class="detail-item" style="margin-top: 15px;">
                    <span class="detail-label">Observaciones</span>
                    <div class="detail-value" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border-left: 4px solid #007bff;">
                        {{ $cliente->observaciones }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Estadísticas -->
        <div class="stats-section">
            <h4 style="margin: 0 0 20px 0; color: #495057; font-size: 16px; text-align: center;">📊 Estadísticas</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="stat-item">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Ventas Totales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">S/ 0.00</div>
                    <div class="stat-label">Monto Comprado</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $cliente->created_at->format('d/m/Y') }}</div>
                    <div class="stat-label">Cliente Desde</div>
                </div>
            </div>
        </div>

        <!-- Footer con acciones -->
        <div style="padding: 20px; background: #f8f9fa; text-align: center; border-top: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: center; gap: 15px;">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn-warning">✏️ Editar Cliente</a>
                <a href="{{ route('clientes.index') }}" class="btn-primary">📋 Ver Todos los Clientes</a>
                <button onclick="confirmarDesactivacion()" class="btn-secondary" style="background: #dc3545;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                    🗑️ Desactivar Cliente
                </button>
            </div>
        </div>
    </div>

    <script>
        function confirmarDesactivacion() {
            if (confirm('¿Está seguro que desea desactivar este cliente?\n\nEsta acción puede ser reversible desde el panel de administración.')) {
                // Aquí podrías implementar la lógica de desactivación
                fetch(`{{ route('clientes.destroy', $cliente) }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente desactivado exitosamente');
                        window.location.href = '{{ route('clientes.index') }}';
                    } else {
                        alert('Error al desactivar el cliente');
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                    console.error('Error:', error);
                });
            }
        }
    </script>
@endsection