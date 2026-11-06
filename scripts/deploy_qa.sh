#!/usr/bin/env bash
# Despliegue de QA: publica la rama 'qa' en https://sistemabase.webnia.cl/
# Uso:  ~/repositories/ticket/scripts/deploy_qa.sh --dry-run
#       ~/repositories/ticket/scripts/deploy_qa.sh --ejecutar

ENTORNO="qa"
RAMA="qa"
USUARIO_ESPERADO="crist668"
HOME_ESPERADO="/home3/crist668"
DOCROOT="/home3/crist668/sistemabase.webnia.cl"
REPO_DIR="/home3/crist668/repositories/ticket"
RESPALDOS_DIR="/home3/crist668/respaldos/ticket_qa"
LOG_FILE="/home3/crist668/deploy_logs/ticket_qa.log"

source "$(dirname "$(readlink -f "$0")")/lib_deploy.sh" || exit 1
main_deploy "$@"
exit $?
