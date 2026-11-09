<?php
/**
 * PDF del ticket. GET id, vista (usuario|tecnico|admin), descargar=1 (opcional).
 * Solo quien puede ver el ticket. La bitácora interna va solo en la vista
 * técnico / administración. Lógica: ticket/logica/LOGICA_PDF_CORREO.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Tickets/PdfTicket.php';

Session::iniciar();
$usuarioId = (int) ($_SESSION['id'] ?? 0);
$ticketId = (int) ($_GET['id'] ?? 0);
try {
    $db = Conexion::getInstance('sistema_panel_central');
    $ticket = $usuarioId > 0 ? ticket_buscar_para_acceso($ticketId, $db) : false;
    if (!$ticket || !puede_ver_ticket($usuarioId, $ticket, $db)) {
        http_response_code($usuarioId > 0 ? 403 : 401);
        header('Content-Type: text/plain; charset=utf-8');
        echo $usuarioId > 0 ? 'No tienes permiso para ver este ticket.' : 'Tu sesión expiró. Vuelve a ingresar.';
        exit;
    }
    $vista = (string) ($_GET['vista'] ?? '');
    $conBitacora = $vista !== 'usuario' && (new BitacoraTicket($db))->rolPara($usuarioId, $ticket, $vista) !== null;
    $datos = PdfTicket::datos($db, $ticketId, $conBitacora);
    $pdf = PdfTicket::generar($datos);
} catch (Throwable $e) {
    error_log('[ticket_pdf] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No fue posible generar el PDF.';
    exit;
}
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdf));
header('Content-Disposition: ' . (isset($_GET['descargar']) ? 'attachment' : 'inline') . '; filename="ticket_' . $ticketId . '.pdf"');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $pdf;
