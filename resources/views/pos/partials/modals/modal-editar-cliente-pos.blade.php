<div id="modal-editar-cliente-pos" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 3000; display: none; justify-content: center; align-items: center;">
    <div style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); width: 400px; font-family: Arial, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div style="background: #17a2b8; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px;"><i class='bx bx-edit'></i> Editar Cliente</h3>
            <button onclick="cerrarModalEditarCliente()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">×</button>
        </div>
        
        <form id="form-editar-cliente-pos" onsubmit="guardarEdicionCliente(event)">
            <input type="hidden" id="edit-cliente-id">
            <div style="padding: 20px;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Nombre / Razón Social</label>
                    <input type="text" id="edit-cliente-nombre" class="form-control-new" readonly style="background: #f8f9fa;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Teléfono / Celular</label>
                    <input type="text" id="edit-cliente-telefono" class="form-control-new" placeholder="Ingrese número">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Dirección</label>
                    <textarea id="edit-cliente-direccion" class="form-control-new" style="height: 60px; resize: none;" placeholder="Ingrese dirección"></textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #444;">Email</label>
                    <input type="email" id="edit-cliente-email" class="form-control-new" placeholder="correo@ejemplo.com">
                </div>
            </div>
            
            <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="cerrarModalEditarCliente()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Cancelar</button>
                <button type="submit" id="btn-guardar-edit-cliente" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold;">Actualizar Datos</button>
            </div>
        </form>
    </div>
</div>
