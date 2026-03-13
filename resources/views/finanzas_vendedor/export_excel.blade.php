<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Empresa / Persona</th>
            <th>Concepto / Descripción</th>
            <th>Documento</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($operaciones as $op)
            <tr>
                <td>{{ \Carbon\Carbon::parse($op->fecha_registro)->format('d/m/Y') }}</td>
                <td>{{ isset($op->_es_activo) ? 'Adelantos personal' : $op->tipo->nombre }}</td>
                <td>{{ $op->empresa_persona ?? $op->nombre }}</td>
                <td>{{ isset($op->_es_activo) ? ($op->observaciones ?? 'Adelanto desde caja') : $op->nombre }}</td>
                <td>{{ $op->documento }}</td>
                <td>{{ number_format($op->monto, 2) }}</td>
                <td>{{ $op->metodo_pago ?? '-' }}</td>
                <td>{{ number_format($op->monto_pagado ?? ($op->is_settled ? $op->monto : 0), 2) }}</td>
                <td>{{ number_format($op->saldo, 2) }}</td>
                <td>{{ strtoupper($op->estado) }}</td>
                <td>{{ isset($op->_es_activo) ? '' : ($op->observaciones ?? '') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
