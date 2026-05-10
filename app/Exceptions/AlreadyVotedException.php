<?php

namespace App\Exceptions;

use Exception;

class AlreadyVotedException extends Exception
{
    protected $message = 'Anda sudah memberikan suara pada pemilihan ini.';
}
