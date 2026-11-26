#!/usr/bin/env bash
# Deshace un despliegue de PRODUCCIÓN.
# Uso:  ~/repositories/sistema-central-seduc/scripts/rollback_prod.sh                       (lista respaldos)
#       ~/repositories/sistema-central-seduc/scripts/rollback_prod.sh <ID> --dry-run
#       ~/repositories/sistema-central-seduc/scripts/rollback_prod.sh <ID> --ejecutar

ENTORNO="prod"
USUARIO_ESPERADO="acceso"
DOCROOT="/home/acceso/public_html"
RESPALDOS_DIR="/home/acceso/respaldos/sistema_prod"
LOG_FILE="/home/acceso/deploy_logs/sistema_prod.log"

source "$(dirname "$(readlink -f "$0")")/lib_deploy.sh" || exit 1
main_rollback "$@"
exit $?
