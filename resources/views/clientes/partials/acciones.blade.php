<div style="display: flex; gap: 5px;">
    <a href="{{ route('clientes.show', $cliente->id) }}" 
       style="padding: 4px 8px; background: #17a2b8; color: white; text-decoration: none; border-radius: 3px; font-size: 11px;">
        Ver
    </a>
    <a href="{{ route('clientes.edit', $cliente->id) }}" 
       style="padding: 4px 8px; background: #ffc107; color: black; text-decoration: none; border-radius: 3px; font-size: 11px;">
        Editar
    </a>
    <button onclick="eliminarCliente({{ $cliente->id }})" 
            style="padding: 4px 8px; background: #dc3545; color: white; border: none; border-radius: 3px; font-size: 11px; cursor: pointer;">
        Desactivar
    </button>
</div>