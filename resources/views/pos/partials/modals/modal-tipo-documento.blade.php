<div id="modal-tipo-documento"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2005; display: none; justify-content: center; align-items: center;">
    <div
        style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-width: 450px; width: 90%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div
            style="background: #007bff; color: white; padding: 18px 20px; text-align: center;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #ffffff !important;">📋 Seleccionar Tipo de Documento</h3>
        </div>

        <!-- Lista de tipos de documento -->
        <div style="padding: 20px;">
            <p style="text-align: center; color: #4b5563; font-size: 14px; margin-bottom: 15px; font-weight: 500;">
                Seleccione un documento y pulse Aceptar [o use los números 1, 2, 3...]
            </p>
            @foreach($documentos as $idx => $doc)
                @php
                    $docName = strtolower($doc->nombre);
                    $val = 'boleta';
                    $icon = '🧾';
                    $color = '#117a8b';
                    $desc = 'Comprobante estándar';
                    
                    if (str_contains($docName, 'boleta')) { 
                        $val = 'boleta'; $icon = '🧾'; $color = '#117a8b'; $desc = 'Para personas naturales (DNI)';
                    } elseif (str_contains($docName, 'factura')) { 
                        $val = 'factura'; $icon = '📊'; $color = '#1e7e34'; $desc = 'Para empresas con RUC';
                    } elseif (str_contains($docName, 'nota de venta') || str_contains($docName, 'nota venta')) { 
                        $val = 'ticket'; $icon = '📝'; $color = '#138496'; $desc = 'Documento interno informativo';
                    } elseif (str_contains($docName, 'ticket')) { 
                        $val = 'ticket'; $icon = '📄'; $color = '#593196'; $desc = 'Comprobante de consumo';
                    }
                @endphp
                <div class="tipo-documento-option" onclick="marcarTipoDocumento(this, '{{ $val }}')"
                     data-val="{{ $val }}"
                     data-id-tido="{{ $doc->id_tido }}"
                     data-nombre="{{ $doc->nombre }}"
                     style="display: flex; align-items: center; padding: 15px; margin: 10px 0; background: #ffffff; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                     onmouseover="if(!this.classList.contains('active')) { this.style.borderColor='#007bff'; this.style.background='#f0f7ff'; }"
                     onmouseout="if(!this.classList.contains('active')) { this.style.borderColor='#e5e7eb'; this.style.background='#ffffff'; }">
                    
                    <div style="background: {{ $color }}; color: white; padding: 10px; border-radius: 8px; margin-right: 15px; font-weight: 800; min-width: 70px; text-align: center; font-size: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                        {{ $icon }} [{{ $idx + 1 }}]
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: #111827; font-size: 16px; text-transform: uppercase;">{{ $doc->nombre }}</div>
                        <div style="font-size: 13px; color: #4b5563; margin-top: 2px; font-weight: 500;">{{ $desc }}</div>
                    </div>
                    <div class="check-icon" style="display: none;">
                        <i class='bx bx-check-circle' style="font-size: 28px; color: #007bff;"></i>
                    </div>
                </div>
            @endforeach

            @if($documentos->isEmpty())
                <div style="text-align: center; color: #ef4444; padding: 20px; font-weight: 600;">⚠️ No hay formatos configurados para esta sucursal.</div>
            @endif
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; padding: 15px 20px; text-align: center; border-top: 1px solid #e5e7eb; display: flex; gap: 10px; justify-content: center;">
            <button id="btn-aceptar-tipo-doc" onclick="confirmarSeleccionTipoDocumento()"
                style="padding: 10px 40px; background: #22c55e; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 15px; transition: all 0.2s;"
                onmouseover="this.style.background='#16a34a'"
                onmouseout="this.style.background='#22c55e'">
                Aceptar
            </button>
            <button onclick="cerrarModalTipoDocumento()"
                style="padding: 10px 30px; background: #4b5563; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 15px; transition: background 0.2s;"
                onmouseover="this.style.background='#374151'"
                onmouseout="this.style.background='#4b5563'">
                Cerrar Ventana
            </button>
        </div>
    </div>
</div>

<script>
    let documentSelectedVal = null;
    let documentSelectedName = '';

    function marcarTipoDocumento(el, val) {
        // Desmarcar todos
        const options = document.querySelectorAll('.tipo-documento-option');
        options.forEach(opt => {
            opt.classList.remove('active');
            opt.style.borderColor = '#e5e7eb';
            opt.style.background = '#ffffff';
            opt.querySelector('.check-icon').style.display = 'none';
        });

        // Marcar el actual
        el.classList.add('active');
        el.style.borderColor = '#007bff';
        el.style.background = '#f0f7ff';
        el.querySelector('.check-icon').style.display = 'block';

        documentSelectedVal = val;
        documentSelectedName = el.getAttribute('data-nombre');
        
        // Efecto visual en el botón
        const btn = document.getElementById('btn-aceptar-tipo-doc');
        if(btn) {
            btn.style.background = '#22c55e';
            btn.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => btn.classList.remove('animate__animated', 'animate__pulse'), 500);
        }
    }

    async function confirmarSeleccionTipoDocumento() {
        if (!documentSelectedVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor, seleccione un tipo de documento.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        // Guardar valores ANTES de cerrar la modal (cerrar resetea documentSelectedVal)
        const tipoSeleccionado = documentSelectedVal;
        const nombreSeleccionado = documentSelectedName;

        // Cerrar el modal de tipo documento inmediatamente
        cerrarModalTipoDocumento();

        const { isConfirmed } = await Swal.fire({
            title: '¿Confirmar Emisión?',
            text: `¿Estás seguro que desea emitir una ${nombreSeleccionado.toUpperCase()}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22c55e',
            cancelButtonColor: '#4b5563',
            confirmButtonText: 'Sí, emitir',
            cancelButtonText: 'Cancelar'
        });

        if (isConfirmed) {
            if (typeof seleccionarTipoDocumento === 'function') {
                seleccionarTipoDocumento(tipoSeleccionado);
            }
        }
    }

    document.addEventListener('keydown', function(e) {
        // No interferir si hay una alerta de SweetAlert activa
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            return;
        }

        const modal = document.getElementById('modal-tipo-documento');
        if (modal && modal.style.display === 'flex') {
            const options = Array.from(modal.querySelectorAll('.tipo-documento-option'));
            const keyNum = parseInt(e.key);
            
            // Números para seleccionar (1-9)
            if (!isNaN(keyNum) && keyNum > 0 && keyNum <= options.length) {
                e.preventDefault();
                e.stopPropagation();
                options[keyNum - 1].click();
            }

            // Enter para confirmar
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                if (documentSelectedVal) {
                    confirmarSeleccionTipoDocumento();
                }
            }

            // Escape para cerrar
            if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();
                cerrarModalTipoDocumento();
            }
        }
    }, true); // Use capture phase to intercept before other listeners
</script>
