<?php

namespace App\Services\Pix;

use RuntimeException;

/**
 * Falha de negócio no PIX (documento inválido, recusa do Asaas, etc.).
 * Deve ser exibida ao usuário; não é um 500.
 */
class PixException extends RuntimeException
{
}
