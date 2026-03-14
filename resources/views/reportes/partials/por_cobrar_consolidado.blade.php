<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - POR COBRAR (CONSOLIDADO)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th class="text-center">Cant. Documentos</th>
                <th>Vencimiento Más Antiguo</th>
                <th class="text-end text-danger">Total Deuda</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalGeneral = 0; 
                $isExcel = !empty($is_excel);
            @endphp
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td>{{ $row->cliente->numero_documento ?? '-' }}</td>
                    <td class="text-center">{{ $row->total_documentos }}</td>
                    <td class="{{ $row->vencimiento_mas_antiguo < now() ? 'text-danger fw-bold' : '' }}">
                        {{ \Carbon\Carbon::parse($row->vencimiento_mas_antiguo)->format('d/m/Y') }}
                    </td>
                    <td class="text-end fw-bold text-danger">{{ moneda($row->total_deuda, $isExcel, !$isExcel) }}</td>
                </tr>
                @php $totalGeneral += $row->total_deuda; @endphp
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No hay deudas
                            pendientes</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-light">
            <tr>
                <td colspan="5" class="text-end fw-bold">TOTAL GENERAL POR COBRAR:</td>
                <td class="text-end fw-bold text-danger fs-5">{{ moneda($totalGeneral, $isExcel, !$isExcel) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
