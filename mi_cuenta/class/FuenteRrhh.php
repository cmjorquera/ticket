<?php
declare(strict_types=1);

/**
 * Origen de las liquidaciones y documentos de una persona.
 *
 * Hoy hay tres implementaciones (ver Rrhh::fuente()):
 *   - FuenteBuk      → lee la API de Buk (cuando 'buk' está en config_privada)
 *   - FuenteDemo     → datos de EJEMPLO para QA/local (se muestran marcados)
 *   - FuenteNinguna  → producción sin Buk: listas vacías y aviso
 *
 * $persona es lo que arma MiCuenta::persona():
 *   ['id', 'nombre', 'email', 'cargo', 'area', 'rut' (puede ser null), 'id_buk' (puede ser null)]
 *
 * Liquidación (lista):
 *   ['id', 'periodo' => 'AAAA-MM', 'bruto', 'descuentos', 'liquido', 'aporte_patronal',
 *    'dias_trabajados', 'horas_extra', 'tiene_pdf' => bool]
 * Liquidación (detalle): lo anterior + 'haberes' => [[concepto, monto, imponible bool]],
 *    'descuentos_detalle' => [[concepto, monto]]
 * Documento:
 *   ['id', 'nombre', 'categoria' (contratos|anexos|certificados|personales|otros),
 *    'extension', 'tamano' (bytes), 'fecha' 'AAAA-MM-DD']
 * Archivo: ['nombre', 'mime', 'contenido'] (bytes)
 */
interface FuenteRrhh
{
    /** 'buk' | 'demo' | 'ninguna' */
    public function tipo(): string;

    /** Nombre para mostrar, ej: "Buk" o "Datos de ejemplo". */
    public function nombre(): string;

    /** @return list<array> liquidaciones, de la más reciente a la más antigua */
    public function liquidaciones(array $persona): array;

    public function liquidacion(array $persona, string $id): ?array;

    public function liquidacionPdf(array $persona, string $id): ?array;

    /** @return list<array> */
    public function documentos(array $persona): array;

    public function documentoArchivo(array $persona, string $id): ?array;
}

/** Producción sin Buk configurado: no inventa nada. */
final class FuenteNinguna implements FuenteRrhh
{
    public function tipo(): string { return 'ninguna'; }
    public function nombre(): string { return 'Sin conexión'; }
    public function liquidaciones(array $persona): array { return []; }
    public function liquidacion(array $persona, string $id): ?array { return null; }
    public function liquidacionPdf(array $persona, string $id): ?array { return null; }
    public function documentos(array $persona): array { return []; }
    public function documentoArchivo(array $persona, string $id): ?array { return null; }
}
