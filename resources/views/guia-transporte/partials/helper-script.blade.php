@push('helper-script')
    <script>
        let token = "{{ csrf_token() }}";
        let rol = '{{ session('rol') }}';
        // Búsqueda y autocompletar en el campo cod_sap
        $('#cod_sap').on('input', function() {
            var query = $(this).val();
            let tipo = $(this).data('tipo');

            if (query.length > 3) {
                $.ajax({
                    url: '{{ env('APP_URL') }}/buscar-productos',
                    method: 'GET',
                    data: {
                        query: query,
                        tipo: tipo
                    },
                    success: function(response) {
                        $('#cod_sap_results').empty();

                        response.forEach(function(producto) {
                            if (tipo == 'ingreso') {
                                $('#cod_sap_results').append(
                                    '<li class="list-group-item list-group-item-action" data-sap="' +
                                    producto.sap + '" data-descripcion="' + producto
                                    .nombre + '">' +
                                    producto.sap + ' - ' + producto.nombre + '</li>'
                                );
                            } else {
                                // Convertir el objeto producto a una cadena JSON
                                let json = JSON.stringify(producto);

                                // Escapar las comillas dobles para que no causen conflicto en el HTML
                                json = json.replace(/"/g, '&quot;');

                                $('#cod_sap_results').append(
                                    '<li class="list-group-item list-group-item-action" data-json="' +
                                    json + '" data-sap="' + producto.cod_sap +
                                    '" data-descripcion="' +
                                    producto.descripcion + '">' + producto.cod_sap + ' - ' +
                                    producto.descripcion + '</li>'
                                );
                            }
                        });

                        $('#cod_sap_results').show();
                    }
                });
            } else {
                $('#cod_sap_results').empty().hide();
            }
        });

        // Selección de un producto
        $(document).on('click', '#cod_sap_results li', function() {
            let ingreso = $(this).data('json');
            var sap = $(this).data('sap');
            var descripcion = $(this).data('descripcion');

            if (ingreso) {
                $('#serie').val(ingreso.serie);
                $('#serie_18_digitos').val(ingreso.serie_18_digitos);
                $('#cantidad').val(ingreso.cantidad);
            }

            if (sap && sap.toString().startsWith("10")) {
                if ($('#serie')) {
                    $('#serie').attr('disabled', true);
                    $('#serie_18_digitos').attr('disabled', true);
                }
                $('#content_serie').hide();
            }

            $('#cod_sap').val(sap);
            $('#descripcion').val(descripcion);

            $('#cod_sap_results').empty().hide();
        });

        // Cerrar la lista si se hace clic fuera
        $(document).click(function(e) {
            if (!$(e.target).closest('#cod_sap, #cod_sap_results').length) {
                $('#cod_sap_results').hide();
            }
        });

        $('#serie').on('input', function() {
            var query = $(this).val();

            if (query.length > 3) {
                $.ajax({
                    url: '{{ env('APP_URL') }}/buscar-series',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        $('#cod_series_results').empty();

                        response.forEach(function(producto) {
                            $('#cod_series_results').append(
                                '<li class="list-group-item list-group-item-action" data-sap="' +
                                producto.cod_sap + '" data-descripcion="' + producto
                                .descripcion + '">' +
                                producto.serie + ' - ' + producto.descripcion + '</li>'
                            );
                        });

                        $('#cod_series_results').show();
                    }
                });
            } else {
                $('#cod_series_results').empty().hide();
            }
        });

        // Selección de un producto
        $(document).on('click', '#cod_series_results li', function() {
            var sap = $(this).data('sap');
            var descripcion = $(this).data('descripcion');

            $('#cod_sap').val(sap);
            $('#descripcion').val(descripcion);

            if ($('#serie_18_digitos')) {
                $('#serie_18_digitos').val(resumirSerie($('#serie').val()))
            }

            $('#cod_series_results').empty().hide();
        });

        // Cerrar la lista si se hace clic fuera
        $(document).click(function(e) {
            if (!$(e.target).closest('#serie, #cod_series_results').length) {
                $('#cod_series_results').hide();
            }
        });


        function resumirSerie(serieValue) {
            return serieValue.slice(-18);
        }
    </script>
@endpush
