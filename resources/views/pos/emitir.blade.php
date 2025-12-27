@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pos.css') }}">

<div style="max-width:1000px;margin:20px auto;border:1px solid #ddd;padding:16px;background:#fff;">
    <h3 style="text-align:center;color:#b33;">Comprobante de venta</h3>

    <div style="display:flex;gap:20px;">
        <div style="flex:1;border-right:1px solid #eee;padding-right:12px;">
            <div style="font-size:18px;font-weight:600;margin-bottom:8px;">TOTAL <span style="float:right;font-size:22px;">S/ <span id="total-amount">0.00</span></span></div>

            <div style="margin:12px 0;">
                <label>ENTREGA</label>
                <input id="entrega" type="number" step="0.01" value="0.00" style="width:100%;padding:8px;margin-top:6px;border:1px solid #bcd;">
            </div>

            <div style="margin:12px 0;">
                <label>CAMBIO</label>
                <div style="color:#b33;margin-top:6px;font-weight:700;">S/ <span id="cambio">0.00</span></div>
            </div>

            <div style="margin:12px 0;">
                <label>PAGO</label>
                <select id="medio-pago" style="width:100%;padding:8px;margin-top:6px;">
                    <option value="efectivo">EFECTIVO</option>
                    <option value="tarjeta">TARJETA</option>
                    <option value="mult">Multipagos</option>
                </select>
            </div>

            <div style="margin-top:18px;display:flex;gap:8px;">
                <button onclick="accept()" style="background:#6b2e51;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Aceptar</button>
                <button onclick="cancel()" style="background:#0b8a7e;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Cancelar</button>
            </div>
        </div>

        <div style="flex:1;padding-left:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:12px;color:#666;">Fecha Emisión</div>
                    <div>{{ now()->format('d/m/Y H:i') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;color:#666;">Vendedor</div>
                    <div>{{ $user->name ?? '-' }}</div>
                </div>
            </div>

            <div style="margin-top:12px;">
                <label>Observaciones</label>
                <textarea style="width:100%;min-height:80px;border:1px solid #bcd;padding:8px;"></textarea>
            </div>

            <div style="margin-top:12px;display:flex;gap:8px;">
                <input type="text" placeholder="Guía Remisión Rem." style="flex:1;padding:8px;border:1px solid #bcd;">
                <input type="text" placeholder="Guía Remisión Trans." style="flex:1;padding:8px;border:1px solid #bcd;">
            </div>

            <div style="margin-top:12px;display:flex;gap:8px;align-items:center;">
                <div style="width:60px;">Serie</div>
                <input type="text" value="T001" style="width:80px;padding:8px;border:1px solid #bcd;">
                <input type="text" value="0001" style="flex:1;padding:8px;border:1px solid #bcd;">
            </div>

        </div>
    </div>
</div>

<script>
function accept(){
    // placeholder: submit sale
    alert('Emitir (simulado)');
}
function cancel(){
    window.location = '{{ route('pos.index') }}';
}
</script>

@endsection