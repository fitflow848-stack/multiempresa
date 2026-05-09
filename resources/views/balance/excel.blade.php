<table>
    <thead>
        <tr>
            <th colspan="2" style="font-size: 16pt; font-weight: bold;">BALANCE GENERAL</th>
        </tr>
        <tr>
            <th colspan="2">A la fecha: {{ $fecha }}</th>
        </tr>
    </thead>
    <tbody>
        <tr><td></td><td></td></tr>
        <tr>
            <th style="background-color: #28a745; color: #ffffff; font-weight: bold;">ACTIVO</th>
            <th style="background-color: #28a745; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($total_activo, 2) }}</th>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; font-weight: bold;">Activo Corriente</th>
            <th style="background-color: #f8f9fa; font-weight: bold; text-align: right;">S/ {{ number_format($total_activo_corriente, 2) }}</th>
        </tr>
        <tr>
            <td>  Caja y Efectivo</td>
            <td style="text-align: right;">S/ {{ number_format($caja, 2) }}</td>
        </tr>
        <tr>
            <td>  Bancos</td>
            <td style="text-align: right;">S/ {{ number_format($bancos ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>  Inventario</td>
            <td style="text-align: right;">S/ {{ number_format($inventario, 2) }}</td>
        </tr>
        @foreach ($tiposActivosCorrientes as $tipo)
            <tr>
                <td>  {{ $tipo->nombre }}</td>
                <td style="text-align: right;">S/ {{ number_format($tipo->activos_sum_monto ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <th style="background-color: #f8f9fa; font-weight: bold;">Activo No Corriente</th>
            <th style="background-color: #f8f9fa; font-weight: bold; text-align: right;">S/ {{ number_format($total_activo_no_corriente, 2) }}</th>
        </tr>
        @foreach ($tiposActivosNoCorrientes as $tipo)
            <tr>
                <td>  {{ $tipo->nombre }}</td>
                <td style="text-align: right;">S/ {{ number_format($tipo->activos_sum_monto ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <th style="background-color: #218838; color: #ffffff; font-weight: bold;">TOTAL ACTIVO</th>
            <th style="background-color: #218838; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($total_activo, 2) }}</th>
        </tr>

        <tr><td></td><td></td></tr>

        <tr>
            <th style="background-color: #dc3545; color: #ffffff; font-weight: bold;">PASIVO</th>
            <th style="background-color: #dc3545; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($total_pasivo, 2) }}</th>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; font-weight: bold;">Pasivo Corriente</th>
            <th style="background-color: #f8f9fa; font-weight: bold; text-align: right;">S/ {{ number_format($total_pasivo_corriente, 2) }}</th>
        </tr>
        @foreach ($tiposPasivosCorrientes as $tipo)
            <tr>
                <td>  {{ $tipo->nombre }}</td>
                <td style="text-align: right;">S/ {{ number_format($tipo->pasivos_sum_monto ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <th style="background-color: #f8f9fa; font-weight: bold;">Pasivo No Corriente</th>
            <th style="background-color: #f8f9fa; font-weight: bold; text-align: right;">S/ {{ number_format($total_pasivo_no_corriente, 2) }}</th>
        </tr>
        <tr>
            <td>  Otros pasivos a largo plazo</td>
            <td style="text-align: right;">S/ {{ number_format($otros_pasivos_no_corrientes, 2) }}</td>
        </tr>

        <tr>
            <th style="background-color: #c82333; color: #ffffff; font-weight: bold;">TOTAL PASIVO</th>
            <th style="background-color: #c82333; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($total_pasivo, 2) }}</th>
        </tr>

        <tr><td></td><td></td></tr>

        <tr>
            <th style="background-color: #17a2b8; color: #ffffff; font-weight: bold;">PATRIMONIO</th>
            <th style="background-color: #17a2b8; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($patrimonio_calculado, 2) }}</th>
        </tr>
        <tr>
            <td>  Capital Social / Aportes</td>
            <td style="text-align: right;">S/ {{ number_format($total_aportes, 2) }}</td>
        </tr>
        <tr>
            <td>  Utilidad / Pérdida (Calculada)</td>
            <td style="text-align: right;">S/ {{ number_format($patrimonio_calculado - $total_aportes, 2) }}</td>
        </tr>
        <tr>
            <th style="background-color: #138496; color: #ffffff; font-weight: bold;">TOTAL PATRIMONIO</th>
            <th style="background-color: #138496; color: #ffffff; font-weight: bold; text-align: right;">S/ {{ number_format($patrimonio_calculado, 2) }}</th>
        </tr>

        <tr><td></td><td></td></tr>
        <tr style="background-color: #343a40; color: #ffffff;">
            <th style="font-weight: bold;">TOTAL PASIVO + PATRIMONIO</th>
            <th style="font-weight: bold; text-align: right;">S/ {{ number_format($total_pasivo + $patrimonio_calculado, 2) }}</th>
        </tr>
    </tbody>
</table>
