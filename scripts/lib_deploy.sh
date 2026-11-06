#!/usr/bin/env bash
# =====================================================================
#  Lógica común de despliegue del Sistema SEDUC.
#  No se ejecuta directamente: la usan deploy_qa.sh / deploy_prod.sh,
#  que definen las variables de su entorno antes de cargarla.
#
#  Principios:
#   - Nunca usa rsync --delete: no borra archivos del sitio.
#   - Nunca toca la base de datos.
#   - Nunca sobrescribe archivos de usuarios (adjuntos, imágenes, QR...).
#   - Respaldo previo de todo archivo que vaya a cambiar.
#   - Publica lo que está en GitHub (origin/RAMA), no lo que haya
#     modificado a mano en la carpeta del repositorio.
# =====================================================================

set -uo pipefail

# Carpetas donde el sistema guarda archivos de usuarios. En cualquier
# nivel. Si existen en el repositorio, solo se AGREGAN archivos nuevos;
# los que ya están en el servidor nunca se reemplazan.
CARPETAS_PERSISTENTES=(uploads imagenes archivos archivos_denuncias codigosQR logs)

# Nunca se copian al sitio público.
EXCLUIR_SIEMPRE=(
  '.git/' '.github/' '.gitignore' '.gitattributes'
  'scripts/'
  'config_privada/' 'config_privada.local.php' '*.local.php'
  'clases/version_desplegada.json'
  '*.log' 'error.log' 'error_log'
  '*.sql' 'sql/'
)

ARCHIVO_VERSION_REL="clases/version_desplegada.json"

# ---------------------------------------------------------------------
log()   { local m="[$(date '+%Y-%m-%d %H:%M:%S')] $*"; echo "$m"; [[ -n "${LOG_FILE:-}" ]] && echo "$m" >> "$LOG_FILE"; }
error() { log "ERROR: $*"; exit 1; }
aviso() { log "AVISO: $*"; }

uso() {
  cat <<EOF
Uso: $(basename "$0") --dry-run | --ejecutar

  --dry-run    Muestra qué archivos cambiarían. No modifica nada. (por defecto)
  --ejecutar   Respalda y publica la rama '$RAMA' en $DOCROOT

Para deshacer una publicación: scripts/rollback_${ENTORNO}.sh
EOF
}

# ---------------------------------------------------------------------
tomar_bloqueo() {
  LOCK_DIR="$HOME/.deploy_ticket_${ENTORNO}.lock"
  if ! mkdir "$LOCK_DIR" 2>/dev/null; then
    error "Hay otro despliegue en curso ($LOCK_DIR). Si estás seguro de que no, borra esa carpeta."
  fi
  trap 'rm -rf "$LOCK_DIR" "${TMP_EXPORT:-/nonexistent}"' EXIT
}

validar_entorno() {
  [[ "$(whoami)" == "$USUARIO_ESPERADO" ]] || error "Usuario '$(whoami)' no autorizado. Se esperaba '$USUARIO_ESPERADO'."
  [[ "$HOME" == "$HOME_ESPERADO" ]]       || error "HOME '$HOME' distinto de '$HOME_ESPERADO'."
  [[ -d "$DOCROOT" ]]                     || error "No existe el document root: $DOCROOT"
  [[ -d "$REPO_DIR/.git" ]]               || error "No existe el repositorio en $REPO_DIR"

  local cfg="$HOME/config_privada/ticket_config.php"
  [[ -f "$cfg" ]] || error "Falta la configuración privada: $cfg"
  local perm; perm=$(stat -c '%a' "$cfg")
  [[ "$perm" == "600" || "$perm" == "400" ]] || aviso "Permisos de $cfg son $perm (se recomienda 600)."

  for herr in git rsync tar; do
    command -v "$herr" >/dev/null 2>&1 || error "Falta la herramienta '$herr' en el servidor."
  done

  local url; url=$(git -C "$REPO_DIR" remote get-url origin)
  [[ "$url" =~ github\.com[:/]cmjorquera/ticket(\.git)?$ ]] || error "El remoto origin ($url) no es cmjorquera/ticket."

  # La carpeta del repositorio no debe tener cambios hechos a mano.
  if [[ -n "$(git -C "$REPO_DIR" status --porcelain --untracked-files=no)" ]]; then
    error "El repositorio en $REPO_DIR tiene cambios locales. Revísalo con: git -C $REPO_DIR status"
  fi
}

obtener_codigo() {
  log "Descargando la rama '$RAMA' desde GitHub..."
  git -C "$REPO_DIR" fetch --quiet origin "+refs/heads/$RAMA:refs/remotes/origin/$RAMA" \
    || error "No se pudo descargar origin/$RAMA."

  COMMIT=$(git -C "$REPO_DIR" rev-parse "origin/$RAMA")
  COMMIT_CORTO=${COMMIT:0:7}
  COMMIT_MSG=$(git -C "$REPO_DIR" log -1 --format='%s' "$COMMIT")

  TMP_EXPORT="$HOME/.deploy_tmp_${ENTORNO}"
  rm -rf "$TMP_EXPORT" && mkdir -p "$TMP_EXPORT"
  git -C "$REPO_DIR" archive "$COMMIT" | tar -x -C "$TMP_EXPORT" || error "No se pudo extraer el código."

  COMMIT_ANTERIOR="(desconocido)"
  if [[ -f "$DOCROOT/$ARCHIVO_VERSION_REL" ]]; then
    COMMIT_ANTERIOR=$(grep -oE '"commit":\s*"[0-9a-f]+"' "$DOCROOT/$ARCHIVO_VERSION_REL" | grep -oE '[0-9a-f]{7,}' || echo "(desconocido)")
  fi
  local ant="$COMMIT_ANTERIOR"; [[ "$ant" =~ ^[0-9a-f]+$ ]] && ant=${ant:0:7}
  log "Publicado actualmente: $ant  ->  a publicar: $COMMIT_CORTO  \"$COMMIT_MSG\""
}

# Arma los filtros de rsync
filtros_codigo() {
  local f
  for f in "${EXCLUIR_SIEMPRE[@]}"; do echo "--exclude=$f"; done
  for f in "${CARPETAS_PERSISTENTES[@]}"; do echo "--exclude=$f/"; done
}
filtros_persistentes() {
  local f
  for f in "${EXCLUIR_SIEMPRE[@]}"; do echo "--exclude=$f"; done
  echo "--include=*/"
  for f in "${CARPETAS_PERSISTENTES[@]}"; do echo "--include=$f/***"; done
  echo "--exclude=*"
}

RSYNC_BASE=(rsync -rl --checksum --itemize-changes --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r)

# Calcula los cambios sin aplicarlos.
calcular_cambios() {
  local -a fc fp
  mapfile -t fc < <(filtros_codigo)
  mapfile -t fp < <(filtros_persistentes)

  CAMBIOS_CODIGO=$("${RSYNC_BASE[@]}" --dry-run "${fc[@]}" "$TMP_EXPORT/" "$DOCROOT/") \
    || error "rsync (simulación de código) falló."
  CAMBIOS_PERSIST=$("${RSYNC_BASE[@]}" --dry-run --ignore-existing --prune-empty-dirs "${fp[@]}" "$TMP_EXPORT/" "$DOCROOT/") \
    || error "rsync (simulación de persistentes) falló."

  # Formato itemize: ">fc.t...... ruta"  -> archivo modificado
  #                  ">f+++++++++ ruta"  -> archivo nuevo
  MODIFICADOS=$(printf '%s\n' "$CAMBIOS_CODIGO" | awk '/^>f/ && substr($0,3,1)!="+" {print substr($0,13)}')
  NUEVOS=$( { printf '%s\n' "$CAMBIOS_CODIGO"; printf '%s\n' "$CAMBIOS_PERSIST"; } \
             | awk '/^>f\+/ {print substr($0,13)}')

  N_MOD=$(printf '%s' "$MODIFICADOS" | grep -c . || true)
  N_NUE=$(printf '%s' "$NUEVOS" | grep -c . || true)
}

mostrar_resumen() {
  echo
  echo "================ RESUMEN ($ENTORNO) ================"
  echo " Rama:     $RAMA"
  echo " Commit:   $COMMIT_CORTO  $COMMIT_MSG"
  echo " Destino:  $DOCROOT"
  echo " Archivos que se REEMPLAZAN: $N_MOD"
  echo " Archivos NUEVOS:            $N_NUE"
  echo " Archivos que se BORRAN:     0 (este script nunca borra)"
  echo "===================================================="
  if (( N_MOD > 0 )); then echo "-- Se reemplazan:"; printf '%s\n' "$MODIFICADOS" | sed 's/^/   ~ /' | head -100; fi
  if (( N_NUE > 0 )); then echo "-- Nuevos:";        printf '%s\n' "$NUEVOS"      | sed 's/^/   + /' | head -100; fi
  (( N_MOD + N_NUE > 200 )) && echo "   (lista recortada; total $((N_MOD + N_NUE)))"
  echo
}

respaldar() {
  ID_DEPLOY=$(date '+%Y%m%d_%H%M%S')
  DIR_RESP="$RESPALDOS_DIR/$ID_DEPLOY"
  mkdir -p "$DIR_RESP" && chmod 700 "$RESPALDOS_DIR" "$DIR_RESP"

  printf '%s\n' "$MODIFICADOS" | grep . > "$DIR_RESP/modificados.txt" || true
  printf '%s\n' "$NUEVOS"      | grep . > "$DIR_RESP/nuevos.txt"      || true
  [[ -f "$DOCROOT/$ARCHIVO_VERSION_REL" ]] && echo "$ARCHIVO_VERSION_REL" >> "$DIR_RESP/modificados.txt"

  if [[ -s "$DIR_RESP/modificados.txt" ]]; then
    tar -czf "$DIR_RESP/archivos_anteriores.tar.gz" -C "$DOCROOT" -T "$DIR_RESP/modificados.txt" \
      || error "Falló el respaldo. No se publicó nada."
  fi
  cat > "$DIR_RESP/info.txt" <<EOF
entorno=$ENTORNO
fecha=$(date '+%Y-%m-%d %H:%M:%S')
commit_anterior=$COMMIT_ANTERIOR
commit_nuevo=$COMMIT
reemplazados=$N_MOD
nuevos=$N_NUE
EOF
  log "Respaldo creado: $DIR_RESP"
}

publicar() {
  local -a fc fp
  mapfile -t fc < <(filtros_codigo)
  mapfile -t fp < <(filtros_persistentes)

  "${RSYNC_BASE[@]}" "${fc[@]}" "$TMP_EXPORT/" "$DOCROOT/" > /dev/null \
    || error "rsync falló durante la publicación. Restaura con: scripts/rollback_${ENTORNO}.sh $ID_DEPLOY --ejecutar"
  "${RSYNC_BASE[@]}" --ignore-existing --prune-empty-dirs "${fp[@]}" "$TMP_EXPORT/" "$DOCROOT/" > /dev/null \
    || error "rsync (persistentes) falló. Restaura con: scripts/rollback_${ENTORNO}.sh $ID_DEPLOY --ejecutar"

  # Archivo de versión: dentro de clases/, que .htaccess bloquea desde la web.
  cat > "$DOCROOT/$ARCHIVO_VERSION_REL" <<EOF
{
  "entorno": "$ENTORNO",
  "rama": "$RAMA",
  "commit": "$COMMIT",
  "fecha_despliegue": "$(date '+%Y-%m-%d %H:%M:%S %z')",
  "id_despliegue": "$ID_DEPLOY"
}
EOF
  chmod 644 "$DOCROOT/$ARCHIVO_VERSION_REL"

  # Deja la copia de trabajo del repositorio en el mismo commit (solo avance rápido).
  git -C "$REPO_DIR" merge --ff-only --quiet "origin/$RAMA" 2>/dev/null \
    || aviso "No se pudo avanzar la copia de $REPO_DIR (la publicación sí se hizo)."

  log "PUBLICADO $ENTORNO commit=$COMMIT_CORTO id=$ID_DEPLOY reemplazados=$N_MOD nuevos=$N_NUE"
}

main_deploy() {
  local modo="${1:---dry-run}"
  case "$modo" in
    --dry-run|--ejecutar) ;;
    -h|--help) uso; exit 0 ;;
    *) uso; exit 1 ;;
  esac

  mkdir -p "$(dirname "$LOG_FILE")"
  tomar_bloqueo
  validar_entorno
  obtener_codigo
  calcular_cambios
  mostrar_resumen

  if [[ "$modo" == "--dry-run" ]]; then
    log "Simulación terminada ($ENTORNO, $COMMIT_CORTO). No se modificó nada."
    return 0
  fi

  if (( N_MOD + N_NUE == 0 )); then
    log "No hay cambios que publicar."
    return 0
  fi

  read -r -p "¿Publicar estos cambios en $ENTORNO? Escribe 'si' para continuar: " resp
  [[ "$resp" == "si" ]] || { log "Cancelado por el usuario."; return 0; }

  respaldar
  publicar
  echo
  echo "Listo. Si algo falla, deshaz con:"
  echo "  $REPO_DIR/scripts/rollback_${ENTORNO}.sh $ID_DEPLOY --ejecutar"
}

# =====================================================================
#  ROLLBACK: restaura los archivos reemplazados por un despliegue y
#  quita los archivos que ese despliegue agregó.
# =====================================================================
main_rollback() {
  local id="${1:-}" modo="${2:---dry-run}"
  if [[ -z "$id" || "$id" == -* ]]; then
    echo "Uso: $(basename "$0") <ID_DESPLIEGUE> --dry-run | --ejecutar"
    echo "Despliegues disponibles (más reciente al final):"
    ls -1 "$RESPALDOS_DIR" 2>/dev/null | tail -10 | sed 's/^/  /'
    exit 1
  fi
  [[ "$id" =~ ^[0-9]{8}_[0-9]{6}$ ]] || error "ID inválido: $id"
  [[ "$modo" == "--dry-run" || "$modo" == "--ejecutar" ]] || error "Modo inválido: $modo"

  local dir="$RESPALDOS_DIR/$id"
  [[ -d "$dir" ]] || error "No existe el respaldo $dir"

  mkdir -p "$(dirname "$LOG_FILE")"
  tomar_bloqueo
  [[ "$(whoami)" == "$USUARIO_ESPERADO" ]] || error "Usuario no autorizado."

  echo "=========== ROLLBACK $ENTORNO ($id) ==========="
  cat "$dir/info.txt"
  echo "Se restauran: $(grep -c . "$dir/modificados.txt" 2>/dev/null || echo 0) archivos"
  echo "Se quitan (agregados por ese despliegue): $(grep -c . "$dir/nuevos.txt" 2>/dev/null || echo 0) archivos"
  echo "==============================================="

  if [[ "$modo" == "--dry-run" ]]; then
    log "Simulación de rollback $id. No se modificó nada."
    return 0
  fi
  read -r -p "¿Restaurar $ENTORNO al estado anterior a $id? Escribe 'si': " resp
  [[ "$resp" == "si" ]] || { log "Cancelado."; return 0; }

  if [[ -f "$dir/archivos_anteriores.tar.gz" ]]; then
    tar -xzf "$dir/archivos_anteriores.tar.gz" -C "$DOCROOT" || error "Falló la restauración."
  fi
  if [[ -s "$dir/nuevos.txt" ]]; then
    local f
    while IFS= read -r f; do
      [[ -z "$f" || "$f" == /* || "$f" == *..* ]] && continue
      [[ -f "$DOCROOT/$f" ]] && rm -f -- "$DOCROOT/$f"
    done < "$dir/nuevos.txt"
  fi
  if ! grep -qx "$ARCHIVO_VERSION_REL" "$dir/modificados.txt" 2>/dev/null; then
    rm -f "$DOCROOT/$ARCHIVO_VERSION_REL"
  fi
  log "ROLLBACK $ENTORNO id=$id completado."
}
