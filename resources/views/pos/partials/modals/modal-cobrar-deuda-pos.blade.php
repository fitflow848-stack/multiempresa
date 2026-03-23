<div id="modal-cobrar-deuda-pos" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 3000; display: none; justify-content: center; align-items: center;">
    <div style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); width: 400px; font-family: Arial, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div style="background: #28a745; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px;"><i class='bx bx-money-withdraw'></i> Cobrar Deuda</h3>
            <button onclick="cerrarModalCobrarDeuda()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">×</button>
        </div>
        
        <form id="form-cobrar-deuda-pos" onsubmit="procesarCobroDeuda(event)">
            <input type="hidden" id="cobrar-cliente-id">
            <div style="padding: 20px;">
                <div style="margin-bottom: 20px; padding: 12px; background: #e8f5e9; border-radius: 6px; border-left: 4px solid #28a745;">
                    <span id="cobrar-cliente-nombre" style="font-weight: bold; color: #1b5e20; display: block; margin-bottom: 5px;">CLIENTE</span>
                    <span style="font-size: 13px; color: #388e3c;">Total Deuda Pendiente: </span>
                    <span id="cobrar-monto-total" style="font-weight: bold; font-size: 18px; color: #d32f2f;">S/ 0.00</span>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Monto del Pago (S/)</label>
                    <input type="number" step="0.01" min="0.01" id="cobrar-monto-pago" class="form-control-new" style="font-size: 20px; font-weight: bold; height: 45px; color: #1e7e34;" required>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Medio de Pago</label>
                    <select id="cobrar-metodo-pago" class="form-control-new" style="height: 40px;">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Yape">Yape</option>
                        <option value="Plin">Plin</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta">Tarjeta</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Observaciones</label>
                    <textarea id="cobrar-observaciones" class="form-control-new" style="height: 60px; resize: none;" placeholder="Ej: Pago parcial del mes..."></textarea>
                </div>
            </div>
            
            <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="cerrarModalCobrarDeuda()" style="padding: 10px 18px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Cancelar</button>
                <button type="submit" id="btn-confirmar-cobro" style="padding: 10px 18px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold;">Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>
