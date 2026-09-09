<?php
declare(strict_types=1);

require_once __DIR__ . '/TicketDataBase.php';

/** Bandeja general de administración de tickets. */
final class TicketAdministrador extends TicketDataBase
{
    /**
     * @param int|null $estado Estado opcional.
     * @param int|null $idUsuario Solicitante opcional.
     * @param int[]|null $colegioIds Colegios autorizados; null concede alcance global.
     * @param int|null $idColegio Colegio seleccionado.
     * @param int|null $idTecnico Técnico seleccionado.
     * @return array<int,array<string,mixed>>
     * @example $tickets = (new TicketAdministrador($db, $adminId))->traer(5, 10);
     */
    public function traer(
        ?int $estado = null,
        ?int $idUsuario = null,
        ?array $colegioIds = null,
        ?int $idColegio = null,
        ?int $idTecnico = null
    ): array {
        $condiciones = [];
        $parametros = [];

        foreach (['t.id_estado' => $estado, 't.id_usuario' => $idUsuario] as $campo => $valor) {
            if ($valor !== null) {
                if ($valor <= 0) {
                    throw new InvalidArgumentException('El filtro indicado no es válido.');
                }
                $condiciones[] = $campo . ' = ?';
                $parametros[] = $valor;
            }
        }

        if ($colegioIds !== null) {
            $colegioIds = array_values(array_unique(array_filter(array_map('intval', $colegioIds), static fn (int $id): bool => $id > 0)));
            if ($colegioIds === []) {
                $condiciones[] = '1 = 0';
            } else {
                $condiciones[] = 'uc_ticket.id_colegio IN (' . implode(',', array_fill(0, count($colegioIds), '?')) . ')';
                array_push($parametros, ...$colegioIds);
            }
        }

        foreach (['uc_ticket.id_colegio' => $idColegio, 't.id_tecnico' => $idTecnico] as $campo => $valor) {
            if ($valor !== null) {
                if ($valor <= 0) {
                    throw new InvalidArgumentException('El filtro indicado no es válido.');
                }
                $condiciones[] = $campo . ' = ?';
                $parametros[] = $valor;
            }
        }

        return $this->ejecutar(implode(' AND ', $condiciones), $parametros);
    }

    /**
     * @param int $idTecnico Técnico cuyos tickets se consultarán.
     * @return array<int,array<string,mixed>>
     * @example $tickets = (new TicketAdministrador($db, $adminId))->traerPorTecnico(7);
     */
    public function traerPorTecnico(int $idTecnico): array
    {
        if ($idTecnico <= 0) {
            throw new InvalidArgumentException('El técnico no es válido.');
        }

        return $this->ejecutar('t.id_tecnico = ?', [$idTecnico]);
    }
}
