<?php
declare(strict_types=1);

require_once __DIR__ . '/TicketDataBase.php';

/** Bandeja de tickets creados por el usuario autenticado. */
final class TicketUsuario extends TicketDataBase
{
    /**
     * @param int|null $estado Estado opcional por el que se filtrará.
     * @return array<int,array<string,mixed>>
     * @example $tickets = (new TicketUsuario($db, $usuarioId))->traer(5);
     */
    public function traer(?int $estado = null): array
    {
        $condiciones = ['t.id_usuario = ?'];
        $parametros = [$this->idUsuarioSession];
        if ($estado !== null) {
            if ($estado <= 0) {
                throw new InvalidArgumentException('El estado no es válido.');
            }
            $condiciones[] = 't.id_estado = ?';
            $parametros[] = $estado;
        }

        return $this->ejecutar(implode(' AND ', $condiciones), $parametros);
    }
}
