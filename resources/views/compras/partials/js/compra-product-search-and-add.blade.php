<script>
    (function($) {
        'use strict';

        $(function() {
            // CSRF for ajax is already set in blade via $.ajaxSetup (if not, uncomment)
            // $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            const productosSearchUrl = $('#product-search-form').length ? $('#product-search-form').data(
                'url') || '{{ route('productos.search') }}' : '{{ route('productos.search') }}';
            const $formSearch = $('#product-search-form');
            const $results = $('#product-search-results');
            const $count = $('#product-search-count');

            // referencias tabla compra
            const $productosTbody = $('table.table tbody');
            const $totalBruto = $('input[name="total_bruto"]');
            const $totalDescuento = $('input[name="total_descuento"]');
            const $brutoNeto = $('input[name="bruto_neto"]');
            const $totalImpuesto = $('input[name="total_impuesto"]');
            const $totalNeto = $('input[name="total_neto"]');
            const $flete = $('input[name="flete"]');
            const $totalPagar = $('input[name="total_pagar"]');
            const TAX_RATE = 0.18;
            let lineIndex = $('table.table tbody tr').length;

            function escapeHtml(str) {
                if (str === undefined || str === null) return '';
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function renderResultItem(p) {
                const cb = p.cb || '';
                const ref = p.codigo_ref || '';
                const nombre = p.nombre || '';
                const familia = p.familia || '';

                const $item = $(`
            <div class="list-group-item d-flex align-items-center">
                <div class="me-3" style="width:36px; text-align:center;">
                    <i class="bi bi-tag-fill text-secondary"></i>
                </div>

                <div class="flex-grow-1">
                    <div><strong>${escapeHtml(cb)} ${escapeHtml(ref)}</strong> &nbsp; ${escapeHtml(nombre)}</div>
                    <div class="small text-muted">${escapeHtml(familia)}</div>
                </div>

                <div class="ms-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success btn-add-product">Agregar</button>

                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item btn-open-product" href="#">Abrir</a></li>
                        <li><a class="dropdown-item btn-edit-product" href="#">Editar</a></li>
                        <li><a class="dropdown-item btn-copy" href="#">Copiar</a></li>
                      </ul>
                    </div>
                </div>
            </div>
        `);

                $item.data('product', p);
                return $item;
            }

            function doSearch(params) {
                $results.html('<div class="list-group-item text-center py-4">Buscando...</div>');
                $.get(productosSearchUrl, params)
                    .done(function(data) {
                        const items = Array.isArray(data) ? data : (data.items || []);
                        $count.text(`Resultado: ${items.length} producto(s)`);

                        if (!items.length) {
                            $results.html(
                                '<div class="list-group-item text-warning">No se encontraron productos.</div>'
                            );
                            return;
                        }

                        $results.empty();
                        items.forEach(p => $results.append(renderResultItem(p)));
                    })
                    .fail(function() {
                        $results.html(
                            '<div class="list-group-item text-danger">Error al buscar productos.</div>'
                        );
                        $count.text('Resultado: 0 productos');
                    });
            }

            // form submit search
            $formSearch.on('submit', function(e) {
                e.preventDefault();
                const params = $(this).serializeArray().reduce((acc, {
                    name,
                    value
                }) => {
                    if (value !== '') acc[name] = value;
                    return acc;
                }, {});
                doSearch(params);
            });

            // quick search typing
            $('#search-q').on('input', debounce(function() {
                const q = $(this).val().trim();
                if (q.length >= 2) {
                    const params = $formSearch.serializeArray().reduce((acc, {
                        name,
                        value
                    }) => {
                        if (value !== '') acc[name] = value;
                        return acc;
                    }, {});
                    doSearch(params);
                }
            }, 350));

            // clear
            $('#btn-product-clear').on('click', function() {
                $formSearch[0].reset();
                $results.html(
                    '<div class="list-group-item text-muted small">Realiza una búsqueda para ver resultados.</div>'
                );
                $count.text('Resultado: 0 productos');
            });

            // add product button in results
            $results.on('click', '.btn-add-product', function() {
                const p = $(this).closest('.list-group-item').data('product');
                addProductToCompra(p);
            });

            function addProductToCompra(product) {
                const cantidad = 1;
                const costo = (product.precio_compra !== null && product.precio_compra !== undefined) ?
                    Number(product.precio_compra) :
                    (product.precio_linea && product.precio_linea.precio_compra ? Number(product
                        .precio_linea.precio_compra) : 0);

                const descuento = 0;
                const vcpc = '';

                const idx = lineIndex++;
                const row = $(`
            <tr data-idx="${idx}">
                <td class="px-2 align-middle">${idx+1}<input type="hidden" name="product_id[]" value="${escapeHtml(product.id)}"></td>
                <td class="px-2"><input name="cb[]" type="text" class="form-control form-control-sm" value="${escapeHtml(product.cb || '')}"></td>
                <td class="px-2"><input name="descripcion[]" type="text" class="form-control form-control-sm" value="${escapeHtml(product.nombre || '')}"></td>
                <td class="px-2" style="width:110px"><input name="cantidad[]" type="number" step="1" min="0" class="form-control form-control-sm text-end line-qty" value="${cantidad}"></td>
                <td class="px-2" style="width:130px"><input name="costo[]" type="number" step="0.01" class="form-control form-control-sm text-end line-cost" value="${Number(costo).toFixed(2)}"></td>
                <td class="px-2" style="width:110px"><input name="descuento[]" type="number" step="0.01" class="form-control form-control-sm text-end line-discount" value="${Number(descuento).toFixed(2)}"></td>
                <td class="px-2" style="width:110px"><input name="vcpc[]" type="text" class="form-control form-control-sm text-end line-vcpc" value="${escapeHtml(vcpc)}"></td>
                <td class="px-2 text-center" style="width:60px"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line">Eliminar</button></td>
            </tr>
        `);

                $productosTbody.append(row);
                recalcTotals();
            }

            // remove
            $(document).on('click', '.btn-remove-line', function() {
                $(this).closest('tr').remove();
                recalcTotals();
                // renumber
                $('table.table tbody tr').each(function(i, tr) {
                    $(tr).find('td:first').contents().filter(function() {
                        return this.nodeType === 3;
                    }).first().replaceWith((i + 1).toString());
                });
            });

            // recalc on input changes
            $(document).on('input change', '.line-qty, .line-cost, .line-discount', debounce(function() {
                recalcTotals();
            }, 200));

            // recalc totals function
            function recalcTotals() {
                let totalBruto = 0;
                let totalDescuento = 0;

                $('table.table tbody tr').each(function() {
                    const $tr = $(this);
                    const qty = parseFloat($tr.find('.line-qty').val()) || 0;
                    const cost = parseFloat($tr.find('.line-cost').val()) || 0;
                    const disc = parseFloat($tr.find('.line-discount').val()) || 0;
                    const lineBruto = qty * cost;
                    totalBruto += lineBruto;
                    totalDescuento += isNaN(disc) ? 0 : disc;
                });

                const brutoNeto = totalBruto - totalDescuento;
                const incImpuesto = $('#inc_impuesto').is(':checked');
                const totalImpuesto = incImpuesto ? brutoNeto * TAX_RATE : 0;
                const totalNeto = brutoNeto + totalImpuesto;
                const flete = parseFloat($flete.val()) || 0;
                const totalPagar = totalNeto + flete;

                $totalBruto.val(Number(totalBruto).toFixed(2));
                $totalDescuento.val(Number(totalDescuento).toFixed(2));
                $brutoNeto.val(Number(brutoNeto).toFixed(2));
                $totalImpuesto.val(Number(totalImpuesto).toFixed(2));
                $totalNeto.val(Number(totalNeto).toFixed(2));
                $totalPagar.val(Number(totalPagar).toFixed(2));
            }

            $flete.on('input change', debounce(function() {
                recalcTotals();
            }, 200));
            $(document).on('change', '#inc_impuesto', function() {
                recalcTotals();
            });

            $('#compra-form').on('submit', function(e) {
                const lines = $('table.table tbody tr').length;
                if (lines === 0) {
                    e.preventDefault();
                    Swal.fire('Atención', 'Agrega al menos un producto a la compra', 'warning');
                    return false;
                }
                recalcTotals();
                // allow submit
            });

            // Trigger initial recalc
            recalcTotals();

            // util
            function debounce(fn, delay) {
                let t;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, arguments), delay);
                };
            }
        });
    })(jQuery);
</script>
