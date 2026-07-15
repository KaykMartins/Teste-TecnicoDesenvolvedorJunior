<?php

namespace App\Exceptions;

use Exception;

/**
 * Lançada quando a API do ViaCEP não responde de forma utilizável
 * (timeout, erro de conexão, ou status HTTP de erro) — nunca para
 * "CEP não encontrado", que é um resultado válido, não uma falha.
 */
class ViaCepIndisponivelException extends Exception
{
    //
}
