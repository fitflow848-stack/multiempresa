@extends('layout.app')

@section('title', 'Gestión de Precios')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Ventas /</span> Precios
        </h4>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Buscador de Precios</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar Producto</label>
                        <input type="text" id="search-input" class="form-control"
                            placeholder="Nombre, código o marca...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Familia</label>
                        <select id="familia-filter" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($familias as $familia)
                                <option value="{{ $familia->nombre }}">{{ $familia->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Marca</label>
                        <select id="marca-filter" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($marcas as $marca)
                                <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="btn-buscar">
                            <i class="bx bx-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="table-responsive text-nowrap" style="min-height: 300px;">
                <table class="table table-hover" id="prices-table">
                    <thead>
                        <tr>
                            <th width="30%">Producto</th>
                            <th>Costo</th>
                            <th>PVP</th>
                            <th>PVP Dto.</th>
                            <th>PVC</th>
                            <th>PVC Dto.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <!-- Results populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('search-input');
                const familiaFilter = document.getElementById(
                    'familia-filter'); // Note: Filtering by text on client side or need API support? API supports 'q'
                // The current API search focuses on name/presentation. 
                // Usually 'search' APIs are generic.
                // For now we will rely on keypress and button.

                const btnBuscar = document.getElementById('btn-buscar');
                const tableBody = document.querySelector('#prices-table tbody');

                function fetchProducts() {
                    const query = searchInput.value;
                    tableBody.innerHTML =
                        '<tr><td colspan="7" class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

                    // Using the existing search API
                    fetch(`{{ route('pos.buscar') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(response => {
                            const data = response.productos || response;
                            tableBody.innerHTML = '';
                            if (data.length === 0) {
                                tableBody.innerHTML =
                                    '<tr><td colspan="7" class="text-center">No se encontraron productos</td></tr>';
                                return;
                            }

                            data.forEach(item => {
                                const row = document.createElement('tr');

                                // Fields from ProductRepository joined with AlmacenIngresoDetalle
                                const id = item.id;
                                const costo = item.costo || 0;
                                const pvp = item.pvp || 0;
                                const pvpd = item.pvpd || 0;
                                const pvc = item.pvc || 0;
                                const pvcd = item.pvcd || 0;

                                row.innerHTML = `
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-wrap" style="max-width: 300px;">${item.nombre}</span>
                                <small class="text-muted">${item.detalle || ''}</small>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control price-input" 
                                    data-id="${id}" data-field="costo" 
                                    value="${costo}" step="0.01">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control price-input" 
                                    data-id="${id}" data-field="pvp" 
                                    value="${pvp}" step="0.01">
                            </div>
                        </td>
                        <td>
                             <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control price-input" 
                                    data-id="${id}" data-field="pvpd" 
                                    value="${pvpd}" step="0.01">
                            </div>
                        </td>
                        <td>
                             <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control price-input" 
                                    data-id="${id}" data-field="pvc" 
                                    value="${pvc}" step="0.01">
                            </div>
                        </td>
                        <td>
                             <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control price-input" 
                                    data-id="${id}" data-field="pvcd" 
                                    value="${pvcd}" step="0.01">
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-success status-badge d-none">Guardado</span>
                        </td>
                    `;
                                tableBody.appendChild(row);
                            });

                            attachListeners();
                        });
                }

                function attachListeners() {
                    document.querySelectorAll('.price-input').forEach(input => {
                        input.addEventListener('change', function() {
                            updatePrice(this);
                        });
                    });
                }

                function updatePrice(input) {
                    const lineaId = input.dataset.id;
                    const field = input.dataset.field;
                    const value = input.value;
                    const badge = input.closest('tr').querySelector('.status-badge');

                    // Show saving state (optional, maybe spinner)
                    input.classList.add('border-warning');

                    fetch('{{ route('pos.precios.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                id: lineaId,
                                field: field,
                                value: value
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            input.classList.remove('border-warning');
                            if (data.success) {
                                input.classList.add('border-success');
                                badge.classList.remove('d-none');
                                setTimeout(() => {
                                    input.classList.remove('border-success');
                                    badge.classList.add('d-none');
                                }, 2000);
                            } else {
                                input.classList.add('border-danger');
                                alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            input.classList.remove('border-warning');
                            input.classList.add('border-danger');
                        });
                }

                btnBuscar.addEventListener('click', fetchProducts);

                // Trigger search on enter
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        fetchProducts();
                    }
                });

                // Initial load? Maybe not to save resources, or maybe yes.
                // fetchProducts(); 
            });
        </script>
    @endpush
@endsection
