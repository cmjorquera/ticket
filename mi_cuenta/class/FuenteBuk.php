<?php
declare(strict_types=1);

require_once __DIR__ . '/FuenteRrhh.php';

/**
 * Liquidaciones y documentos desde la API de Buk.
 *
 * Configuración (config_privada, sección 'buk'):
 *   'url'     => 'https://EMPRESA.buk.cl/api/v1/chile'
 *   'token'   => token de la API (Buk → Configuración → API)
 *   'timeout' => segundos (opcional, 15)
 *   'rutas'   => (opcional) para reemplazar las rutas de RUTAS
 *
 * ⚠ VERIFICAR: las rutas y los nombres de campos de RUTAS / mapear*() siguen
 * la estructura habitual de la API de Buk, pero deben confirmarse con la
 * documentación de la cuenta de SEDUC antes de pasar a producción. Si una
 * ruta cambia, basta con ponerla en 'rutas' dentro de config_privada.
 *
 * Seguridad: la persona se busca por el id de Buk o el RUT guardados en
 * usuario_rrhh (los define un administrador) o, si no hay, por su correo
 * institucional. El navegador nunca envía el RUT ni el id de Buk.
 */
final class FuenteBuk implements FuenteRrhh
{
    private const RUTAS = [
        'empleados'      => '/employees',                         // ?document_number= | ?email=
        'liquidaciones'  => '/employees/{id}/payroll_details',    // lista de liquidaciones
        'liquidacion'    => '/payroll_details/{liq}',             // detalle (conceptos)
        'documentos'     => '/employees/{id}/docs',               // lista de documentos
    ];

    private string $url;
    private string $token;
    private int $timeout;
    private array $rutas;
    /** @var array<string,?string> id de Buk por id de usuario (en memoria) */
    private array $ids = [];

    public function __construct(array $cfg)
    {
        $this->url = rtrim((string) $cfg['url'], '/');
        $this->token = (string) $cfg['token'];
        $this->timeout = max(3, (int) ($cfg['timeout'] ?? 15));
        $this->rutas = array_merge(self::RUTAS, is_array($cfg['rutas'] ?? null) ? $cfg['rutas'] : []);
    }

    public function tipo(): string { return 'buk'; }
    public function nombre(): string { return 'Buk'; }

    public function liquidaciones(array $persona): array
    {
        $id = $this->idEmpleado($persona);
        if ($id === null) {
            return [];
        }
        $lista = array_map([$this, 'mapearLiquidacion'], $this->todas($this->ruta('liquidaciones', ['id' => $id])));
        usort($lista, static fn (array $a, array $b): int => strcmp($b['periodo'], $a['periodo']));
        return $lista;
    }

    public function liquidacion(array $persona, string $id): ?array
    {
        // Solo se consulta si la liquidación está en la lista de ESTA persona
        $base = null;
        foreach ($this->liquidaciones($persona) as $l) {
            if ($l['id'] === $id) {
                $base = $l;
                break;
            }
        }
        if ($base === null) {
            return null;
        }
        $r = $this->get($this->ruta('liquidacion', ['liq' => $id]));
        $d = $r['data'] ?? $r;
        $haberes = [];
        $descuentos = [];
        foreach ((array) ($d['lines'] ?? $d['items'] ?? $d['conceptos'] ?? []) as $c) {
            $monto = (int) round((float) ($c['amount'] ?? $c['monto'] ?? 0));
            $nombre = (string) ($c['name'] ?? $c['nombre'] ?? $c['concept'] ?? 'Concepto');
            $tipo = strtolower((string) ($c['type'] ?? $c['tipo'] ?? ''));
            if (in_array($tipo, ['haber', 'income', 'haberes', 'earning'], true) || ($tipo === '' && $monto >= 0)) {
                $haberes[] = [$nombre, abs($monto), (bool) ($c['taxable'] ?? $c['imponible'] ?? true)];
            } else {
                $descuentos[] = [$nombre, abs($monto)];
            }
        }
        return $base + ['haberes' => $haberes, 'descuentos_detalle' => $descuentos, 'pdf_url' => $d['pdf_url'] ?? $d['url'] ?? null];
    }

    public function liquidacionPdf(array $persona, string $id): ?array
    {
        $l = $this->liquidacion($persona, $id);
        if (!$l || empty($l['pdf_url'])) {
            return null;
        }
        return ['nombre' => 'Liquidacion_' . $l['periodo'] . '.pdf', 'mime' => 'application/pdf',
                'contenido' => $this->descargar((string) $l['pdf_url'])];
    }

    public function documentos(array $persona): array
    {
        $id = $this->idEmpleado($persona);
        if ($id === null) {
            return [];
        }
        $lista = array_map([$this, 'mapearDocumento'], $this->todas($this->ruta('documentos', ['id' => $id])));
        usort($lista, static fn (array $a, array $b): int => strcmp($b['fecha'], $a['fecha']));
        return $lista;
    }

    public function documentoArchivo(array $persona, string $id): ?array
    {
        foreach ($this->documentos($persona) as $d) {
            if ($d['id'] === $id && !empty($d['url'])) {
                return ['nombre' => $d['nombre'], 'mime' => $d['mime'] ?? 'application/octet-stream',
                        'contenido' => $this->descargar((string) $d['url'])];
            }
        }
        return null;
    }

    // ------------------------------------------------------------ persona en Buk

    private function idEmpleado(array $persona): ?string
    {
        $k = (string) $persona['id'];
        if (array_key_exists($k, $this->ids)) {
            return $this->ids[$k];
        }
        $id = null;
        if (!empty($persona['id_buk'])) {
            $id = (string) $persona['id_buk'];
        } else {
            foreach ([['document_number', $persona['rut'] ?? ''], ['email', $persona['email'] ?? '']] as [$campo, $valor]) {
                if ((string) $valor === '') {
                    continue;
                }
                $r = $this->get($this->rutas['empleados'] . '?' . http_build_query([$campo => $valor]));
                $lista = $r['data'] ?? [];
                // Solo si hay exactamente una coincidencia (nunca adivinar entre varias)
                if (is_array($lista) && count($lista) === 1 && isset($lista[0]['id'])) {
                    $id = (string) $lista[0]['id'];
                    break;
                }
            }
        }
        return $this->ids[$k] = $id;
    }

    // ------------------------------------------------------------ mapeo

    private function mapearLiquidacion(array $l): array
    {
        $periodo = (string) ($l['month'] ?? $l['period'] ?? $l['periodo'] ?? $l['date'] ?? '');
        if (preg_match('/^(\d{4})-(\d{2})/', $periodo, $m)) {
            $periodo = $m[1] . '-' . $m[2];
        } elseif (preg_match('/^(\d{2})-(\d{4})$/', $periodo, $m)) {
            $periodo = $m[2] . '-' . $m[1];
        }
        $bruto = (int) round((float) ($l['total_income'] ?? $l['gross_salary'] ?? $l['bruto'] ?? 0));
        $liquido = (int) round((float) ($l['liquid_salary'] ?? $l['net_salary'] ?? $l['liquido'] ?? 0));
        return [
            'id' => (string) ($l['id'] ?? $periodo),
            'periodo' => $periodo,
            'bruto' => $bruto,
            'descuentos' => (int) round((float) ($l['total_discounts'] ?? $l['descuentos'] ?? max(0, $bruto - $liquido))),
            'liquido' => $liquido,
            'aporte_patronal' => (int) round((float) ($l['employer_contribution'] ?? $l['aporte_patronal'] ?? 0)),
            'dias_trabajados' => (float) ($l['worked_days'] ?? $l['dias_trabajados'] ?? 30),
            'horas_extra' => (float) ($l['overtime_hours'] ?? $l['horas_extra'] ?? 0),
            'tiene_pdf' => !empty($l['pdf_url'] ?? $l['url'] ?? null) || !empty($l['has_pdf']),
        ];
    }

    private function mapearDocumento(array $d): array
    {
        $nombre = (string) ($d['name'] ?? $d['file_name'] ?? $d['nombre'] ?? 'documento');
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION) ?: 'pdf');
        $texto = mb_strtolower($nombre . ' ' . (string) ($d['folder'] ?? $d['category'] ?? ''));
        $categoria = match (true) {
            str_contains($texto, 'anexo') => 'anexos',
            str_contains($texto, 'contrato') => 'contratos',
            str_contains($texto, 'certific') => 'certificados',
            str_contains($texto, 'cedula') || str_contains($texto, 'cédula') || str_contains($texto, 'curricul') || str_contains($texto, 'antecedente') => 'personales',
            default => 'otros',
        };
        return [
            'id' => (string) ($d['id'] ?? md5($nombre)),
            'nombre' => $nombre,
            'categoria' => $categoria,
            'extension' => $ext,
            'tamano' => (int) ($d['size'] ?? $d['file_size'] ?? 0),
            'fecha' => substr((string) ($d['created_at'] ?? $d['date'] ?? $d['fecha'] ?? ''), 0, 10),
            'url' => $d['url'] ?? $d['download_url'] ?? null,
            'mime' => $d['content_type'] ?? null,
        ];
    }

    // ------------------------------------------------------------ HTTP

    private function ruta(string $clave, array $vars): string
    {
        $r = $this->rutas[$clave];
        foreach ($vars as $k => $v) {
            $r = str_replace('{' . $k . '}', rawurlencode((string) $v), $r);
        }
        return $r;
    }

    /** Recorre las páginas ('pagination' → 'next') hasta 20 páginas. */
    private function todas(string $ruta): array
    {
        $datos = [];
        $url = $ruta;
        for ($i = 0; $i < 20 && $url; $i++) {
            $r = $this->get($url);
            $datos = array_merge($datos, (array) ($r['data'] ?? []));
            $sig = $r['pagination']['next'] ?? null;
            $url = is_string($sig) && $sig !== '' ? $sig : null;
        }
        return $datos;
    }

    private function get(string $ruta): array
    {
        $url = preg_match('#^https://#', $ruta) ? $ruta : $this->url . $ruta;
        // Solo se sigue la paginación dentro del mismo servidor de Buk
        if (parse_url($url, PHP_URL_HOST) !== parse_url($this->url, PHP_URL_HOST)) {
            throw new RuntimeException('Respuesta de Buk no válida.');
        }
        $cuerpo = $this->http($url, ['Accept: application/json', 'auth_token: ' . $this->token]);
        $json = json_decode($cuerpo, true);
        if (!is_array($json)) {
            throw new RuntimeException('Buk respondió en un formato inesperado.');
        }
        return $json;
    }

    /** Descarga un archivo (enlace firmado que entrega Buk; no lleva el token). */
    private function descargar(string $url): string
    {
        if (!preg_match('#^https://#', $url)) {
            throw new RuntimeException('Enlace de descarga no válido.');
        }
        return $this->http($url, []);
    }

    private function http(string $url, array $cabeceras): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $cabeceras,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        $cuerpo = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($cuerpo === false || $codigo >= 400) {
            error_log('[mi_cuenta] Buk ' . $codigo . ' ' . preg_replace('/\?.*$/', '', $url) . ' ' . $error);
            throw new RuntimeException($codigo === 401 || $codigo === 403
                ? 'Buk rechazó la conexión (revisar el token).'
                : 'No fue posible conectarse con Buk. Intenta más tarde.');
        }
        return (string) $cuerpo;
    }
}
