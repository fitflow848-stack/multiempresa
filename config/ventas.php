<?php

return [
    /*
    | Tasas de IGV que se pueden elegir en la pantalla de ventas (en %). La general es 18.
    | Agrega solo tasas que la ley permita a la empresa (por ejemplo, regímenes especiales). Probado en SUNAT beta: 10.5 se acepta y 10 se rechaza (error 3462). Con una tasa
    | que no corresponda, SUNAT puede rechazar el comprobante.
    */
    'tasas_igv' => [18, 10.5],
];
