#!/usr/bin/env bash
# Despliegue de PRODUCCIÓN: publica la rama 'main' en https://acceso.seduc.cl/
# Uso:  ~/repositories/sistema-central-seduc/scripts/deploy_prod.sh --dry-run
#       ~/repositories/sistema-central-seduc/scripts/deploy_prod.sh --ejecutar
# Antes de publicar en producción, el cambio debe estar probado en QA.

ENTORNO="prod"
RAMA="main"
USUARIO_ESPERADO="acceso"
HOME_ESPERADO="/home/acceso"
DOCROOT="/home/acceso/public_html"
REPO_DIR="/home/acceso/repositories/sistema-central-seduc"
RESPALDOS_DIR="/home/acceso/respaldos/sistema_prod"
LOG_FILE="/home/acceso/deploy_logs/sistema_prod.log"

source "$(dirname "$(readlink -f "$0")")/lib_deploy.sh" || exit 1
main_deploy "$@"
exit $?
