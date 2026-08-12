<?php

namespace App\Exceptions;

use Exception;

/**
 * Se lanza cuando no se logra tomar el lock del correlativo de factura,
 * es decir cuando otra caja esta facturando en ese mismo instante.
 */
class ReservaFacturaException extends Exception
{
}
