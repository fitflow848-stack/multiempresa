<script>
    (function($) {
        'use strict';

        $(function() {
            // CSRF for ajax is already set in blade via $.ajaxSetup (if not, uncomment)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const productosSearchUrl = $('#product-search-form').length ? $('#product-search-form').data(
                'url') || '{{ route('productos.search') }}' : '{{ route('productos.search') }}';
            const $formSearch = $('#product-search-form');
            const $results = $('#product-search-results');
            const $count = $('#product-search-count');

            // referencias tabla compra
            const $productosTbody = $('#productos-tbody');
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

            // add product button in results: open detail modal to allow edits before adding
            $results.on('click', '.btn-add-product', function() {
                const p = $(this).closest('.list-group-item').data('product');
                openProductDetailModal(p);
            });

            // Open and populate product detail modal
            function openProductDetailModal(product) {
                // populate fields
                $('#detail-product-id').val(product.id || '');
                $('#detail-codigo').text(product.cb || product.codigo_ref || '');
                $('#detail-nombre').text(product.nombre || '');

                const costo = (product.precio_compra !== null && product.precio_compra !== undefined) ?
                    Number(product.precio_compra) : (product.precio_linea && product.precio_linea.precio_compra ? Number(product.precio_linea.precio_compra) : 0);

                $('#detail-cantidad').val(1);
                $('#detail-costo').val(Number(costo).toFixed(2));
                $('#detail-descuento').val(0.00);
                $('#detail-stock-min').val(product.stock_min || 0);
                $('#detail-stock-max').val(product.stock_max || 0);
                $('#detail-lote').val(product.lote || '');
                $('#detail-fecha-vencimiento').val(product.fecha_vencimiento || '');

                const detailEl = document.getElementById('productDetailModal');
                const searchEl = document.getElementById('productSearchModal');

                // ensure we reuse modal instances
                const detailModal = bootstrap.Modal.getOrCreateInstance(detailEl);

                // if search modal is open, hide it first then show detail to avoid stacking issues
                const searchModalInstance = bootstrap.Modal.getInstance(searchEl);
                if (searchModalInstance) {
                    // wait until hidden, then show detail
                    const onHidden = function() {
                        searchEl.removeEventListener('hidden.bs.modal', onHidden);
                        detailModal.show();
                    };
                    searchEl.addEventListener('hidden.bs.modal', onHidden);
                    searchModalInstance.hide();
                } else {
                    detailModal.show();
                }

                // temporarily attach the current product data to confirm button (store raw object)
                $('#btn-confirm-add-product').data('product', product);
            }

            // Confirm adding product from modal (namespaced event to prevent duplicate handlers)
            $(document).off('click.productDetailConfirm', '#btn-confirm-add-product')
                .on('click.productDetailConfirm', '#btn-confirm-add-product', function() {
                    const original = $(this).data('product') || {};

                    const producto = {
                        id: original.id || '',
                        linea_id: original.linea_id || (original.precio_linea && original.precio_linea.id) || '',
                        cb: $('#detail-codigo').text().trim(),
                        nombre: $('#detail-nombre').text().trim(),
                        cantidad: Number($('#detail-cantidad').val()) || 1,
                        precio_compra: Number($('#detail-costo').val()) || 0,
                        descuento: Number($('#detail-descuento').val()) || 0,
                        stock_min: Number($('#detail-stock-min').val()) || 0,
                        stock_max: Number($('#detail-stock-max').val()) || 0,
                        lote: $('#detail-lote').val() || '',
                        fecha_vencimiento: $('#detail-fecha-vencimiento').val() || ''
                    };

                    // Add to table using existing helper. The helper expects product.precio_compra, so provide that.
                    addProductToCompra(producto);

                    // Hide detail modal after adding
                    const detailEl = document.getElementById('productDetailModal');
                    const detailModalInstance = bootstrap.Modal.getInstance(detailEl);
                    if (detailModalInstance) {
                        detailModalInstance.hide();

                        // small delay then cleanup backdrops in case Bootstrap left one behind
                        setTimeout(function() {
                            cleanupModalBackdrops();
                        }, 150);
                    } else {
                        // ensure cleanup regardless
                        setTimeout(cleanupModalBackdrops, 150);
                    }
                });

            // Remove leftover modal-backdrop elements and modal-open class when no modal is visible
            function cleanupModalBackdrops() {
                // If any modal is still shown, skip cleanup
                const anyShown = document.querySelectorAll('.modal.show').length > 0;
                if (anyShown) return;

                // remove backdrop elements
                document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                    el.parentNode && el.parentNode.removeChild(el);
                });

                // remove modal-open class from body
                document.body.classList.remove('modal-open');

                // remove inline padding-right added by bootstrap
                document.body.style.paddingRight = '';
            }

            function addProductToCompra(product) {
                console.log('pruebaba' + product);
                const cantidad = 1;
                const costo = (product.precio_compra !== null && product.precio_compra !== undefined) ?
                    Number(product.precio_compra) :
                    (product.precio_linea && product.precio_linea.precio_compra ? Number(product
                        .precio_linea.precio_compra) : 0);

                const descuento = 0;
                const stockMin = product.stock_min || 0;
                const stockMax = product.stock_max || 0;
                const lote = product.lote || '';
                const fechaVencimiento = product.fecha_vencimiento || '';
                const idx = lineIndex++;

                // Hide no-products message
                $('#no-products').hide();

                const totalCalculado = (cantidad * costo - descuento).toFixed(2);

                const lineaId = product.linea_id || (product.precio_linea && product.precio_linea.id) || '';
                const row = `
                <tr data-idx="${idx}" data-linea-id="${lineaId}" data-producto-id="${product.id || ''}">
                    <td class="text-center">${idx}
                        <input type="hidden" name="product_id[]" value="${escapeHtml(product.id)}">
                        <input type="hidden" name="linea_id[]" value="${escapeHtml(lineaId)}">
                    </td>
                <td>
                    <input name="codigo[]" type="text" class="form-control form-control-sm" 
                           value="${escapeHtml(product.cb || product.codigo_ref || '')}" readonly>
                </td>
                <td>
                    <input name="descripcion[]" type="text" class="form-control form-control-sm" 
                           value="${escapeHtml(product.nombre || '')}" readonly>
                </td>
                <td>
                    <input name="cantidad[]" type="number" step="1" min="1" 
                           class="form-control form-control-sm text-center cantidad-input" value="${cantidad}">
                </td>
                <td>
                    <input name="costo[]" type="number" step="0.01" min="0" 
                           class="form-control form-control-sm text-end costo-input" value="${Number(costo).toFixed(2)}">
                </td>
                <td>
                    <input name="descuento[]" type="number" step="0.01" min="0" 
                           class="form-control form-control-sm text-end descuento-input" value="${Number(descuento).toFixed(2)}">
                </td>
                <td>
                    <input name="stock_min[]" type="number" step="1" min="0" 
                           class="form-control form-control-sm text-center stock-min-input" 
                           value="${stockMin}" placeholder="0">
                </td>
                <td>
                    <input name="stock_max[]" type="number" step="1" min="0" 
                           class="form-control form-control-sm text-center stock-max-input" 
                           value="${stockMax}" placeholder="0">
                </td>
                <td>
                    <input name="lote[]" type="text" maxlength="50" 
                           class="form-control form-control-sm text-center lote-input" 
                           value="${escapeHtml(lote)}" placeholder="Lote...">
                </td>
                <td>
                    <input name="fecha_vencimiento[]" type="date" 
                           class="form-control form-control-sm fecha-vencimiento-input" 
                           value="${fechaVencimiento}">
                </td>
                <td class="text-end total-line">S/ ${totalCalculado}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" title="Eliminar">
                        <i class='bx  bx-x-circle'></i> 
                    </button>
                </td>
            </tr>
        `;

                $productosTbody.append(row);

                // Call the global function to recalculate totals
                if (window.calculateTotals) {
                    window.calculateTotals();
                }

                // Auto-guardar después de agregar producto desde modal
                if (window.autoSaveCompraData) {
                    setTimeout(window.autoSaveCompraData, 500);
                    console.log('Auto-guardado ejecutado después de agregar producto desde modal');
                }
                
                console.log('Producto agregado desde modal con todos los campos:', product);
            }

            // remove
            $(document).on('click', '.btn-remove-line', function() {
                $(this).closest('tr').remove();

                // Show no-products message if no products left
                if ($('#productos-tbody tr:not(#no-products)').length === 0) {
                    $('#no-products').show();
                }

                // Call the global function to recalculate totals
                if (window.calculateTotals) {
                    window.calculateTotals();
                }

                // renumber rows
                $('#productos-tbody tr:not(#no-products)').each(function(i, tr) {
                    $(tr).find('td:first').html((i + 1) +
                        '<input type="hidden" name="product_id[]" value="' +
                        $(tr).find('input[name="product_id[]"]').val() + '">');
                });

                // Auto-guardar después de eliminar producto
                if (window.autoSaveCompraData) {
                    setTimeout(window.autoSaveCompraData, 500);
                    console.log('Auto-guardado ejecutado después de eliminar producto');
                }
            });

            // recalc on input changes
            $(document).on('input change', '.cantidad-input, .costo-input, .descuento-input', function() {
                // Update line total
                const $row = $(this).closest('tr');
                const cantidad = parseFloat($row.find('.cantidad-input').val()) || 0;
                const costo = parseFloat($row.find('.costo-input').val()) || 0;
                const descuento = parseFloat($row.find('.descuento-input').val()) || 0;
                const total = (cantidad * costo) - descuento;

                $row.find('.total-line').text('S/ ' + total.toFixed(2));

                // Call the global function to recalculate totals
                if (window.calculateTotals) {
                    window.calculateTotals();
                }

                // Auto-guardar después de cambiar cantidades/costos/descuentos
                if (window.autoSaveCompraData) {
                    clearTimeout(window.autoSaveTimeout);
                    window.autoSaveTimeout = setTimeout(window.autoSaveCompraData, 1000);
                }
            });

            // recalc totals function
            function recalcTotals() {
                let totalBruto = 0;
                let totalDescuento = 0;
                let productCount = 0;

                $('#productos-tbody tr:not(#no-products)').each(function() {
                    const $tr = $(this);
                    const qty = parseFloat($tr.find('.cantidad-input').val()) || 0;
                    const cost = parseFloat($tr.find('.costo-input').val()) || 0;
                    const disc = parseFloat($tr.find('.descuento-input').val()) || 0;
                    const lineBruto = qty * cost;
                    totalBruto += lineBruto;
                    totalDescuento += isNaN(disc) ? 0 : disc;
                    productCount++;
                });

                const brutoNeto = totalBruto - totalDescuento;
                const incImpuesto = $('#inc_impuesto').is(':checked');
                const totalImpuesto = incImpuesto ? brutoNeto * TAX_RATE : 0;
                const totalNeto = brutoNeto + totalImpuesto;
                const flete = parseFloat($flete.val()) || 0;
                const totalPagar = totalNeto + flete;

                // Update displays using the global elements
                $('#subtotal-display').text('S/ ' + totalBruto.toFixed(2));
                $('#descuento-display').text('S/ ' + totalDescuento.toFixed(2));
                $('#impuestos-display').text('S/ ' + totalImpuesto.toFixed(2));
                $('#total-display').text('S/ ' + totalNeto.toFixed(2));
                $('#productos-count').text(productCount);

                // Update hidden form inputs
                $totalBruto.val(Number(totalBruto).toFixed(2));
                $totalDescuento.val(Number(totalDescuento).toFixed(2));
                $brutoNeto.val(Number(brutoNeto).toFixed(2));
                $totalImpuesto.val(Number(totalImpuesto).toFixed(2));
                $totalNeto.val(Number(totalNeto).toFixed(2));
                if ($totalPagar.length) {
                    $totalPagar.val(Number(totalPagar).toFixed(2));
                }

                // Show/hide no products message
                if (productCount === 0) {
                    $('#no-products').show();
                } else {
                    $('#no-products').hide();
                }
            }

            // Connect with flete input if exists
            if ($flete.length) {
                $flete.on('input change', function() {
                    recalcTotals();
                });
            }

            $(document).on('change', '#inc_impuesto', function() {
                recalcTotals();
            });

            $('#compra-form').on('submit', function(e) {
                const lines = $('#productos-tbody tr:not(#no-products)').length;
                if (lines === 0) {
                    e.preventDefault();
                    Swal.fire('Atención', 'Agrega al menos un producto a la compra', 'warning');
                    return false;
                }
                // allow submit
            });

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
