@extends('layout.app')

@section('content')
    <style>
        /* Estilos Generales */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #ebedf2 !important;
            padding: 10px 15px;
        }

        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 4px;
        }

        .form-control-sm,
        .form-select-sm {
            border-radius: 4px;
        }

        /* Buscador de productos flotante */
        #cod_sap_results {
            position: absolute;
            z-index: 99999;
            width: 100%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            max-height: 260px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            padding: 0;
            margin-top: 6px;
        }

        #cod_sap_results li.list-group-item {
            background: #fff !important;
            border: none !important;
            border-bottom: 1px solid #f1f1f1 !important;
            padding: 10px 12px !important;
            cursor: pointer;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #cod_sap_results li.list-group-item:hover,
        #cod_sap_results li.list-group-item:focus {
            background: #e9f6ff !important;
            color: #0b6bd6;
        }

        /* Tabla de productos */
        .table-custom thead {
            background-color: #1572e8;
            color: white;
        }

        .table-custom th {
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .destinatario-item {
            background: #fcfcfc;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-center justify-content-between pt-2 pb-4">
                <h3 class="fw-bold mb-0">GUÍA DE REMISIÓN</h3>
                <a href="{{ route('guia.index') }}" class="btn btn-secondary btn-round">
                    <i class="bi bi-arrow-left-circle me-1"></i> Regresar
                </a>
            </div>

            <form id="formGuia">
                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-calendar3 me-2"></i>Información del Traslado</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha de Traslado</label>
                                        <input type="date" class="form-control" id="fecha_traslado"
                                            name="fecha_traslado">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Peso Bruto Total (KG)</label>
                                        <input type="number" step="0.01" class="form-control" name="peso_bruto">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-geo-alt me-2"></i>Punto de Partida</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">RUC</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="ruc_partida" name="ruc_partida">
                                            <button type="button" class="btn btn-primary btn-search_partida"><i
                                                    class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Razón Social</label>
                                        <input type="text" class="form-control" id="razon_partida" name="razon_partida">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label">Dirección Completa</label>
                                        <input type="text" class="form-control" id="direccion_partida"
                                            name="direccion_partida">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Departamento</label>
                                        <select class="form-select form-control" name="departamento_partida"
                                            id="select_departamento">
                                            @foreach ($departamentos as $departamento)
                                                <option value="{{ $departamento->dep_cod }}">
                                                    {{ strtoupper($departamento->dep_nombre) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Provincia</label>
                                        <select class="form-select form-control" name="provincia_partida"
                                            id="select_provincia"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Distrito</label>
                                        <select class="form-select form-control" name="distrito_partida"
                                            id="select_distrito"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-geo-fill me-2"></i>Punto de Llegada</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label">Dirección de Llegada</label>
                                        <input type="text" class="form-control" id="direccion_llegada"
                                            name="direccion_llegada">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Dep.</label>
                                        <select class="form-select form-control" name="departamento_llegada"
                                            id="select_departamento_lle">
                                            @foreach ($departamentos as $departamento)
                                                <option value="{{ $departamento->dep_cod }}">
                                                    {{ strtoupper($departamento->dep_nombre) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Prov.</label>
                                        <select class="form-select form-control" name="provincia_llegada"
                                            id="select_provincia_lle"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Dist.</label>
                                        <select class="form-select form-control" name="distrito_llegada"
                                            id="select_distrito_lle"></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle me-2"></i>Otros Datos</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="form-label">Motivo de Traslado</label>
                                    <input type="text" class="form-control" name="motivo_traslado"
                                        placeholder="Ej: Venta, Compra, Traslado entre almacenes">
                                </div>
                                <div>
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control" name="observacion" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-people me-2"></i>Destinatarios</h5>
                        <button type="button" id="addDestinatario" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg"></i> Agregar Destinatario
                        </button>
                    </div>
                    <div class="card-body p-3" id="destinatarios">
                        <div class="destinatario-item">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">DNI/RUC</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="documento[]">
                                        <button type="button" class="btn btn-outline-primary btn-search"><i
                                                class="bx bx-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Apellidos y Nombres / Razón Social</label>
                                    <input type="text" class="form-control" name="datos[]" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-box-seam me-2"></i>Detalle de Productos</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row align-items-end mb-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label">Buscar Producto (Nombre o Código)</label>
                                <input type="text" id="cod_sap" class="form-control"
                                    placeholder="Escriba para buscar...">
                                <ul id="cod_sap_results" class="list-group"></ul>
                            </div>
                            <div class="col-md-2" id="quantity-container" style="display:none;">
                                <label class="form-label">Cantidad</label>
                                <input type="number" id="product-quantity" class="form-control" min="1"
                                    value="1">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="add-product-btn" class="btn btn-primary w-100"
                                    style="display:none;">
                                    <i class="bi bi-cart-plus me-1"></i> Añadir a la lista
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-custom" id="productTable">
                                <thead>
                                    <tr>
                                        <th>COD</th>
                                        <th>TIPO</th>
                                        <th>DESCRIPCIÓN</th>
                                        <th>SERIE</th>
                                        <th width="100">CANT.</th>
                                        <th>U.M.</th>
                                        <th width="100">PESO</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-check-circle me-1"></i> GENERAR GUÍA DE REMISIÓN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>


    <script>
        let token = '{{ csrf_token() }}';

        $('#select_departamento').change(function() {
            let dep = $(this).val();
            $.post("{{ route('provincia.get') }}", {
                    _token: token,
                    dep: dep
                },
                function(data, textStatus, jqXHR) {
                    let opt = '';

                    $.each(data, function(i, v) {
                        opt += `<option value="${v.pro_id}">${v.pro_nombre}</option>`;
                    });
                    $('#select_provincia').html(opt);
                },
            );
        });

        $('#select_provincia').change(function() {
            let prov = $(this).val();
            $.post("{{ route('distrito.get') }}", {
                    _token: token,
                    prov: prov
                },
                function(data, textStatus, jqXHR) {
                    let opt = '';

                    $.each(data, function(i, v) {
                        opt += `<option value="${v.dis_id}">${v.dis_nombre}</option>`;
                    });
                    $('#select_distrito').html(opt);
                },
            );
        });

        $('#select_departamento_lle').change(function() {
            let dep = $(this).val();
            $.post("{{ route('provincia.get') }}", {
                    _token: token,
                    dep: dep
                },
                function(data, textStatus, jqXHR) {
                    let opt = '';

                    $.each(data, function(i, v) {
                        opt += `<option value="${v.pro_id}">${v.pro_nombre}</option>`;
                    });
                    $('#select_provincia_lle').html(opt);
                },
            );
        });

        $('#select_provincia_lle').change(function() {
            let prov = $(this).val();
            $.post("{{ route('distrito.get') }}", {
                    _token: token,
                    prov: prov
                },
                function(data, textStatus, jqXHR) {
                    let opt = '';

                    $.each(data, function(i, v) {
                        opt += `<option value="${v.dis_id}">${v.dis_nombre}</option>`;
                    });
                    $('#select_distrito_lle').html(opt);
                },
            );
        });

        // Escuchar el click en los botones de búsqueda para cada destinatario
        $(document).on('click', '.btn-search', function() {
            // Soportar tanto el contenedor nuevo `.destinatario` como el markup inicial `.destinatario-item`
            const destinatarioContainer = $(this).closest('.destinatario, .destinatario-item');

            // Obtener el valor del input correspondiente al botón clickeado dentro del contenedor
            let documento = destinatarioContainer.find('input[name="documento[]"]').val();
            let tipo = 'dni';

            if (!documento) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Vacío',
                    text: 'Por favor, ingrese el RUC/DNI.'
                });
                return;
            }

            if (documento.length == 8) {
                tipo = 'dni';
            }
            if (documento.length > 8) {
                tipo = 'ruc';
            }

            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Buscando...',
                html: 'Por favor, espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Mostrar el indicador de carga
                }
            });

            // Definir la ruta para la búsqueda según el tipo
            let ruta = tipo == 'ruc' ? '{{ route('apidocumento.ruc') }}' : '{{ route('apidocumento.dni') }}';

            $.ajax({
                url: ruta,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento: documento
                },
                success: function(response) {
                    Swal.close(); // Cerrar el mensaje de carga cuando la solicitud sea exitosa
                    if (response) {
                        // Si se encontró la respuesta, completar el campo correspondiente
                        let datos = tipo == 'ruc' ? response.razonSocial :
                            `${response.nombre ? response.nombre : ''} ${response.nombres} ${response.apellidoPaterno} ${response.apellidoMaterno}`;
                        destinatarioContainer.find('input[name="datos[]"]').val(datos);
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Datos no encontrados',
                            text: 'No se encontraron datos para el DNI/RUC proporcionado.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close(); // Cerrar el mensaje de carga si hay un error
                    console.error('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al buscar el DNI/RUC.'
                    });
                }
            });
        });

        $(document).on('click', '.btn-search_partida', function() {
            const documento = $('#ruc_partida').val(); // Correcto
            const tipo = 'ruc'; // Asegúrate de que sea constante o variable

            if (!documento) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Vacío',
                    text: 'Por favor, ingrese el RUC/DNI.'
                });
                return;
            }

            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Buscando...',
                html: 'Por favor, espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Mostrar el indicador de carga
                }
            });

            // Definir la ruta para la búsqueda según el tipo
            let ruta = tipo === 'ruc' ? '{{ route('apidocumento.ruc') }}' : '{{ route('apidocumento.dni') }}';

            $.ajax({
                url: ruta,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento: documento
                },
                success: function(response) {
                    Swal.close(); // Cerrar el mensaje de carga cuando la solicitud sea exitosa
                    if (response) {
                        // Si se encontró la respuesta, completar los campos correspondientes
                        $('#razon_partida').val(response.razonSocial);
                        $('#direccion_partida').val(response.direccion);
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Datos no encontrados',
                            text: 'No se encontraron datos para el DNI/RUC proporcionado.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close(); // Cerrar el mensaje de carga si hay un error
                    console.error('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al buscar el DNI/RUC.'
                    });
                }
            });
        });

        let isScanning = false; // Variable para distinguir entre escaneo y envío manual

        $('#formGuia').on('keydown', function(e) {
            if (e.key === 'Enter' && !isScanning) {
                // Permitir Enter dentro de los textareas
                if ($(e.target).is("textarea")) {
                    return;
                }
                e.preventDefault(); // Evita el envío del formulario
            }
        });

        // Detectar el evento de escaneo (pistola)
        $('#formGuia').on('input', 'input[name="campoEscaneo"]', function(e) {
            const valor = $(this).val();

            if (valor) {
                isScanning = true;
                setTimeout(() => {
                    isScanning = false;
                }, 300); // Restablecer después de un breve momento
            }
        });

        $('#formGuia').on('submit', function(e) {
            if (isScanning) {
                e.preventDefault(); // No enviar si fue un escaneo
                return;
            }

            e.preventDefault();
            // Crear el FormData del formulario
            let formData = new FormData(this);
            formData.append('_token', token); // Agregar el token CSRF

            // Agregar los destinatarios al FormData
            let destinatarios = [];
            $('#destinatarios .destinatario').each(function() {
                const documento = $(this).find('input[name="documento[]"]').val();
                const datos = $(this).find('input[name="datos[]"]').val();
                if (documento && datos) {
                    destinatarios.push({
                        documento: documento,
                        datos: datos
                    });
                }
            });
            formData.append('destinatarios', JSON.stringify(destinatarios)); // Agregar destinatarios como JSON

            // Agregar los datos de la tabla al FormData
            $('#productTable tbody tr').each(function(index, row) {
                const rowData = {
                    cod_sap: $(row).find('td:eq(0)').text(),
                    tipo: $(row).find('td:eq(1)').text(),
                    descripcion: $(row).find('td:eq(2)').text(),
                    serie: $(row).find('td:eq(3)').text(),
                    cantidad: $(row).find('td:eq(4)').text(),
                    unidad_medida: $(row).find('td:eq(5) select').val(),
                    peso: $(row).find('td:eq(6)').text(),
                };

                formData.append(`detalle[${index}]`, JSON.stringify(rowData));
            });

            // Realizar la solicitud AJAX
            $.ajax({
                url: '{{ route('guia.save') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Los datos se guardaron correctamente.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.open(
                            `{{ env('APP_URL') }}/guia/remision/${response.id}`,
                            '_blank');
                        // Primero redirige al usuario a la vista
                        window.location.href = `{{ route('guia.index') }}`;
                    });
                },
                error: function(response) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al guardar los datos.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });


        $('#cod_sap').on('input', function() {
            var query = $(this).val();
            let tipo = $(this).data('tipo');

            if (query.length > 3) {
                $.ajax({
                    url: '{{ env('APP_URL') }}/pos/buscar-productos',
                    method: 'GET',
                    data: {
                        q: query,
                        tipo: tipo
                    },
                    success: function(response) {
                        $('#cod_sap_results').empty();
                        let foundMatch = false; // Variable para verificar si encontramos coincidencias

                        response.forEach(function(producto) {
                            // Mapear campos defensivamente según el JSON retornado
                            const codigo = producto.producto_id ?? producto.cod_sap ?? producto.codigo ?? '';
                            const descripcion = producto.nombre ?? producto.descripcion ?? producto.detalle ?? '';
                            const serie = producto.serie ?? '-';
                            const cantidad = producto.cantidad_total ?? producto.cantidad ?? 1;
                            const unidad = producto.unidad ?? producto.unidad_medida ?? 'UND';
                            const peso = producto.peso ?? producto.peso_kg ?? '-';
                            const tipo = producto.product_linea_id ?? producto.tipo ?? '';
                            let origen = '';
                            if (typeof producto.origen === 'string') origen = producto.origen;
                            if (Array.isArray(producto.origen) && producto.origen.length > 0) origen = producto.origen[0].nombre;

                            $('#cod_sap_results').append(
                                `<li class="list-group-item list-group-item-action" data-sap="${codigo}" data-descripcion="${descripcion.replace(/"/g,'&quot;')}" data-serie="${serie}" data-cantidad="${cantidad}" data-unidad="${unidad}" data-peso="${peso}" data-tipo="${tipo}">` +
                                `${codigo} ${descripcion} ${producto.detalle ? ('• ' + producto.detalle) : ''} ${producto.fecha_vencimiento ? ('• FV: ' + producto.fecha_vencimiento) : ''}` +
                                `</li>`
                            );
                            foundMatch = true; // Hay coincidencia
                        });

                        if (!foundMatch) {
                            // Si no hay coincidencias, muestra el botón para agregar el producto
                            $('#add-product-btn').show();
                            $('#quantity-container').show();
                        } else {
                            $('#add-product-btn').hide();
                            $('#quantity-container').hide();
                        }

                        $('#cod_sap_results').show();
                    }
                });
            } else {
                $('#cod_sap_results').empty().hide();
                $('#add-product-btn').hide();
                $('#quantity-container').hide();
            }
        });

        $('#add-product-btn').on('click', function() {
            let descripcion = $('#cod_sap').val();
            let cantidad = $('#product-quantity').val();
            let unidadOptions = `
            <select class="form-select">
                <option value="UND">UND</option>
                <option value="KG">KG</option>
                <option value="MTS">MTS</option>
            </select>`;

            if (descripcion && cantidad) {
                const newRow = `<tr>
                    <td></td>
                    <td></td>
                    <td>${descripcion}</td>
                    <td></td>
                    <td>${cantidad}</td>
                    <td>${unidadOptions}</td>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="bx bx-x-circle"></i></button>
                    </td>
                </tr>`;

                $('#productTable tbody').append(newRow);
                $('#quantity-container').hide(); // Ocultar el input de cantidad después de agregar el producto
                $('#cod_sap').val(''); // Limpiar el campo de búsqueda
                $('#product-quantity').val(''); // Limpiar el campo de búsqueda
            }

            // Ocultar el botón después de añadir
            $('#add-product-btn').hide();
        });

        // Selección de un producto de la lista de resultados
        $(document).on('click', '#cod_sap_results li', function() {
            var sap = $(this).data('sap');
            var tipo = $(this).data('tipo');
            var descripcion = $(this).data('descripcion');
            var serie = $(this).data('serie') == 'undefined' ? '-' : $(this).data('serie');
            serie = serie == null ? '-' : serie;
            var cantidad = $(this).data('cantidad') == 'undefined' ? 1 : $(this).data('cantidad');
            var unidad = $(this).data('unidad') == 'undefined' ? '-' : $(this).data('unidad');
            var peso = $(this).data('peso') == 'undefined' ? '-' : $(this).data('peso');
            let unidadOptions = `
            <select class="form-select">
                <option value="UND">UND</option>
                <option value="KG">KG</option>
                <option value="MTS">MTS</option>
            </select>`;

            // Añadir fila a la tabla con los valores seleccionados
            const newRow = `<tr>
                <td>${sap}</td>
                <td>${tipo}</td>
                <td>${descripcion}</td>
                <td>${serie}</td>
                <td>${cantidad}</td>
                <td>${unidadOptions}</td>
                <td>${peso}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-btn"><i class='bx  bx-trash-alt'></i> </button>
                </td></tr>`;

            $('#productTable tbody').append(newRow);
            $('#cod_sap_results').empty().hide();
            $('#add-product-btn').hide(); // Ocultar el botón de agregar producto
            $('#cod_sap').val(''); // Limpiar el campo de búsqueda
        });
        // Función para eliminar un producto
        $('#productTable').on('click', '.delete-btn', function() {
            $(this).closest('tr').remove();
        });


        // Función para agregar un nuevo destinatario
        $('#addDestinatario').on('click', function() {
            const nuevoDestinatario = `
                    <div class="destinatario">
                        <div class="d-flex">
                            <div class="form-group me-3">
                                <label for="documento" class="control-label">DNI/RUC</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="documento[]">
                                    <button type="button" class="btn btn-outline-primary btn-search">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="datos" class="control-label">APELLIDOS Y NOMBRE/RAZÓN SOCIAL</label>
                                <input type="text" class="form-control" name="datos[]" readonly>
                            </div>
                        </div>
                    </div>
                `;
            // Agregar el nuevo destinatario al contenedor con el id "destinatarios"
            $('#destinatarios').append(nuevoDestinatario);
        });
    </script>
@endsection
