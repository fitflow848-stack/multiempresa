<div id="modal-cliente-dni"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
    <div
        style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 500px; font-family: Arial, sans-serif;">
        <h3 style="margin: 0 0 20px 0; color: #007bff; text-align: center;">🔍 Buscar Cliente por DNI</h3>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Número de DNI:</label>
            <input type="text" id="dni-input" placeholder="Ingrese DNI (8 dígitos)" maxlength="8"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                onkeypress="if(event.key==='Enter') buscarPorDNI()">
        </div>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button onclick="buscarPorDNI()"
                style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">🔍
                Buscar</button>
            <button onclick="cerrarModalClienteDNI()"
                style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
        </div>
    </div>
</div>
