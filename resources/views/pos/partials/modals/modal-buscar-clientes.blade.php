<div id="modal-buscar-clientes" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 700px; max-height: 80%; overflow: hidden; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="background: #17a2b8; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px;">📋 Selección de Cliente</h3>
                <button onclick="cerrarBuscadorClientes()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">×</button>
            </div>
            
            <!-- Botones de acción -->
            <div style="padding: 15px 20px; border-bottom: 1px solid #dee2e6; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <button onclick="mostrarFormularioNuevoCliente()" style="padding: 10px 18px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(40,167,69,0.2);">
                    <i class='bx bx-plus-circle'></i> Nuevo cliente
                </button>
                <button onclick="usarClienteContado()" style="padding: 10px 18px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(40,167,69,0.2);" title="Usar cliente contable para ventas rápidas">
                    <i class='bx bx-purchase-tag'></i> Cliente Contable
                </button>
                <button onclick="seleccionarClienteSeleccionado()" style="padding: 10px 18px; background: #17a2b8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(23,162,184,0.2);">
                    <i class='bx bx-user-check'></i> Usar seleccionado
                </button>
                <button onclick="mostrarFormularioDNI()" style="padding: 10px 18px; background: #ffffff; color: #dc3545; border: 1.5px solid #dc3545; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.background='#dc3545'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#dc3545'">
                    <i class='bx bx-globe'></i> reniec/sunat
                </button>
            </div>
            
            <!-- Campo de búsqueda -->
            <div style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">
                <input type="text" id="buscar-cliente-input" placeholder="Buscar cliente" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onkeyup="buscarClientes()">
            </div>
            
            <!-- Lista de clientes -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa; position: sticky; top: 0;">
                        <tr>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">#</th>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">DNI/RUC</th>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">Cliente</th>
                            <th style="padding: 8px; text-align: right; font-size: 12px; border-bottom: 1px solid #dee2e6;">Debe</th>
                            <th style="padding: 8px; text-align: center; font-size: 12px; border-bottom: 1px solid #dee2e6;">Acc.</th>
                        </tr>
                    </thead>
                    <tbody id="lista-clientes-tbody">
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">Cargando clientes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div style="background: #17a2b8; color: white; padding: 10px 20px; text-align: center; font-size: 12px;">
                Volver TPV
            </div>
        </div>
    </div>