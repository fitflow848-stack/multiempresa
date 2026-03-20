<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize()
    {
        // Ajusta según tu lógica de autorización
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'ticket' => 'required|string',
            'cliente' => 'nullable|string',
            'tipo_documento' => 'required|string',
            'tipo_pago_id' => 'required|exists:tipos_pagos,id',
            'total' => 'required|numeric|min:0',
            'entrega' => 'required|numeric|min:0',
            'deuda' => 'nullable|numeric|min:0',
            'genera_deuda' => 'nullable|boolean',
            'serie' => 'required|string',
            'numero' => 'required|string',
            'observaciones' => 'nullable|string',
            'proforma' => 'nullable|integer',
            'plazo_dias' => 'nullable|integer|min:1',
        ];
    }
}