<?php
declare(strict_types=1);

require_once __DIR__ . '/TicketDataBase.php';

/** Bandeja de tickets asignados al técnico autenticado. */
final class TicketTecnico extends TicketDataBase
{
    /**
     * @param int|null $estado Estado opcional por el que se filtrará.
     * @return array<int,array<string,mixed>>
     * @example $tickets = (new TicketTecnico($db, $tecnicoId))->traer();
     */
    public function traer(?int $estado = null): array
    {
        $condiciones = ['t.id_tecnico = ?'];
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
