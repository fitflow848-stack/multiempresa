<div id="modal-nuevo-cliente" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2001; display: none; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 550px; overflow: hidden; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        
        <!-- Header con diseño premium -->
        <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 20px; text-align: center; position: relative;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600; letter-spacing: 0.5px;">
                <i class='bx bx-user-plus me-1'></i> Registrar Nuevo Cliente
            </h3>
            <p style="margin: 5px 0 0 0; opacity: 0.85; font-size: 0.85rem;">Complete los datos o busque por DNI/RUC</p>
            <button onclick="cerrarModalNuevoCliente()" style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <div style="padding: 25px;">
            <!-- Selector de Tipo de Cliente (Persona / Empresa) -->
            <div style="display: flex; gap: 10px; margin-bottom: 25px;">
                <button type="button" id="btn-tipo-persona" onclick="setTipoCliente('Particular')" style="flex: 1; padding: 12px; border: 2px solid #28a745; border-radius: 8px; background: #eafaf1; color: #1e7e34; font-weight: 700; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <i class='bx bx-user fs-3'></i> Persona
                </button>
                <button type="button" id="btn-tipo-empresa" onclick="setTipoCliente('Empresa')" style="flex: 1; padding: 12px; border: 2px solid #dee2e6; border-radius: 8px; background: white; color: #6c757d; font-weight: 700; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <i class='bx bx-buildings fs-3'></i> Empresa
                </button>
                <input type="hidden" id="tipo-cliente" value="Particular">
            </div>

            <!-- Sección de Documento con Buscador RENIEC/SUNAT -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444; font-size: 0.9rem;">Documento de Identidad:</label>
                <div style="display: flex; gap: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; border: 1px solid #ced4da;">
                    <select id="tipo-documento" onchange="ajustarPlaceholderDoc()" style="width: 90px; padding: 10px 5px; border: none; background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #495057; border-right: 1px solid #dee2e6; outline: none;">
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                        <option value="CE">CE</option>
                    </select>
                    <input type="text" id="nro-documento" placeholder="Ingrese 8 dígitos" maxlength="11" style="flex-grow: 1; padding: 12px; border: none; font-size: 1rem; outline: none; transition: all 0.2s;">
                    <button type="button" onclick="consultarServicioIdentidad()" id="btn-search-reniec" style="background: #28a745; color: white; border: none; width: 55px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" title="Buscar en RENIEC/SUNAT">
                        <i class='bx bx-search-alt fs-4'></i>
                    </button>
                </div>
                <small id="doc-hint" style="display: block; margin-top: 6px; color: #6c757d; font-size: 0.75rem;">Consulte el documento usando la lupa para autocompletar.</small>
            </div>

            <hr style="border: 0; border-top: 1px dashed #dee2e6; margin: 25px 0;">

            <!-- Campos Manuales -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 18px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 0.85rem;">Nombre Completo / Razón Social:</label>
                    <input type="text" id="nombre-cliente" placeholder="Nombre que aparecerá en el ticket" style="width: 100%; padding: 11px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.95rem; outline: none;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 0.85rem;">Dirección Fiscal:</label>
                    <input type="text" id="direccion-cliente" placeholder="Opcional" style="width: 100%; padding: 11px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.95rem; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 0.85rem;">Email (para envío XML):</label>
                        <input type="email" id="email-cliente" placeholder="correo@ejemplo.com" style="width: 100%; padding: 11px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.95rem; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 0.85rem;">Celular / WhatsApp:</label>
                        <input type="text" id="telefono-cliente" placeholder="Nro de contacto" style="width: 100%; padding: 11px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con Botones -->
        <div style="background: #f8f9fa; padding: 15px 25px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #eee;">
            <button onclick="cerrarModalNuevoCliente()" style="padding: 10px 20px; background: white; color: #6c757d; border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                Cancelar
            </button>
            <button onclick="registrarNuevoCliente()" style="padding: 10px 30px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2); transition: all 0.2s;">
                <i class='bx bx-save me-1'></i> Guardar Cliente
            </button>
        </div>
    </div>
</div>

<style>
    #nro-documento:focus { border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1) !important; }
    #modal-nuevo-cliente input:focus { border-color: #28a745; box-shadow: 0 0 8px rgba(40, 167, 69, 0.1); }
</style>
