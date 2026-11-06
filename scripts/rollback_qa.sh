#!/usr/bin/env bash
# Deshace un despliegue de QA.
# Uso:  ~/repositories/ticket/scripts/rollback_qa.sh                       (lista respaldos)
#       ~/repositories/ticket/scripts/rollback_qa.sh <ID> --dry-run
#       ~/repositories/ticket/scripts/rollback_qa.sh <ID> --ejecutar

ENTORNO="qa"
USUARIO_ESPERADO="crist668"
DOCROOT="/home3/crist668/sistemabase.webnia.cl"
RESPALDOS_DIR="/home3/crist668/respaldos/ticket_qa"
LOG_FILE="/home3/crist668/deploy_logs/ticket_qa.log"

source "$(dirname "$(readlink -f "$0")")/lib_deploy.sh" || exit 1
main_rollback "$@"
exit $?
