<?php

namespace App\Exceptions;

use Exception;

class ElectionNotActiveException extends Exception
{
    protected $message = 'Pemilihan belum dimulai atau sudah berakhir.';
}
