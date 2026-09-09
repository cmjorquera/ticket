<?php
/**
 * ════════════════════════════════════════════════════════════════════════════════
 * FUNCIÓN DE DÍAS RESTANTES (COUNTDOWN) - Dinámico
 * ════════════════════════════════════════════════════════════════════════════════
 * 
 * Archivo: ticket_dias_restantes.php
 * Descripción: Calcula y muestra los días que FALTAN para que se cumpla 
 *              la fecha estimada de resolución (cambia cada día)
 * 
 * Diferencia con la función anterior:
 * - ANTERIOR: "08/04/2026 (2 días)" - Estático, diferencia fija
 * - NUEVO: "2 días restantes" - Dinámico, cambia cada día
 * 
 * Ubicación: clases/ticket_dias_restantes.php
 * 
 * ════════════════════════════════════════════════════════════════════════════════
 */

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN PRINCIPAL: Calcular días restantes
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Calcula cuántos días FALTAN hasta la fecha estimada (a partir de HOY)
 * 
 * @param string $fecha_estimada - Fecha estimada (formato: YYYY-MM-DD HH:MM:SS)
 * @return int|null - Número de días restantes (positivo, cero o negativo si vencido)
 *
 * @example
 * echo diasRestantes('2026-09-08 11:54:00');  // 2 (si hoy es 06/09)
 * echo diasRestantes('2026-09-06 11:54:00');  // -1 (ya pasó, vencido)
 */
function diasRestantes($fecha_estimada) {
    // Validar que la fecha no sea nula
    if (empty($fecha_estimada)) {
        return null;
    }
    
    try {
        // Crear objeto DateTime de la fecha estimada
        $fecha_fin = new DateTime($fecha_estimada);
        
        // Obtener fecha de HOY a las 00:00:00 (para comparar solo días)
        $hoy = new DateTime('today midnight');
        
        // Calcular diferencia
        $intervalo = $hoy->diff($fecha_fin);
        
        // Si invert es 1, la fecha estimada es anterior a hoy (vencido)
        $dias = $intervalo->days;
        
        if ($intervalo->invert == 1) {
            // Fecha es anterior a hoy, retornar negativo
            return -$dias;
        }
        
        return $dias;
        
    } catch (Exception $e) {
        return null;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener texto descriptivo de días restantes
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Convierte número de días restantes a texto descriptivo en español
 * 
 * @param int $dias - Días restantes (puede ser negativo)
 * @return string - Texto descriptivo (ej: "2 días restantes", "Vencido", "Hoy")
 * 
 * @example
 * echo obtenerTextoDiasRestantes(2);   // "2 días restantes"
 * echo obtenerTextoDiasRestantes(1);   // "1 día restante"
 * echo obtenerTextoDiasRestantes(0);   // "Vence hoy"
 * echo obtenerTextoDiasRestantes(-1);  // "Vencido hace 1 día"
 */
function obtenerTextoDiasRestantes($dias) {
    if ($dias === null) {
        return "-";
    }
    
    // Casos especiales
    if ($dias > 1) {
        return "$dias días restantes";
    } elseif ($dias == 1) {
        return "1 día restante";
    } elseif ($dias == 0) {
        return "Vence hoy";
    } else {
        // Vencido (negativo)
        $dias_vencido = abs($dias);
        if ($dias_vencido == 1) {
            return "Vencido hace 1 día";
        } else {
            return "Vencido hace $dias_vencido días";
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener clase CSS según urgencia
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Retorna clase CSS para colorear según urgencia del ticket
 * 
 * @param int $dias - Días restantes
 * @return string - Clase CSS Bootstrap (bg-success, bg-warning, etc)
 * 
 * @example
 * echo obtenerClaseDiasRestantes(5);   // "bg-success" (Verde)
 * echo obtenerClaseDiasRestantes(1);   // "bg-warning" (Amarillo)
 * echo obtenerClaseDiasRestantes(0);   // "bg-danger"  (Rojo)
 * echo obtenerClaseDiasRestantes(-2);  // "bg-danger"  (Rojo)
 */
function obtenerClaseDiasRestantes($dias) {
    if ($dias === null) {
        return 'bg-secondary';
    }
    
    if ($dias >= 3) {
        return 'bg-success';        // Verde - Tiempo suficiente
    } elseif ($dias >= 1) {
        return 'bg-warning text-dark';  // Amarillo - Se acerca
    } else {
        return 'bg-danger';         // Rojo - Hoy o vencido
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN: Generar HTML completo del badge
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Genera HTML completo con fecha + días restantes + color
 * 
 * @param string $fecha_estimada - Fecha estimada
 * @return string - HTML del badge con color dinámico
 * 
 * @example
 * echo generarBadgeDiasRestantes('2026-09-08 11:54:00');
 * // Output: <span class="badge bg-info">08/09/2026<br><small>(2 días)</small></span>
 */
function generarBadgeDiasRestantes($fecha_estimada) {
    if (empty($fecha_estimada)) {
        return '<span class="badge bg-secondary">-</span>';
    }
    
    try {
        // Calcular días restantes
        $dias = diasRestantes($fecha_estimada);
        
        if ($dias === null) {
            return '<span class="badge bg-secondary">Error</span>';
        }
        
        // Obtener texto descriptivo
        $texto = obtenerTextoDiasRestantes($dias);
        
        // Obtener clase CSS
        $clase = obtenerClaseDiasRestantes($dias);
        
        // Formatear fecha
        $fecha_fin = new DateTime($fecha_estimada);
        $fecha_formateada = $fecha_fin->format('d/m/Y');
        
        // Retornar HTML
        return sprintf(
            '<span class="badge %s">%s<br><small>(%s)</small></span>',
            htmlspecialchars($clase),
            htmlspecialchars($fecha_formateada),
            htmlspecialchars($texto)
        );
        
    } catch (Exception $e) {
        return '<span class="badge bg-secondary">Error</span>';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener emoji según urgencia
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Retorna emoji descriptivo según urgencia
 * 
 * @param int $dias - Días restantes
 * @return string - Emoji (🟢 🟡 🔴 ⚠️)
 */
function obtenerEmojiDiasRestantes($dias) {
    if ($dias === null) {
        return '⚪';
    }
    
    if ($dias >= 3) {
        return '🟢';  // Verde - Tranquilo
    } elseif ($dias >= 1) {
        return '🟡';  // Amarillo - Atención
    } elseif ($dias == 0) {
        return '🟠';  // Naranja - Hoy
    } else {
        return '🔴';  // Rojo - Vencido
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN ALTERNATIVA: Incluir emoji en el texto
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Versión de generarBadgeDiasRestantes que incluye emoji
 * 
 * @param string $fecha_estimada - Fecha estimada
 * @return string - HTML del badge con emoji
 */
function generarBadgeDiasRestantesConEmoji($fecha_estimada) {
    if (empty($fecha_estimada)) {
        return '⚪ -';
    }
    
    try {
        $dias = diasRestantes($fecha_estimada);
        
        if ($dias === null) {
            return '⚪ Error';
        }
        
        $emoji = obtenerEmojiDiasRestantes($dias);
        $texto = obtenerTextoDiasRestantes($dias);
        $clase = obtenerClaseDiasRestantes($dias);
        
        $fecha_fin = new DateTime($fecha_estimada);
        $fecha_formateada = $fecha_fin->format('d/m/Y');
        
        return sprintf(
            '<span class="badge %s">%s %s<br><small>(%s)</small></span>',
            htmlspecialchars($clase),
            $emoji,
            htmlspecialchars($fecha_formateada),
            htmlspecialchars($texto)
        );
        
    } catch (Exception $e) {
        return '⚪ Error';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener estado de urgencia (para filtros)
// ─────────────────────────────────────────────────────────────────────────────
/**
 * Retorna categoría de urgencia para filtrar tickets
 * 
 * @param int $dias - Días restantes
 * @return string - Categoría: 'tranquilo', 'atencion', 'urgente', 'vencido'
 */
function obtenerCategoriaUrgencia($dias) {
    if ($dias === null) {
        return 'desconocido';
    }
    
    if ($dias >= 3) {
        return 'tranquilo';
    } elseif ($dias >= 1) {
        return 'atencion';
    } elseif ($dias == 0) {
        return 'urgente';
    } else {
        return 'vencido';
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// EJEMPLO DE USO EN TABLA
// ═════════════════════════════════════════════════════════════════════════════════

/*

<?php
require_once __DIR__ . '/../../clases/ticket_dias_restantes.php';

// Supongamos que tienes un array de tickets
$tickets = [
    ['id' => 1, 'caso' => 'Inventario', 'fecha_estimada_admin' => '2026-09-08 11:54:00'],
    ['id' => 2, 'caso' => 'Sistema', 'fecha_estimada_admin' => '2026-09-07 11:54:00'],
    ['id' => 3, 'caso' => 'Horas', 'fecha_estimada_admin' => '2026-09-06 11:54:00'],
];

// Hoy es 2026-09-06 (ejemplo)
?>

<table class="table">
    <tbody>
        <?php foreach($tickets as $ticket): ?>
            <tr>
                <td><?php echo htmlspecialchars($ticket['caso']); ?></td>
                <td>
                    <?php echo generarBadgeDiasRestantesConEmoji(
                        $ticket['fecha_estimada_admin']
                    ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

RESULTADO (si hoy es 2026-09-06):

┌──────────────┬─────────────────────────────────────┐
│ CASO         │ FECHA DE RESPUESTA                  │
├──────────────┼─────────────────────────────────────┤
│ Inventario   │ 🟢 08/09/2026 (2 días restantes)   │
│ Sistema      │ 🟡 07/09/2026 (1 día restante)     │
│ Horas        │ 🟠 06/09/2026 (Vence hoy)          │
└──────────────┴─────────────────────────────────────┘

*/

?>
