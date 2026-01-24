<div id="modal-nuevo-cliente"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
    <div
        style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 600px; max-height: 80%; overflow-y: auto; font-family: Arial, sans-serif;">
        <h3 style="margin: 0 0 20px 0; color: #28a745; text-align: center;">👤 Nuevo Cliente</h3>
        <div style="color: #6c757d; text-align: center; margin-bottom: 20px; font-size: 14px;">Datos administrativos del
            cliente</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Tipo
                    Cliente:</label>
                <select id="tipo-cliente"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="Particular">Particular</option>
                    <option value="Empresa">Empresa</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Tipo
                    Documento:</label>
                <select id="tipo-documento"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="DNI">DNI</option>
                    <option value="RUC">RUC</option>
                    <option value="CE">Carnet de Extranjería</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Nro.
                    Documento:</label>
                <input type="text" id="nro-documento" placeholder="Número de documento"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Nombre:</label>
                <input type="text" id="nombre-cliente" placeholder="Nombre completo"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Dirección:</label>
                <input type="text" id="direccion-cliente" placeholder="Dirección completa"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Email:</label>
                <input type="email" id="email-cliente" placeholder="correo@ejemplo.com"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Teléfono:</label>
                <input type="text" id="telefono-cliente" placeholder="Número de teléfono"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 25px;">
            <button onclick="registrarNuevoCliente()"
                style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">💾
                Registrar</button>
            <button onclick="cerrarModalNuevoCliente()"
                style="padding: 10px 25px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
        </div>
    </div>
</div>
