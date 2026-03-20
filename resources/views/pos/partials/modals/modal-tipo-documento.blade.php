<div id="modal-tipo-documento"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
    <div
        style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 400px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div
            style="background: #007bff; color: white; padding: 15px 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <h3 style="margin: 0; font-size: 16px;">📋 Seleccionar Tipo de Documento</h3>
        </div>

        <!-- Lista de tipos de documento -->
        <div style="padding: 20px;">
            @foreach($documentos as $idx => $doc)
                @php
                    $docName = strtolower($doc->nombre);
                    $val = 'boleta';
                    $icon = '🧾';
                    $color = '#17a2b8';
                    $desc = 'Comprobante estándar';
                    
                    if (str_contains($docName, 'boleta')) { 
                        $val = 'boleta'; $icon = '🧾'; $color = '#17a2b8'; $desc = 'Para personas naturales';
                    } elseif (str_contains($docName, 'factura')) { 
                        $val = 'factura'; $icon = '📊'; $color = '#28a745'; $desc = 'Para empresas con RUC';
                    } elseif (str_contains($docName, 'nota de venta') || str_contains($docName, 'nota venta')) { 
                        $val = 'ticket'; $icon = '📝'; $color = '#20c997'; $desc = 'Documento informativo (Nota)';
                    } elseif (str_contains($docName, 'ticket')) { 
                        $val = 'ticket'; $icon = '📄'; $color = '#6f42c1'; $desc = 'Comprobante interno';
                    }
                @endphp
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('{{ $val }}')"
                     data-id-tido="{{ $doc->id_tido }}"
                     style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <div style="background: {{ $color }}; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">
                        {{ $icon }} [{{ $idx + 1 }}]
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #495057;">{{ $doc->nombre }}</div>
                        <div style="font-size: 12px; color: #6c757d;">{{ $desc }}</div>
                    </div>
                </div>
            @endforeach

            @if($documentos->isEmpty())
                <div style="text-align: center; color: #888;">No hay documentos configurados para esta sucursal.</div>
            @endif
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 10px 20px; text-align: center; border-radius: 0 0 8px 8px;">
            <button onclick="cerrarModalTipoDocumento()"
                style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
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
