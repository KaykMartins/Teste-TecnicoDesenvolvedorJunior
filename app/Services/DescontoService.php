<?php

namespace App\Services;

use InvalidArgumentException;

class DescontoService
{
    /**
     * Aplica um desconto percentual sobre um valor.
     *
     * @throws InvalidArgumentException se $valor ou $desconto forem nulos, não numéricos,
     *                                   se $valor for negativo, ou se $desconto estiver fora de 0-100.
     */
    public function calcularDesconto(mixed $valor, mixed $desconto): float
    {
        if ($valor === null) {
            throw new InvalidArgumentException('O valor não pode ser nulo.');
        }

        if ($desconto === null) {
            throw new InvalidArgumentException('O desconto não pode ser nulo.');
        }

        if (! is_numeric($valor)) {
            throw new InvalidArgumentException('O valor deve ser numérico.');
        }

        if (! is_numeric($desconto)) {
            throw new InvalidArgumentException('O desconto deve ser numérico.');
        }

        $valor = (float) $valor;
        $desconto = (float) $desconto;

        if ($valor < 0) {
            throw new InvalidArgumentException('O valor não pode ser negativo.');
        }

        if ($desconto < 0 || $desconto > 100) {
            throw new InvalidArgumentException('O desconto deve estar entre 0 e 100.');
        }

        return $valor - $valor * $desconto / 100;
    }
}
