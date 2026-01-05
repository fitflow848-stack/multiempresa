<?php

namespace App\Helpers;

/**
 * Helper para extraer iniciales/siglas de nombres de empresas.
 *
 * Comportamiento:
 * - Si hay una sigla entre paréntesis (ej. "(CILSA)"), se devuelve esa sigla (sin espacios ni puntos).
 * - Si no, toma la primera letra de cada token (por defecto ignora stop-words como "de", "la", "y").
 * - Opciones para ignorar sufijos legales (S.A., S.A.C., S.R.L., etc.).
 */
class CompanyInitials
{
    protected static array $stopWords = [
        'DE', 'DEL', 'LA', 'LAS', 'LOS', 'Y', 'E', 'EN', 'PARA', 'POR', 'EL'
    ];

    protected static array $companySuffixes = [
        'SA', 'S.A', 'S.A.', 'SAC', 'S.A.C', 'S.A.C.', 'SRL', 'S.R.L', 'S.R.L.', 'EIRL',
        'SL', 'S.L', 'S.L.', 'LTDA', 'LTD', 'INC', 'COMPANY', 'CO', 'SAS'
    ];

    protected static function normalize(string $s): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $s));

        // Quitar acentos si está disponible la extensión intl
        if (extension_loaded('intl') && class_exists(\Normalizer::class)) {
            $s = \Normalizer::normalize($s, \Normalizer::FORM_D);
            $s = preg_replace('/\p{M}/u', '', $s);
        } else {
            // fallback (puede perder caracteres no ASCII)
            $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($trans !== false) $s = $trans;
        }

        return $s;
    }

    protected static function insideParenthesisAcronym(string $name): ?string
    {
        if (!preg_match('/\(([^)]+)\)/u', $name, $m)) {
            return null;
        }

        $inner = trim($m[1]);
        $innerNorm = self::normalize($inner);
        $upper = mb_strtoupper($innerNorm);

        // Si el contenido parece una sigla (mayúsculas, números, &, -, espacios o puntos)
        if (preg_match('/^[A-Z0-9&\-\s\.]+$/u', $upper)) {
            return preg_replace('/[\s\.]+/u', '', $upper);
        }

        return null;
    }

    /**
     * Devuelve las iniciales/ sigla.
     *
     * @param string $name Nombre de la empresa
     * @param array $options [
     *     'ignoreStopWords' => bool (default true),
     *     'ignoreCompanySuffixes' => bool (default false)
     * ]
     * @return string
     */
    public static function get(string $name, array $options = []): string
    {
        if ($name === '') return '';

        $ignoreStopWords = $options['ignoreStopWords'] ?? true;
        $ignoreCompanySuffixes = $options['ignoreCompanySuffixes'] ?? false;

        // 1) Si hay sigla entre paréntesis
        $paren = self::insideParenthesisAcronym($name);
        if ($paren) return $paren;

        // 2) Normalizar
        $norm = self::normalize($name);

        // 3) Extraer tokens (letras y números)
        preg_match_all('/[\p{L}\p{N}]+/u', $norm, $matches);
        $tokens = $matches[0] ?? [];

        $initials = [];
        foreach ($tokens as $token) {
            $up = mb_strtoupper(str_replace('.', '', $token));

            if ($ignoreStopWords && in_array($up, self::$stopWords, true)) {
                continue;
            }
            if ($ignoreCompanySuffixes && in_array($up, self::$companySuffixes, true)) {
                continue;
            }

            $first = mb_substr($up, 0, 1);
            if (preg_match('/[A-Z0-9]/u', $first)) {
                $initials[] = $first;
            }
        }

        // Fallback si no se obtuvo nada
        if (empty($initials) && mb_strlen($norm) > 0) {
            if (preg_match('/[A-Za-z0-9]/u', $norm, $m)) {
                return mb_strtoupper($m[0]);
            }
            return '';
        }

        return implode('', $initials);
    }
}