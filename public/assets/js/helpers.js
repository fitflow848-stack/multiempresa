function mostrarNotificacion(mensaje) {
    // Crear notificación temporal
    const notificacion = document.createElement("div");
    notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 10000000;
                font-size: 14px;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
    notificacion.textContent = mensaje;
    document.body.appendChild(notificacion);

    // Mostrar con animación
    setTimeout(() => (notificacion.style.opacity = "1"), 100);

    // Ocultar después de 3 segundos
    setTimeout(() => {
        notificacion.style.opacity = "0";
        setTimeout(() => document.body.removeChild(notificacion), 300);
    }, 3000);
}

function cerrarModal(modal) {
    if (modal) {
        modal.remove();
    }
}

function limpiarLista() {
    if (confirm("¿Está seguro que desea limpiar la lista de productos?")) {
        document.getElementById("productos-tbody").innerHTML = "";
    }
    cerrarContextMenu();
}


// Agregar estilos CSS para el menú contextual
const style = document.createElement("style");
style.textContent = `
            .context-menu-item {
                position: relative;
                transition: all 0.2s ease;
                font-size: 13px;
            }
            .context-menu-item:hover {
                background-color: #e3f2fd !important;
                font-weight: 500;
            }
            .context-menu-item:active {
                background-color: #bbdefb !important;
            }
            .productos-table tr:hover {
                background: #E7F3FF !important;
                cursor: pointer;
            }
            .productos-table tr:hover td {
                font-weight: 500;
            }
            #context-menu {
                animation: fadeIn 0.2s ease;
                border: 2px solid #2196F3;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0.9); }
                to { opacity: 1; transform: scale(1); }
            }
        `;
document.head.appendChild(style);
