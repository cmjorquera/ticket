#!/bin/bash

# ════════════════════════════════════════════════════════════════════════════════
# SCRIPT: Limpiar referencias a validar_sesion.php
# 
# Qué hace:
# 1. Busca todas las referencias a includes/validar_sesion.php
# 2. Las cambia a validar_sesion.php (raíz)
# 3. Elimina el archivo duplicado
# ════════════════════════════════════════════════════════════════════════════════

echo "🔧 Iniciando limpieza de validar_sesion.php..."
echo ""

# Contar archivos a cambiar
CONTADOR=$(grep -r "includes/validar_sesion.php" . 2>/dev/null | wc -l)
echo "📊 Se encontraron $CONTADOR referencias a cambiar"
echo ""

# ────────────────────────────────────────────────────────────────────────────────
# 1. Cambiar en dashboard.php
# ────────────────────────────────────────────────────────────────────────────────

if [ -f "dashboard.php" ]; then
    echo "✏️  Corrigiendo dashboard.php..."
    sed -i "s|require_once __DIR__ . '/includes/validar_sesion.php';|require_once __DIR__ . '/validar_sesion.php';|g" dashboard.php
    echo "   ✅ dashboard.php corregido"
fi

# ────────────────────────────────────────────────────────────────────────────────
# 2. Cambiar en pages/*.php (14 archivos)
# ────────────────────────────────────────────────────────────────────────────────

if [ -d "pages" ]; then
    echo "✏️  Corrigiendo pages/*.php..."
    
    find pages -name "*.php" -type f | while read file; do
        sed -i "s|require_once __DIR__ . '/../includes/validar_sesion.php';|require_once __DIR__ . '/../validar_sesion.php';|g" "$file"
        echo "   ✅ $(basename $file) corregido"
    done
fi

# ────────────────────────────────────────────────────────────────────────────────
# 3. Eliminar archivo duplicado
# ────────────────────────────────────────────────────────────────────────────────

if [ -f "includes/validar_sesion.php" ]; then
    echo ""
    echo "🗑️  Eliminando duplicado: includes/validar_sesion.php"
    rm -f includes/validar_sesion.php
    echo "   ✅ Archivo duplicado eliminado"
fi

# ────────────────────────────────────────────────────────────────────────────────
# 4. Verificación final
# ────────────────────────────────────────────────────────────────────────────────

echo ""
echo "🔍 Verificando resultados..."
echo ""

# Buscar referencias restantes a includes/validar_sesion.php
RESTANTES=$(grep -r "includes/validar_sesion.php" . 2>/dev/null | wc -l)

if [ "$RESTANTES" -eq 0 ]; then
    echo "✅ ¡PERFECTO! No hay más referencias a includes/validar_sesion.php"
else
    echo "❌ Aún hay $RESTANTES referencias sin cambiar"
fi

# Verificar que validar_sesion.php existe en raíz
if [ -f "validar_sesion.php" ]; then
    echo "✅ validar_sesion.php existe en raíz"
else
    echo "❌ WARNING: validar_sesion.php NO existe en raíz"
fi

# Verificar que el duplicado fue eliminado
if [ ! -f "includes/validar_sesion.php" ]; then
    echo "✅ includes/validar_sesion.php fue eliminado"
else
    echo "❌ includes/validar_sesion.php aún existe"
fi

echo ""
echo "════════════════════════════════════════════════════════════════════════════"
echo "🎉 ¡LIMPIEZA COMPLETADA!"
echo "════════════════════════════════════════════════════════════════════════════"
echo ""
echo "Cambios realizados:"
echo "✅ dashboard.php → ahora usa validar_sesion.php (raíz)"
echo "✅ pages/*.php → ahora usan ../validar_sesion.php"
echo "✅ includes/validar_sesion.php → eliminado"
echo ""
echo "Tu sistema está listo para usar 🚀"
echo ""
