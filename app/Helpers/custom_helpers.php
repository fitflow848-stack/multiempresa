<?php

use App\Models\CompanyDocument;
use Carbon\Carbon;
use App\Helpers\CompanyInitials;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('numeroALetras')) {
    function numeroALetras($numero, $moneda = 'Soles')
    {
        $unidad = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decena = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centena = ['', 'CIEN', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        $especiales = [
            11 => 'ONCE',
            12 => 'DOCE',
            13 => 'TRECE',
            14 => 'CATORCE',
            15 => 'QUINCE',
            16 => 'DIECISÉIS',
            17 => 'DIECISIETE',
            18 => 'DIECIOCHO',
            19 => 'DIECINUEVE'
        ];

        $numEntero = intval(floor($numero)); // Parte entera
        $numDecimal = round(($numero - $numEntero) * 100); // Parte decimal (2 decimales)

        $convertir = function ($n) use (&$convertir, $unidad, $decena, $centena, $especiales) {
            if ($n == 0) return 'CERO';
            if ($n < 10) return $unidad[$n];
            if ($n < 20) {
                // manejar 10 y 11-19 correctamente
                if ($n == 10) return 'DIEZ';
                if (isset($especiales[$n])) return $especiales[$n];
            }
            if ($n < 100) {
                $d = intval($n / 10);
                $r = $n % 10;
                $texto = $decena[$d];
                if ($r > 0) $texto .= ' Y ' . $unidad[$r];
                return $texto;
            }
            if ($n < 1000) {
                if ($n == 100) return 'CIEN';
                $c = intval($n / 100);
                $resto = $n % 100;
                $texto = ($c == 1 && $resto > 0) ? 'CIENTO' : $centena[$c];
                if ($resto > 0) $texto .= ' ' . $convertir($resto);
                return $texto;
            }
            if ($n < 1000000) {
                $m = intval($n / 1000);
                $resto = $n % 1000;
                if ($m == 1) {
                    $texto = 'MIL';
                } else {
                    $texto = $convertir($m) . ' MIL';
                }
                if ($resto > 0) $texto .= ' ' . $convertir($resto);
                return $texto;
            }
            if ($n < 1000000000000) {
                $millones = intval($n / 1000000);
                $resto = $n % 1000000;
                if ($millones == 1) {
                    $texto = 'UN MILLÓN';
                } else {
                    $texto = $convertir($millones) . ' MILLONES';
                }
                if ($resto > 0) $texto .= ' ' . $convertir($resto);
                return $texto;
            }
            // fuera de rango manejable
            return (string)$n;
        };

        $enteroEnLetras = $convertir($numEntero);
        $decimalesEnLetras = sprintf('%02d', $numDecimal);

        return $enteroEnLetras . " CON " . $decimalesEnLetras . "/100 " . strtoupper($moneda);
    }
}

if (!function_exists('agregarCerosIzquierda')) {
    /**
     * Agregar ceros a la izquierda de un número.
     *
     * @param int|string $numero Número a completar con ceros.
     * @param int $cantidadDigitos Número total de dígitos que se desea.
     * @return string Número con ceros a la izquierda.
     */
    function agregarCerosIzquierda($numero, $cantidadDigitos = 8)
    {
        return str_pad($numero, $cantidadDigitos, '0', STR_PAD_LEFT);
    }
}


if (!function_exists('formatString')) {
    function formatString($string)
    {
        $replace = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ];

        return strtr($string, $replace);
    }
}

if (!function_exists('fechaFormateada')) {
    function fechaFormateada()
    {
        $meses = [
            '01' => 'ENE',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'ABR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGO',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DIC'
        ];

        $dia = date('d');
        $mes = $meses[date('m')];
        $anio = date('Y');

        return $dia . $mes . $anio;
    }
}

if (!function_exists('normalizarTipoResiduoNew')) {
    function normalizarTipoResiduoNew($tipo)
    {
        $tipo = strtolower($tipo); // minúsculas
        $tipo = iconv('UTF-8', 'ASCII//TRANSLIT', $tipo); // quita tildes
        $tipo = preg_replace('/[^a-z0-9]/', '', $tipo); // quita espacios y símbolos
        return $tipo;
    }
}

if (!function_exists('tryParseDate')) {
    /**
     * Intenta parsear una cadena de fecha con varios formatos conocidos.
     * Devuelve Carbon instance o null si no se pudo parsear.
     */
    function tryParseDate(string $str)
    {
        $str = trim($str);
        if ($str === '') {
            return null;
        }

        // Formatos que esperamos recibir (ajusta si necesitas más)
        $formats = [
            'Y-m-d',    // 2025-10-21
            'd/m/Y',    // 21/10/2025
            'd-m-Y',    // 21-10-2025
            'd.m.Y',    // 21.10.2025
            'Y/m/d',    // 2025/10/21
        ];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $str);
                // Verificar que el parseo fue exacto: al formatearlo vuelva al mismo string (evita "trailing data")
                if ($d && $d->format($fmt) === $str) {
                    return $d;
                }
            } catch (\Exception $e) {
                // ignorar y probar siguiente formato
            }
        }

        // Fallback: dejar que Carbon intente parseo flexible
        try {
            return Carbon::parse($str);
        } catch (\Exception $e) {
            return null;
        }
    }
}


if (!function_exists('renderCheckboxPeligrosidad')) {
    /**
     * Genera el HTML para un checkbox de peligrosidad con diseño similar a la imagen
     * 
     * @param string $peligrosidadActual La peligrosidad actual del residuo
     * @param string $tipoPeligrosidad El tipo de peligrosidad a comparar
     * @param string $etiqueta La etiqueta a mostrar
     * @return string HTML del checkbox con la etiqueta
     */
    function renderCheckboxPeligrosidad($peligrosidadActual, $tipoPeligrosidad, $etiqueta)
    {
        $isChecked = $peligrosidadActual === $tipoPeligrosidad;
        $checkboxContent = $isChecked ? 'X' : '';

        return '<tr>
                    <td style="padding: 3px 6px; font-size: 8px; border: 1px solid #000;">' . $etiqueta . '</td>
                    <td style="width: 20px; text-align: center; border: 1px solid #000; background: #4472C4;">
                        <span style="color: white; font-weight: bold; font-size: 10px;">' . $checkboxContent . '</span>
                    </td>
                </tr>';
    }
}

if (!function_exists('renderPeligrosidadColumn')) {
    /**
     * Genera una columna completa de peligrosidades con tabla interna
     * 
     * @param string $peligrosidadActual La peligrosidad actual del residuo
     * @param array $peligrosidades Array de peligrosidades con sus etiquetas
     * @param string $width Ancho de la columna (ej: '25%')
     * @param int $colspan Colspan si es necesario
     * @return string HTML de la columna TD completa
     */
    function renderPeligrosidadColumn($peligrosidadActual, $peligrosidades, $width = '25%', $colspan = null)
    {
        $colspanAttr = $colspan ? ' colspan="' . $colspan . '"' : '';
        $html = '<td style="width: ' . $width . '; padding: 0; vertical-align: top;"' . $colspanAttr . '>
                    <table style="width: 100%; border-collapse: collapse; margin: 0;">';

        foreach ($peligrosidades as $tipo => $etiqueta) {
            $html .= renderCheckboxPeligrosidad($peligrosidadActual, $tipo, $etiqueta);
        }

        $html .= '</table></td>';
        return $html;
    }
}

if (! function_exists('company_initials')) {
    /**
     * Helper global para obtener iniciales de una empresa.
     *
     * Ejemplo:
     *   company_initials('PETRAMAS S.A.C.') => 'PSAC'
     *   company_initials('PETRAMAS S.A.C.', ['ignoreCompanySuffixes' => true]) => 'P'
     *   company_initials('COMPAÑÍA INDUSTRIAL LIMA S.A. (CILSA)') => 'CILSA'
     *
     * @param string $name
     * @param array $options
     * @return string
     */
    function company_initials(string $name, array $options = []): string
    {
        return CompanyInitials::get($name, $options);
    }
}

/**
 * Intenta parsear una fecha usando una lista de formatos comunes.
 * Si ninguno encaja, intenta Carbon::parse como último recurso.
 *
 * @param string $input
 * @return Carbon
 * @throws Exception
 */
function parseFlexibleDate(string $input): Carbon
{
    $input = trim($input);
    if ($input === '') {
        throw new \InvalidArgumentException('Fecha vacía');
    }

    $formats = [
        'd/m/Y',
        'd-m-Y',
        'Y-m-d',
        'Y/m/d',
        'd M Y',   // 21 Oct 2025
        'd F Y',   // 21 October 2025
        'm/d/Y',
        'm-d-Y',
    ];

    foreach ($formats as $fmt) {
        $dt = Carbon::createFromFormat($fmt, $input);
        // createFromFormat devuelve instancia aún si hay errores de parseo; comprobar errores
        $errors = Carbon::getLastErrors();
        if ($dt !== false && $errors['warning_count'] === 0 && $errors['error_count'] === 0) {
            return $dt;
        }
    }

    // Último recurso: intentar parseo flexible (acepta textos tipo "2025-10-21", "October 21 2025", etc.)
    try {
        return Carbon::parse($input);
    } catch (\Exception $e) {
        throw new \Exception("No se pudo parsear la fecha: {$input}");
    }
}
if (! function_exists('get_initial_estado')) {
    /**
     * Alias de company_initials para compatibilidad.
     *
     * @param string $name
     * @param array $options
     * @return string
     */
    function get_initial_estado(string $name): string
    {
        switch (strtoupper(trim($name))) {
            case 'BUEN ESTADO':
                return 'B';
            case 'MAL ESTADO':
                return 'M';
            case 'REGULAR':
                return 'R';
            case 'NO APLICA':
                return 'NA';
            case 'NO TIENE':
                return 'N';
            default:
                return '-';
        }
    }
}

if (! function_exists('get_descripcion_plan')) {

    function get_descripcion_plan(string $name, $planes)
    {
        foreach ($planes as $plan) {
            if ($plan->nombre === $name) {
                return $plan->descripcion;
            }
        }
    }
}

if (! function_exists('get_fecha_formateada')) {

    function get_fecha_formateada($daterange)
    {
        $raw = trim($daterange);
        // Separar por "to", "-", "–", "—"
        $parts = preg_split('/\s*(?:-|–|—|to)\s*/i', $raw);

        if (count($parts) >= 2) {
            try {
                $fechaInicio = \Carbon\Carbon::createFromFormat('d/m/Y', trim($parts[0]))->format('Y-m-d');
                $fechaFin = \Carbon\Carbon::createFromFormat('d/m/Y', trim($parts[1]))->format('Y-m-d');
            } catch (\Exception $e) {
                // Si falla el formato d/m/Y, intentar otros formatos
                try {
                    $fechaInicio = \Carbon\Carbon::parse(trim($parts[0]))->format('Y-m-d');
                    $fechaFin = \Carbon\Carbon::parse(trim($parts[1]))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Si no se puede parsear, ignorar el filtro de fecha
                }
            }
        }
        return [$fechaInicio ?? null, $fechaFin ?? null];
    }
}

if(! function_exists('obtenerSerieDocumento')) {
    /**
     * Obtener la serie correspondiente según el tipo de documento
     */
    function obtenerSerieDocumento($company, $tipoDocumento)
    {
        $documento = DB::table('documentos_sunat')
            ->where('nombre', 'like', '%' . $tipoDocumento . '%')
            ->first();

        if (!$documento) {
            return null;
        }

        $document = CompanyDocument::where('company_id', $company->id)
            ->where('branch_id', Auth::user()->branch_id)
            ->where('sunat_document_id', $documento->id_tido)
            ->first();

        return $document ? $document->series : null;
    }
}
