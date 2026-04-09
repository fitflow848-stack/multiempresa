<div id="modal-romper-docena" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2005; display: none; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
    <div style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.4); width: 100%; max-width: 550px; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; max-height: 90vh; margin: 20px; display: flex; flex-direction: column;">
        
        <!-- Header -->
        <div style="background: #d63384; color: white; padding: 20px; display: flex; align-items: center; gap: 12px;">
            <i class='bx bx-git-branch' style="font-size: 1.8rem;"></i>
            <h3 style="margin: 0; font-size: 1.3rem; font-weight: 700;">conversión Docena / Saco / Granel</h3>
            <button onclick="cerrarModalRomperDocena()" style="margin-left: auto; background: none; border: none; color: white; cursor: pointer; font-size: 1.5rem;">&times;</button>
        </div>

        <div style="padding: 25px; overflow-y: auto; overflow-x: visible; flex: 1;">
            <!-- Producto Origen (Saco) -->
            <div style="background: #fff0f6; border: 1.5px solid #d63384; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-size: 0.85rem; color: #d63384; font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">📍 Producto a convertir (Bulk)</div>
                <div id="romper-nombre-origen" style="font-weight: 700; font-size: 1.1rem; color: #333;">Fosfato di Amonico / SACO 50 KG</div>
                <div style="display: flex; gap: 15px; margin-top: 8px; font-size: 0.95rem;">
                    <span>Stock actual: <strong id="romper-stock-origen">0.00</strong></span>
                </div>
            </div>

            <!-- Cantidad a Romper -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #495057;">¿Cuántos sacos/unidades vas a convertir?</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="number" id="romper-cantidad-bulk" value="1" min="1" step="any"
                        style="width: 120px; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 1.1rem; text-align: center; font-weight: 700;">
                    <span id="romper-unidad-bulk" style="font-weight: 600; color: #6c757d;">UNIDADES / SACOS</span>
                </div>
            </div>

            <hr style="border: 0; border-top: 2px dashed #dee2e6; margin: 25px 0;">

            <!-- Producto Destino (Kilos) -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #495057;">Destino: Escoger producto para pasar stock</label>
                <div style="position: relative;">
                    <i class='bx bx-search' style="position: absolute; left: 12px; top: 14px; color: #6c757d; font-size: 1.3rem;"></i>
                    <input type="text" id="romper-search-target" placeholder="Buscar producto similar (ej. Kg, Suelto)..." 
                        style="width: 100%; padding: 12px 12px 12px 40px; border: 2px solid #ced4da; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <!-- Resultados de búsqueda rápidos -->
                <div id="romper-resultados-container" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; margin-top: 4px; max-height: 220px; overflow-y: auto; border: 1px solid #eee; border-radius: 6px; display: none; background: #fafafa; box-shadow: 0 6px 20px rgba(0,0,0,0.12);">
                    <!-- Se llenará con JS -->
                </div>

                <div id="romper-producto-seleccionado" style="margin-top: 10px; padding: 10px; background: #e7f3ff; border: 1px solid #007bff; border-radius: 6px; display: none; align-items: center; gap: 10px;">
                    <i class='bx bxs-check-circle' style="color: #007bff; font-size: 1.3rem;"></i>
                    <span id="romper-nombre-destino" style="font-weight: 600; color: #004085;">Producto Destino</span>
                    <button onclick="cancelarSeleccionDestino()" style="margin-left: auto; color: #dc3545; background: none; border: none; cursor: pointer; font-weight: 700;">(Cambiar)</button>
                </div>
            </div>

            <!-- Equivante / Factor -->
            <div id="romper-factor-container" style="margin-bottom: 10px; display: none;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #495057;">¿Cuántas unidades resultantes (por cada uno)?</label>
                <div style="display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1.5px solid #dee2e6;">
                    <div style="text-align: center;">
                        <input type="number" id="romper-factor" value="12" min="1" step="any"
                            style="width: 100px; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 1.1rem; text-align: center; font-weight: 700;">
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 5px;">EQUIVALENCIA</div>
                    </div>
                    <div style="font-size: 1.5rem; color: #adb5bd;">&times;</div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: #6c757d;">Total a aumentar en destino:</div>
                        <div id="romper-total-destino" style="font-size: 1.4rem; font-weight: 800; color: #28a745;">0.00 Unid.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div style="padding: 20px; background: #f8f9fa; display: flex; gap: 15px; border-top: 1px solid #eee;">
            <button onclick="cerrarModalRomperDocena()" style="flex: 1; padding: 15px; background: #f1f3f5; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; color: #495057;">
                CANCELAR
            </button>
            <button onclick="procesarRoturaManual()" id="btn-confirmar-romper" style="flex: 2; padding: 15px; background: #d63384; color: white; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(214,51,132,0.3);">
                DIFERENCIAR Y AUMENTAR STOCK
            </button>
        </div>
    </div>
</div>
