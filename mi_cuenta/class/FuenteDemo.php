<?php
declare(strict_types=1);

require_once __DIR__ . '/FuenteRrhh.php';
require_once __DIR__ . '/PdfRrhh.php';

/**
 * DATOS DE EJEMPLO para QA/local mientras no esté la conexión con Buk.
 * Todo se calcula a partir del id de la persona (siempre da lo mismo) y la
 * pantalla lo muestra con el aviso "Datos de ejemplo". Los PDF llevan la
 * marca "EJEMPLO – SIN VALIDEZ". Nunca se usa en producción (ver Rrhh::fuente()).
 *
 * Los cálculos imitan una liquidación chilena (AFP, salud 7 %, seguro de
 * cesantía, impuesto único por tramos) solo para que la pantalla se vea real.
 */
final class FuenteDemo implements FuenteRrhh
{
    private const UTM = 69000;          // valor aproximado, solo para el ejemplo
    private const TOPE_GRATIFICACION = 213000;
    private const MESES = 12;

    public function tipo(): string { return 'demo'; }
    public function nombre(): string { return 'Datos de ejemplo'; }

    public function liquidaciones(array $persona): array
    {
        return array_map(static function (array $l): array {
            unset($l['haberes'], $l['descuentos_detalle']);
            return $l;
        }, $this->generar($persona));
    }

    public function liquidacion(array $persona, string $id): ?array
    {
        foreach ($this->generar($persona) as $l) {
            if ($l['id'] === $id) {
                return $l;
            }
        }
        return null;
    }

    public function liquidacionPdf(array $persona, string $id): ?array
    {
        $l = $this->liquidacion($persona, $id);
        if (!$l) {
            return null;
        }
        return [
            'nombre' => 'Liquidacion_' . $l['periodo'] . '_EJEMPLO.pdf',
            'mime' => 'application/pdf',
            'contenido' => PdfRrhh::liquidacion($persona, $l, true),
        ];
    }

    public function documentos(array $persona): array
    {
        $ingreso = $this->fechaIngreso($persona);
        $apellido = mb_strtoupper(self::sinTildes(self::apellido($persona['nombre'])));
        $pref = $apellido . '_';
        $anio = (int) date('Y');
        $docs = [
            ['contratos', 'Contrato_de_trabajo_' . $ingreso->format('Y'), $ingreso, 410],
            ['anexos', 'Anexo_reajuste_' . ($anio - 1), new DateTimeImmutable(($anio - 1) . '-03-01'), 180],
            ['anexos', 'Anexo_reajuste_' . $anio, new DateTimeImmutable($anio . '-03-01'), 182],
            ['anexos', 'Anexo_teletrabajo', new DateTimeImmutable(($anio - 1) . '-08-12'), 160],
            ['certificados', 'Certificado_de_antiguedad', new DateTimeImmutable($anio . '-05-07'), 95],
            ['certificados', 'Certificado_de_remuneraciones_' . ($anio - 1), new DateTimeImmutable($anio . '-02-20'), 120],
            ['certificados', 'Certificado_de_cotizaciones_AFP', new DateTimeImmutable($anio . '-06-03'), 140],
            ['personales', 'Recepcion_Reglamento_Interno_RIOHS', $ingreso, 75],
            ['personales', 'Obligacion_de_informar_ODI', $ingreso, 220],
            ['otros', 'Entrega_de_elementos_de_proteccion', new DateTimeImmutable(($anio - 1) . '-04-15'), 60],
        ];
        $lista = [];
        foreach ($docs as $i => [$cat, $nombre, $fecha, $kb]) {
            $lista[] = [
                'id' => 'demo-doc-' . ($i + 1),
                'nombre' => $pref . $nombre . '.pdf',
                'categoria' => $cat,
                'extension' => 'pdf',
                'tamano' => $kb * 1024,
                'fecha' => $fecha->format('Y-m-d'),
            ];
        }
        usort($lista, static fn (array $a, array $b): int => strcmp($b['fecha'], $a['fecha']));
        return $lista;
    }

    public function documentoArchivo(array $persona, string $id): ?array
    {
        foreach ($this->documentos($persona) as $d) {
            if ($d['id'] === $id) {
                return [
                    'nombre' => $d['nombre'],
                    'mime' => 'application/pdf',
                    'contenido' => PdfRrhh::documentoEjemplo($persona, $d),
                ];
            }
        }
        return null;
    }

    // ------------------------------------------------------------ cálculo

    private function generar(array $persona): array
    {
        $id = (int) $persona['id'];
        mt_srand($id * 7919 + 17);
        $base = (int) (round(mt_rand(950, 2100) / 5) * 5000);          // sueldo base
        $ultimo = (new DateTimeImmutable('first day of last month', new DateTimeZone('America/Santiago')))->setTime(0, 0);
        $lista = [];
        for ($i = 0; $i < self::MESES; $i++) {
            $mes = $ultimo->modify("-$i month");
            $m = (int) $mes->format('n');
            // Reajuste en marzo: antes de marzo del año actual el sueldo era ~4 % menor
            $sueldo = $mes < new DateTimeImmutable($ultimo->format('Y') . '-03-01') ? (int) round($base / 1.04 / 1000) * 1000 : $base;
            $haberes = [['Sueldo base', $sueldo, true]];
            $haberes[] = ['Gratificación legal', min((int) round($sueldo * 0.25), self::TOPE_GRATIFICACION), true];
            $horas = mt_rand(0, 9) < 3 ? mt_rand(2, 10) : 0;
            if ($horas) {
                $haberes[] = ['Horas extra (' . $horas . ' h)', (int) round($sueldo / 30 * 7 / 44 * 1.5 * $horas), true];
            }
            if ($m === 9) {
                $haberes[] = ['Aguinaldo Fiestas Patrias', 60000, false];
            }
            if ($m === 12) {
                $haberes[] = ['Aguinaldo de Navidad', 70000, false];
            }
            $haberes[] = ['Asignación de movilización', 45000, false];
            $haberes[] = ['Asignación de colación', 55000, false];

            $imponible = array_sum(array_map(static fn ($h) => $h[2] ? $h[1] : 0, $haberes));
            $bruto = array_sum(array_column($haberes, 1));
            $afp = (int) round($imponible * 0.1127);
            $salud = (int) round($imponible * 0.07);
            $afc = (int) round($imponible * 0.006);
            $impuesto = $this->impuesto($imponible - $afp - $salud - $afc);
            $desc = [['AFP (10 % + comisión)', $afp], ['Salud 7 %', $salud], ['Seguro de cesantía 0,6 %', $afc]];
            if ($impuesto > 0) {
                $desc[] = ['Impuesto único', $impuesto];
            }
            if (mt_rand(0, 9) < 2) {
                $desc[] = ['Anticipo de sueldo', 100000];
            }
            $totalDesc = array_sum(array_column($desc, 1));
            $lista[] = [
                'id' => 'demo-' . $mes->format('Y-m'),
                'periodo' => $mes->format('Y-m'),
                'bruto' => $bruto,
                'descuentos' => $totalDesc,
                'liquido' => $bruto - $totalDesc,
                'aporte_patronal' => (int) round($imponible * 0.0582),   // SIS + AFC + mutual + aporte empleador
                'dias_trabajados' => 30,
                'horas_extra' => $horas,
                'tiene_pdf' => true,
                'haberes' => $haberes,
                'descuentos_detalle' => $desc,
            ];
        }
        mt_srand();
        return $lista;
    }

    /** Impuesto único de segunda categoría (tramos en UTM, valores aproximados). */
    private function impuesto(int $base): int
    {
        $tramos = [[13.5, 0, 0], [30, 0.04, 0.54], [50, 0.08, 1.74], [70, 0.135, 4.49], [90, 0.23, 11.14], [120, 0.304, 17.80]];
        $utm = $base / self::UTM;
        foreach ($tramos as [$hasta, $factor, $rebaja]) {
            if ($utm <= $hasta) {
                return max(0, (int) round(($utm * $factor - $rebaja) * self::UTM));
            }
        }
        return (int) round(($utm * 0.35 - 23.92) * self::UTM);
    }

    private function fechaIngreso(array $persona): DateTimeImmutable
    {
        mt_srand((int) $persona['id'] * 104729);
        $f = new DateTimeImmutable(sprintf('%d-%02d-%02d', mt_rand(2015, 2023), mt_rand(1, 12), mt_rand(1, 28)));
        mt_srand();
        return $f;
    }

    private static function apellido(string $nombre): string
    {
        $p = preg_split('/\s+/u', trim($nombre)) ?: [];
        return $p[1] ?? ($p[0] ?? 'PERSONA');
    }

    public static function sinTildes(string $t): string
    {
        return strtr($t, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Ü' => 'U', 'ü' => 'u']);
    }
}
