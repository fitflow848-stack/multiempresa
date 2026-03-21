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
                Haga clic o use los números del teclado [1, 2, 3...]
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
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('{{ $val }}')"
                     data-id-tido="{{ $doc->id_tido }}"
                     style="display: flex; align-items: center; padding: 15px; margin: 10px 0; background: #ffffff; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                     onmouseover="this.style.borderColor='#007bff'; this.style.background='#f0f7ff';"
                     onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#ffffff';">
                    
                    <div style="background: {{ $color }}; color: white; padding: 10px; border-radius: 8px; margin-right: 15px; font-weight: 800; min-width: 70px; text-align: center; font-size: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                        {{ $icon }} [{{ $idx + 1 }}]
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: #111827; font-size: 16px; text-transform: uppercase;">{{ $doc->nombre }}</div>
                        <div style="font-size: 13px; color: #4b5563; margin-top: 2px; font-weight: 500;">{{ $desc }}</div>
                    </div>
                    <i class='bx bx-chevron-right' style="font-size: 24px; color: #d1d5db;"></i>
                </div>
            @endforeach

            @if($documentos->isEmpty())
                <div style="text-align: center; color: #ef4444; padding: 20px; font-weight: 600;">⚠️ No hay formatos configurados para esta sucursal.</div>
            @endif
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; padding: 15px 20px; text-align: center; border-top: 1px solid #e5e7eb;">
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
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('modal-tipo-documento');
        if (modal && modal.style.display === 'flex') {
            const options = Array.from(modal.querySelectorAll('.tipo-documento-option'));
            const keyNum = parseInt(e.key);
            if (!isNaN(keyNum) && keyNum > 0 && keyNum <= options.length) {
                options[keyNum - 1].click();
            }
        }
    });
</script>
