<div id="modal-cliente-dni" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2005; display: none; justify-content: center; align-items: center; backdrop-filter: blur(2px);">
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 100%; max-width: 450px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        
        <h3 style="margin: 0 0 25px 0; color: #28a745; text-align: center; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class='bx bx-building-house' style="font-size: 1.8rem;"></i> Crear Cliente desde RENIEC
        </h3>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #495057; font-size: 0.95rem;">Número de Documento:</label>
            <input type="text" id="dni-input" placeholder="DNI (8 dígitos) o RUC (11 dígitos)" maxlength="11"
                style="width: 100%; padding: 12px 15px; border: 2px solid #000; border-radius: 8px; font-size: 1.1rem; outline: none; font-weight: 500;"
                onkeypress="if(event.key==='Enter') buscarPorDNIrapido()">
            <small style="display: block; margin-top: 8px; color: #6c757d; font-size: 0.85rem;">Ingrese 8 dígitos para DNI o 11 para RUC</small>
        </div>

        <div style="display: flex; justify-content: center; gap: 15px; margin-top: 10px;">
            <button onclick="buscarPorDNIrapido()" id="btn-crear-reniec-directo"
                style="padding: 12px 25px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 1rem; box-shadow: 0 3px 6px rgba(40,167,69,0.2);">
                <i class='bx bx-building-house'></i> Crear Cliente
            </button>
            <button onclick="cerrarModalClienteDNI()"
                style="padding: 12px 25px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                Cancelar
            </button>
        </div>
    </div>
</div>
