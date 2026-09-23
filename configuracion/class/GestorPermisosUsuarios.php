<?php

require_once dirname(__DIR__, 2) . '/clases/GestorAccesoModular.php';

class GestorPermisosUsuarios
{
    private $bdato;
    private $gestorAcceso;

    public function __construct($bdato)
    {
        $this->bdato = $bdato;
        $this->gestorAcceso = new GestorAccesoModular($bdato);
    }

    public function obtenerUsuariosAsociados($idUsuarioActual)
    {
        return $this->gestorAcceso->obtenerUsuariosAsociados($idUsuarioActual);
    }

    public function obtenerPermisosUsuario($idUsuario)
    {
        return $this->bdato->fetchAll(
            "SELECT pm.id, pm.id_usuario, pm.id_menu1 AS id_menu, pm.id_tipo_permiso
               FROM permisos_menu_1 pm
              WHERE pm.id_usuario = ?
           ORDER BY pm.id_menu1 ASC",
            [(int) $idUsuario]
        );
    }

    /**
     * Guarda permisos de menús y submenús para un usuario.
     *
     * Recibe desde $_POST:
     *   menus[]    → ids de menús principales marcados (checked)
     *   submenus[] → ids de submenús marcados (checked)
     *
     * Tablas que escribe:
     *   permisos_menu_1  (id, id_menu1, id_submenu, id_usuario, id_tipo_permiso)
     *   permiso_sub_menu (id_permiso_sub, id_usuario, id_submenu, permiso)
     */
    public function guardarPermisosUsuario(
        int $idUsuarioActual,
        int $idUsuario,
        array $menusChecked,
        array $submenusChecked
    ): array {
        // Super Admin (perfil 3) puede modificar a cualquiera.
        // Para otros perfiles se valida acceso normal.
        $perfilActual = $this->gestorAcceso->obtenerPerfil($idUsuarioActual);
        if ($perfilActual !== 3) {
            if (!$this->gestorAcceso->validarAccesoAUsuario($idUsuarioActual, $idUsuario)) {
                throw new RuntimeException('No tiene permiso para modificar permisos de este usuario.');
            }
        }

        // Normalizar a arrays de enteros positivos
        $menusChecked    = array_values(array_unique(array_filter(
            array_map('intval', $menusChecked),
            static fn (int $id): bool => $id > 0
        )));
        $submenusChecked = array_values(array_unique(array_filter(
            array_map('intval', $submenusChecked),
            static fn (int $id): bool => $id > 0
        )));

        // Obtener todos los submenús existentes para tener la lista completa
        $todosSubmenus = $this->bdato->fetchAll(
            "SELECT id_submenu, id_menu FROM menu_1_sub ORDER BY id_submenu ASC"
        );

        $procesados = 0;
        $errores    = [];

        // ── Iniciar transacción ──────────────────────────────────────────────
        $pdo = $this->bdato->getPDO();
        $pdo->beginTransaction();

        try {
            // 1. Limpiar permisos anteriores de menús principales
            $this->bdato->execute(
                "DELETE FROM permisos_menu_1 WHERE id_usuario = ?",
                [$idUsuario]
            );

            // 2. Insertar menús marcados en permisos_menu_1
            foreach ($menusChecked as $idMenu) {
                $this->bdato->execute(
                    "INSERT INTO permisos_menu_1 (id_menu1, id_submenu, id_usuario, id_tipo_permiso)
                     VALUES (?, NULL, ?, 1)",
                    [$idMenu, $idUsuario]
                );
                $procesados++;
            }

            // 3. Guardar submenús en permiso_sub_menu (UPSERT)
            //    Para cada submenú existente: permiso=1 si está chequeado, permiso=0 si no.
            $submenusCheckedSet = array_flip($submenusChecked); // para búsqueda O(1)

            foreach ($todosSubmenus as $submenu) {
                $idSubmenu = (int) $submenu['id_submenu'];
                $permiso   = isset($submenusCheckedSet[$idSubmenu]) ? 1 : 0;

                $this->bdato->execute(
                    "INSERT INTO permiso_sub_menu (id_usuario, id_submenu, permiso)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE permiso = VALUES(permiso)",
                    [$idUsuario, $idSubmenu, $permiso]
                );
                $procesados++;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw new RuntimeException('Error al guardar permisos: ' . $e->getMessage());
        }

        return [
            'procesados' => $procesados,
            'errores'    => $errores,
        ];
    }
}