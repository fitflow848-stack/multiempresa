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
            <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('ticket')"
                style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                <div
                    style="background: #6f42c1; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">
                    📄</div>
                <div>
                    <div style="font-weight: 600; color: #495057;">Ticket</div>
                    <div style="font-size: 12px; color: #6c757d;">Comprobante interno</div>
                </div>
            </div>

            <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('boleta')"
                style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                <div
                    style="background: #17a2b8; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">
                    🧾</div>
                <div>
                    <div style="font-weight: 600; color: #495057;">Boleta</div>
                    <div style="font-size: 12px; color: #6c757d;">Para personas naturales</div>
                </div>
            </div>

            <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('factura')"
                style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                <div
                    style="background: #28a745; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">
                    📊</div>
                <div>
                    <div style="font-weight: 600; color: #495057;">Factura</div>
                    <div style="font-size: 12px; color: #6c757d;">Para empresas con RUC</div>
                </div>
            </div>

            <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('nota-venta')"
                style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                <div
                    style="background: #20c997; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">
                    📝</div>
                <div>
                    <div style="font-weight: 600; color: #495057;">Nota Venta</div>
                    <div style="font-size: 12px; color: #6c757d;">Documento informativo</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 10px 20px; text-align: center; border-radius: 0 0 8px 8px;">
            <button onclick="cerrarModalTipoDocumento()"
                style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
        </div>
    </div>
</div>
