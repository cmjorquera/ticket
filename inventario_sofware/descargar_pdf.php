<?php
require_once __DIR__ . '/componentes/boot.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

function inv_pdf_h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function inv_pdf_moneda($valor)
{
    return 'USD ' . number_format((float)$valor, 0, ',', '.');
}

function inv_pdf_logo_base()
{
    $candidatos = [
        __DIR__ . '/../imagenes/logo_seduc.png',
        __DIR__ . '/../img/logo_seduc.png',
    ];

    foreach ($candidatos as $ruta) {
        if (is_file($ruta)) {
            return $ruta;
        }
    }

    return '';
}

function inv_pdf_encabezado($titulo, $subtitulo = '')
{
    $logo = inv_pdf_logo_base();
    $html = '<div style="border-bottom:1px solid #d6e1ec;padding-bottom:12px;margin-bottom:18px;">';
    $html .= '<table width="100%" cellpadding="0" cellspacing="0"><tr>';
    $html .= '<td width="90" style="vertical-align:middle;">';
    if ($logo !== '') {
        $html .= '<img src="' . $logo . '" style="width:70px;">';
    }
    $html .= '</td>';
    $html .= '<td style="vertical-align:middle;">';
    $html .= '<h1 style="margin:0;font-size:22px;color:#17324d;">' . inv_pdf_h($titulo) . '</h1>';
    if ($subtitulo !== '') {
        $html .= '<div style="margin-top:4px;font-size:11px;color:#5f7388;">' . inv_pdf_h($subtitulo) . '</div>';
    }
    $html .= '</td>';
    $html .= '<td width="160" style="text-align:right;vertical-align:middle;font-size:10px;color:#5f7388;">Generado el ' . date('d-m-Y H:i') . '</td>';
    $html .= '</tr></table></div>';
    return $html;
}

function inv_pdf_estilos()
{
    return '
    <style>
        body { font-family: sans-serif; color: #17324d; font-size: 11px; }
        h2 { color: #17324d; font-size: 16px; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #eef5fb; color: #17324d; font-weight: bold; font-size: 10px; padding: 8px; border: 1px solid #d8e4ef; text-align: left; }
        td { padding: 7px 8px; border: 1px solid #d8e4ef; vertical-align: top; }
        .muted { color: #6c8298; }
        .chip { display: inline-block; padding: 4px 8px; background: #f4f7fb; border: 1px solid #d8e4ef; border-radius: 999px; font-size: 10px; margin-right: 6px; }
        .box { border: 1px solid #d8e4ef; background: #fbfdff; border-radius: 12px; padding: 10px 12px; margin-bottom: 14px; }
        .grid td { width: 25%; }
        .empty { color: #6c8298; font-style: italic; }
    </style>';
}

try {
    $tipo = trim((string)($_GET['tipo'] ?? ''));
    $mpdf = new Mpdf([
        'margin_top' => 14,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_bottom' => 12,
        'tempDir' => sys_get_temp_dir(),
    ]);

    $html = inv_pdf_estilos();
    $nombreArchivo = 'inventario_software.pdf';

    if ($tipo === 'software_listado') {
        $filtros = [
            'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
            'id_usuario_responsable' => (int)($_GET['id_usuario_responsable'] ?? 0),
            'tipo_licenciamiento' => trim((string)($_GET['tipo_licenciamiento'] ?? '')),
            'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
        ];
        $datos = $inventario->listarSoftwares($filtros);
        $licencias = 0;
        $costo = 0;
        foreach ($datos as $fila) {
            $licencias += (int)($fila['cantidad_licencias'] ?? 0);
            $costo += (float)($fila['costo'] ?? 0);
        }

        $html .= inv_pdf_encabezado('Listado de software y licencias', 'Exportacion filtrada del modulo operativo');
        $html .= '<div class="box"><span class="chip">Registros: ' . count($datos) . '</span><span class="chip">Licencias: ' . $licencias . '</span><span class="chip">Costo: ' . inv_pdf_moneda($costo) . '</span></div>';
        $html .= '<table><thead><tr><th>ID</th><th>Software</th><th>Colegio</th><th>Version</th><th>Licencias</th><th>Tipo</th><th>Pagado por</th><th>Responsable</th></tr></thead><tbody>';
        if ($datos) {
            foreach ($datos as $fila) {
                $html .= '<tr>';
                $html .= '<td>' . (int)$fila['id_software'] . '</td>';
                $html .= '<td><strong>' . inv_pdf_h($fila['nombre_software']) . '</strong><br><span class="muted">' . inv_pdf_h($fila['proveedor'] ?? '-') . '</span></td>';
                $html .= '<td>' . inv_pdf_h($fila['nom_colegio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['version_software'] ?: '-') . '</td>';
                $html .= '<td>' . (int)$fila['cantidad_licencias'] . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['tipo_licenciamiento']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['pagado_por']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['responsable'] ?: 'Sin asignar') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="8" class="empty">No hay software para los filtros seleccionados.</td></tr>';
        }
        $html .= '</tbody></table>';
        $nombreArchivo = 'inventario_software_' . date('Ymd_His') . '.pdf';
    } elseif ($tipo === 'sitios_listado') {
        $filtros = [
            'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
            'id_usuario_responsable' => (int)($_GET['id_usuario_responsable'] ?? 0),
            'tipo_sitio' => trim((string)($_GET['tipo_sitio'] ?? '')),
            'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
        ];
        $datos = $inventario->listarSitiosWeb($filtros);
        $conteo = ['Web' => 0, 'App' => 0, 'Cliente' => 0];
        foreach ($datos as $fila) {
            $tipoFila = (string)($fila['tipo_sitio'] ?? '');
            if (isset($conteo[$tipoFila])) {
                $conteo[$tipoFila]++;
            }
        }

        $html .= inv_pdf_encabezado('Listado de sitios web, apps y clientes', 'Exportacion filtrada del inventario digital');
        $html .= '<div class="box"><span class="chip">Registros: ' . count($datos) . '</span><span class="chip">Web: ' . $conteo['Web'] . '</span><span class="chip">App: ' . $conteo['App'] . '</span><span class="chip">Cliente: ' . $conteo['Cliente'] . '</span></div>';
        $html .= '<table><thead><tr><th>ID</th><th>Nombre</th><th>Colegio</th><th>Tipo</th><th>URL</th><th>Estado</th><th>Responsable</th></tr></thead><tbody>';
        if ($datos) {
            foreach ($datos as $fila) {
                $html .= '<tr>';
                $html .= '<td>' . (int)$fila['id_sitio'] . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['nombre_sitio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['nom_colegio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['tipo_sitio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['url_sitio'] ?: '-') . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['estado_sitio'] ?: '-') . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['responsable'] ?: 'Sin asignar') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="7" class="empty">No hay sitios para los filtros seleccionados.</td></tr>';
        }
        $html .= '</tbody></table>';
        $nombreArchivo = 'inventario_sitios_' . date('Ymd_His') . '.pdf';
    } elseif ($tipo === 'consulta_software') {
        $software = trim((string)($_GET['software'] ?? ''));
        $consulta = $inventario->obtenerConsultaPorSoftware($software);

        $html .= inv_pdf_encabezado('Consulta por software', $software !== '' ? $software : 'Sin software seleccionado');
        $html .= '<table class="grid"><tbody><tr><td><strong>Software</strong><br>' . inv_pdf_h($consulta['software'] ?: '-') . '</td><td><strong>Licencias</strong><br>' . (int)$consulta['resumen']['total_licencias'] . '</td><td><strong>Colegios</strong><br>' . (int)$consulta['resumen']['total_colegios'] . '</td><td><strong>Costo total</strong><br>' . inv_pdf_moneda($consulta['resumen']['costo_total']) . '</td></tr></tbody></table>';
        $html .= '<h2>Distribucion por colegio</h2>';
        $html .= '<table><thead><tr><th>Colegio</th><th>Licencias</th><th>Registros</th><th>Licenciamiento</th><th>Pagado por</th><th>Costo total</th></tr></thead><tbody>';
        if (!empty($consulta['colegios'])) {
            foreach ($consulta['colegios'] as $fila) {
                $html .= '<tr>';
                $html .= '<td>' . inv_pdf_h($fila['nom_colegio']) . '</td>';
                $html .= '<td>' . (int)$fila['licencias'] . '</td>';
                $html .= '<td>' . (int)$fila['registros'] . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['licenciamiento'] ?: '-') . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['pagado_por'] ?: '-') . '</td>';
                $html .= '<td>' . inv_pdf_moneda($fila['costo_total']) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" class="empty">No se encontraron registros para este software.</td></tr>';
        }
        $html .= '</tbody></table>';
        $nombreArchivo = 'consulta_software_' . preg_replace('/[^a-z0-9]+/i', '_', $software ?: 'sin_dato') . '.pdf';
    } elseif ($tipo === 'consulta_colegio') {
        $idColegio = (int)($_GET['id_colegio'] ?? 0);
        $consulta = $inventario->obtenerConsultaPorColegio($idColegio);
        $colegio = $consulta['colegio'];

        if (!$colegio) {
            throw new RuntimeException('No se encontro el colegio solicitado.');
        }

        $html .= inv_pdf_encabezado('Consulta por colegio', $colegio['nom_colegio']);
        $html .= '<div class="box"><strong>Colegio:</strong> ' . inv_pdf_h($colegio['nom_colegio']) . '</div>';
        $html .= '<table class="grid"><tbody><tr><td><strong>Softwares</strong><br>' . (int)$consulta['resumen']['total_softwares'] . '</td><td><strong>Licencias</strong><br>' . (int)$consulta['resumen']['total_licencias'] . '</td><td><strong>Sitios</strong><br>' . (int)$consulta['resumen']['total_sitios'] . '</td><td><strong>Costo software</strong><br>' . inv_pdf_moneda($consulta['resumen']['costo_total']) . '</td></tr></tbody></table>';
        $html .= '<h2>Softwares del colegio</h2>';
        $html .= '<table><thead><tr><th>Software</th><th>Version</th><th>Licencias</th><th>Tipo</th><th>Pagado por</th></tr></thead><tbody>';
        if (!empty($consulta['softwares'])) {
            foreach ($consulta['softwares'] as $fila) {
                $html .= '<tr>';
                $html .= '<td>' . inv_pdf_h($fila['nombre_software']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['version_software'] ?: '-') . '</td>';
                $html .= '<td>' . (int)$fila['cantidad_licencias'] . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['tipo_licenciamiento']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['pagado_por']) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="5" class="empty">Este colegio no tiene software registrado.</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<h2>Sitios del colegio</h2>';
        $html .= '<table><thead><tr><th>Nombre</th><th>Tipo</th><th>Estado</th><th>URL</th></tr></thead><tbody>';
        if (!empty($consulta['sitios'])) {
            foreach ($consulta['sitios'] as $fila) {
                $html .= '<tr>';
                $html .= '<td>' . inv_pdf_h($fila['nombre_sitio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['tipo_sitio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['estado_sitio']) . '</td>';
                $html .= '<td>' . inv_pdf_h($fila['url_sitio'] ?: '-') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="4" class="empty">Este colegio no tiene sitios registrados.</td></tr>';
        }
        $html .= '</tbody></table>';
        $nombreArchivo = 'consulta_colegio_' . (int)$colegio['id_colegio'] . '.pdf';
    } elseif ($tipo === 'software_ficha') {
        $idSoftware = (int)($_GET['id_software'] ?? 0);
        $software = $inventario->obtenerSoftwareCompleto($idSoftware);

        if (!$software) {
            throw new RuntimeException('No se encontro el software solicitado.');
        }

        $html .= inv_pdf_encabezado('Ficha de software', $software['nombre_software']);
        $html .= '<table class="grid"><tbody><tr>'
            . '<td><strong>Colegio</strong><br>' . inv_pdf_h($software['nom_colegio']) . '</td>'
            . '<td><strong>Responsable</strong><br>' . inv_pdf_h($software['responsable'] ?: 'Sin asignar') . '</td>'
            . '<td><strong>Licencias</strong><br>' . (int)$software['cantidad_licencias'] . '</td>'
            . '<td><strong>Costo</strong><br>' . inv_pdf_h($software['moneda']) . ' ' . number_format((float)$software['costo'], 2, ',', '.') . '</td>'
            . '</tr></tbody></table>';
        $html .= '<table><tbody>'
            . '<tr><th width="24%">Version</th><td>' . inv_pdf_h($software['version_software'] ?: '-') . '</td></tr>'
            . '<tr><th>Licenciamiento</th><td>' . inv_pdf_h($software['tipo_licenciamiento']) . '</td></tr>'
            . '<tr><th>Pagado por</th><td>' . inv_pdf_h($software['pagado_por']) . '</td></tr>'
            . '<tr><th>Proveedor</th><td>' . inv_pdf_h($software['proveedor'] ?: '-') . '</td></tr>'
            . '<tr><th>URL o referencia</th><td>' . inv_pdf_h($software['url_referencia'] ?: '-') . '</td></tr>'
            . '<tr><th>Observaciones</th><td>' . nl2br(inv_pdf_h($software['observaciones'] ?: 'Sin observaciones.')) . '</td></tr>'
            . '</tbody></table>';

        $html .= '<h2>Datos de almacenamiento</h2><table><thead><tr><th>Nombre</th><th>RUT</th><th>Email</th><th>Otros</th></tr></thead><tbody>';
        if (!empty($software['almacenamiento'])) {
            foreach ($software['almacenamiento'] as $fila) {
                $html .= '<tr>'
                    . '<td>' . inv_pdf_h($fila['nombre_contacto'] ?: '-') . '</td>'
                    . '<td>' . inv_pdf_h($fila['rut_contacto'] ?: '-') . '</td>'
                    . '<td>' . inv_pdf_h($fila['email_contacto'] ?: '-') . '</td>'
                    . '<td>' . inv_pdf_h($fila['otros_datos'] ?: '-') . '</td>'
                    . '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="4" class="empty">No hay datos de almacenamiento asociados.</td></tr>';
        }
        $html .= '</tbody></table>';
        $nombreArchivo = 'software_' . (int)$software['id_software'] . '.pdf';
    } elseif ($tipo === 'sitio_ficha') {
        $idSitio = (int)($_GET['id_sitio'] ?? 0);
        $sitio = $inventario->obtenerSitioWeb($idSitio);

        if (!$sitio) {
            throw new RuntimeException('No se encontro el sitio solicitado.');
        }

        $html .= inv_pdf_encabezado('Ficha de sitio web', $sitio['nombre_sitio']);
        $html .= '<table class="grid"><tbody><tr>'
            . '<td><strong>Colegio</strong><br>' . inv_pdf_h($sitio['nom_colegio']) . '</td>'
            . '<td><strong>Responsable</strong><br>' . inv_pdf_h($sitio['responsable'] ?: 'Sin asignar') . '</td>'
            . '<td><strong>Tipo</strong><br>' . inv_pdf_h($sitio['tipo_sitio']) . '</td>'
            . '<td><strong>Estado</strong><br>' . inv_pdf_h($sitio['estado_sitio']) . '</td>'
            . '</tr></tbody></table>';
        $html .= '<table><tbody>'
            . '<tr><th width="24%">URL</th><td>' . inv_pdf_h($sitio['url_sitio'] ?: '-') . '</td></tr>'
            . '<tr><th>Hosting / proveedor</th><td>' . inv_pdf_h($sitio['proveedor_hosting'] ?: '-') . '</td></tr>'
            . '<tr><th>Observaciones</th><td>' . nl2br(inv_pdf_h($sitio['observaciones'] ?: 'Sin observaciones.')) . '</td></tr>'
            . '</tbody></table>';
        $nombreArchivo = 'sitio_' . (int)$sitio['id_sitio'] . '.pdf';
    } else {
        throw new RuntimeException('Tipo de PDF no valido.');
    }

    $mpdf->WriteHTML($html);
    $mpdf->Output($nombreArchivo, 'D');
    exit;
} catch (Throwable $e) {
    inventario_responder_error('No fue posible generar el PDF: ' . $e->getMessage());
}
