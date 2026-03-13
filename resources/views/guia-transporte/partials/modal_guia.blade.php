@php
    $departamentos = \App\Models\Departamento::all();
@endphp

<style>
    .modal-novik .modal-content {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }
    .modal-novik .modal-header {
        background-color: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1rem 1.5rem;
    }
    .modal-novik .modal-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.1rem;
    }
    .section-label-modal {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .novik-control-modal {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .novik-control-modal:focus {
        border-color: #2ecc71;
        background: #fff;
        outline: none;
    }
    .novik-btn-create {
        background: #2ecc71;
        color: #fff;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
    }
    .novik-btn-cancel {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
    }
    .label-mini {
        font-size: 0.65rem;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 0.2rem;
        display: block;
    }
</style>

<div class="modal fade modal-novik" id="modalGenerarGuia" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header">
                <h5 class="modal-title">Guía de Remisión - <span id="guia_doc_ref" class="text-success"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModalGuia">
                @csrf
                <input type="hidden" name="documento_relacionado" id="guia_venta_numero">
                <input type="hidden" name="venta_id" id="guia_venta_id">
                
                <div class="modal-body p-4 pt-2">
                    <!-- Row 1: Modalidad, Emision, Peso, Traslado -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="label-mini">Modalidad</label>
                            <select class="novik-control-modal" name="modalidad_traslado_codigo" id="modal_modalidad">
                                <option value="01">Pública</option>
                                <option value="02">Privada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="label-mini">F. Traslado</label>
                            <input type="date" class="novik-control-modal" name="fecha_traslado" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="label-mini">Peso Total (KG)</label>
                            <input type="number" step="0.01" class="novik-control-modal fw-bold" name="peso_bruto" id="guia_peso_total" value="1.00">
                        </div>
                        <div class="col-md-3">
                            <label class="label-mini">Motivo</label>
                            <select class="novik-control-modal" name="motivo_traslado_codigo">
                                <option value="01">Venta</option>
                                <option value="04">Traslado entre est.</option>
                            </select>
                        </div>
                    </div>

                    <!-- Transportista / Conductor -->
                    <div id="modal_section_publico">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="label-mini">RUC Transportista</label>
                                <input type="text" class="novik-control-modal" name="transportista_doc" id="guia_trans_ruc" placeholder="Buscar RUC...">
                            </div>
                            <div class="col-md-8">
                                <label class="label-mini">Razón Social</label>
                                <input type="text" class="novik-control-modal" name="transportista_nombre" id="guia_trans_nombre" placeholder="Empresa de transporte">
                            </div>
                        </div>
                    </div>

                    <div id="modal_section_privado" style="display:none">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="label-mini">Placa</label>
                                <input type="text" class="novik-control-modal" name="vehiculo_placa" placeholder="ABC-123">
                            </div>
                            <div class="col-md-3">
                                <label class="label-mini">DNI Cond.</label>
                                <input type="text" class="novik-control-modal" name="conductor_doc_numero" placeholder="DNI">
                            </div>
                            <div class="col-md-3">
                                <label class="label-mini">Licencia</label>
                                <input type="text" class="novik-control-modal" name="conductor_licencia" placeholder="Licencia">
                            </div>
                            <div class="col-md-3">
                                <label class="label-mini">Nombre</label>
                                <input type="text" class="novik-control-modal" name="conductor_nombre" placeholder="Nombre completo">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <span class="section-label-modal"><i class="bx bx-map-pin"></i> Punto de Partida</span>
                            <div class="row g-1 mb-2">
                                <div class="col-4"><input type="text" class="novik-control-modal" value="LIMA" readonly title="Dep"></div>
                                <div class="col-4"><input type="text" class="novik-control-modal" value="LIMA" readonly title="Prov"></div>
                                <div class="col-4"><input type="text" class="novik-control-modal" value="LIMA" readonly title="Dist"></div>
                            </div>
                            <input type="text" class="novik-control-modal" name="direccion_partida" id="guia_dir_partida" placeholder="Dirección partida">
                        </div>
                        <div class="col-md-6">
                            <span class="section-label-modal"><i class="bx bxs-map-pin"></i> Punto de Destino</span>
                            <div class="row g-1 mb-2">
                                <div class="col-4"><input type="text" class="novik-control-modal" name="dep_llegada_text" id="modal_dep_llegada" readonly title="Dep"></div>
                                <div class="col-4"><input type="text" class="novik-control-modal" name="prov_llegada_text" id="modal_prov_llegada" readonly title="Prov"></div>
                                <div class="col-4"><input type="text" class="novik-control-modal" name="dist_llegada_text" id="modal_dist_llegada" readonly title="Dist"></div>
                                {{-- Hidden fields for IDs/Codes --}}
                                <input type="hidden" name="departamento_llegada" id="hid_dep_lle">
                                <input type="hidden" name="provincia_llegada" id="hid_prov_lle">
                                <input type="hidden" name="distrito_llegada" id="hid_dist_lle">
                            </div>
                            <input type="text" class="novik-control-modal" name="direccion_llegada" id="guia_dir_llegada" placeholder="Dirección destino">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="novik-btn-cancel" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="novik-btn-create" id="btn_submit_guia">
                        EMITIR GUÍA ELECTRÓNICA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


