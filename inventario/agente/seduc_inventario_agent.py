#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SEDUC Inventario Agent v1.0
Recopila informacion de hardware del equipo Windows y genera un archivo JSON
listo para importar en el sistema SEDUC.

Sin dependencias externas. Requiere PowerShell (incluido en Windows 7+).

Compilar a ejecutable:
    pip install pyinstaller
    pyinstaller --onefile --console seduc_inventario_agent.py
"""

import ctypes
import json
import os
import socket
import subprocess
import sys
from datetime import datetime


# ---------------------------------------------------------------------------
# Utilidades de ejecucion
# ---------------------------------------------------------------------------

def _run_ps(command: str, timeout: int = 20) -> str:
    """Ejecuta un fragmento PowerShell y retorna stdout como string limpio."""
    try:
        result = subprocess.run(
            ["powershell", "-NoProfile", "-NonInteractive", "-Command", command],
            capture_output=True, text=True, timeout=timeout,
            creationflags=subprocess.CREATE_NO_WINDOW if sys.platform == "win32" else 0
        )
        return result.stdout.strip()
    except Exception:
        return ""


# ---------------------------------------------------------------------------
# Recopilacion de datos
# ---------------------------------------------------------------------------

def get_computer_info() -> dict:
    """Nombre del equipo, fabricante, modelo y numero de serie."""
    nombre    = socket.gethostname()
    fabricante = _run_ps("(Get-WmiObject Win32_ComputerSystem).Manufacturer")
    modelo     = _run_ps("(Get-WmiObject Win32_ComputerSystem).Model")
    serial     = _run_ps("(Get-WmiObject Win32_BIOS).SerialNumber")

    # Limpiar valores genericos de BIOS
    serial_limpio = serial.strip()
    if serial_limpio.lower() in ("", "to be filled by o.e.m.", "none", "system serial number", "default string"):
        serial_limpio = ""

    return {
        "nombre":    nombre.strip(),
        "fabricante": fabricante.strip(),
        "modelo":    modelo.strip(),
        "serial":    serial_limpio,
    }


def get_processor() -> dict:
    """Nombre, fabricante y velocidad del procesador."""
    nombre    = _run_ps("(Get-WmiObject Win32_Processor | Select-Object -First 1).Name")
    velocidad = _run_ps("(Get-WmiObject Win32_Processor | Select-Object -First 1).MaxClockSpeed")

    velocidad_str = ""
    try:
        mhz = int(velocidad)
        ghz = mhz / 1000
        velocidad_str = f"{ghz:.1f} GHz"
    except (ValueError, TypeError):
        pass

    nombre_limpio = nombre.strip()
    fabricante_proc = ""
    lower = nombre_limpio.lower()
    if "intel" in lower:
        fabricante_proc = "Intel"
    elif "amd" in lower:
        fabricante_proc = "AMD"

    return {
        "nombre":     nombre_limpio,
        "fabricante": fabricante_proc,
        "velocidad":  velocidad_str,
    }


def get_ram_gb() -> int:
    """RAM fisica total en GB."""
    total_str = _run_ps(
        "(Get-WmiObject Win32_PhysicalMemory | Measure-Object Capacity -Sum).Sum"
    )
    try:
        return round(int(total_str) / (1024 ** 3))
    except (ValueError, TypeError):
        pass

    # Fallback via GlobalMemoryStatusEx
    try:
        class MEMSTATEX(ctypes.Structure):
            _fields_ = [
                ("dwLength",                ctypes.c_ulong),
                ("dwMemoryLoad",            ctypes.c_ulong),
                ("ullTotalPhys",            ctypes.c_ulonglong),
                ("ullAvailPhys",            ctypes.c_ulonglong),
                ("ullTotalPageFile",        ctypes.c_ulonglong),
                ("ullAvailPageFile",        ctypes.c_ulonglong),
                ("ullTotalVirtual",         ctypes.c_ulonglong),
                ("ullAvailVirtual",         ctypes.c_ulonglong),
                ("ullAvailExtendedVirtual", ctypes.c_ulonglong),
            ]
        stat = MEMSTATEX()
        stat.dwLength = ctypes.sizeof(stat)
        ctypes.windll.kernel32.GlobalMemoryStatusEx(ctypes.byref(stat))
        return round(stat.ullTotalPhys / (1024 ** 3))
    except Exception:
        return 0


def get_disks() -> list:
    """Lista de discos con modelo, capacidad y tipo (SSD/HDD/NVMe)."""
    discos = []
    output = _run_ps(
        "Get-WmiObject Win32_DiskDrive | "
        "Select-Object Size,Model,MediaType | "
        "ConvertTo-Json -Compress"
    )
    if not output:
        return discos

    try:
        data = json.loads(output)
        if isinstance(data, dict):
            data = [data]
    except json.JSONDecodeError:
        return discos

    for d in data:
        try:
            size_gb = round(int(d.get("Size") or 0) / (1024 ** 3))
        except (ValueError, TypeError):
            size_gb = 0

        if size_gb == 0:
            continue

        capacidad = f"{round(size_gb / 1000)} TB" if size_gb >= 950 else f"{size_gb} GB"

        modelo_d    = (d.get("Model") or "").strip()
        media_type  = (d.get("MediaType") or "").lower()
        modelo_low  = modelo_d.lower()

        if "nvme" in modelo_low or "nvme" in media_type:
            tipo = "SSD NVMe"
        elif "ssd" in modelo_low or "ssd" in media_type or "solid" in media_type:
            tipo = "SSD"
        else:
            tipo = "HDD"

        discos.append({
            "modelo":    modelo_d,
            "capacidad": capacidad,
            "tipo":      tipo,
        })

    return discos


def get_os_info() -> dict:
    """Caption del SO y usuario de sesion actual."""
    caption = _run_ps("(Get-WmiObject Win32_OperatingSystem).Caption")
    usuario = _run_ps("$env:USERNAME")
    return {
        "windows": caption.strip(),
        "usuario": usuario.strip(),
    }


def get_monitor_resolutions() -> list:
    """Resoluciones de cada monitor via EnumDisplayMonitors (ctypes, sin deps)."""
    resoluciones = []
    try:
        from ctypes.wintypes import BOOL, HDC, LPRECT, LPARAM

        MonitorEnumProc = ctypes.WINFUNCTYPE(BOOL, ctypes.c_void_p, HDC, LPRECT, LPARAM)

        class RECT(ctypes.Structure):
            _fields_ = [("left", ctypes.c_long), ("top", ctypes.c_long),
                        ("right", ctypes.c_long), ("bottom", ctypes.c_long)]

        def _callback(hMon, hdcMon, lprcMon, dwData):
            r = lprcMon.contents
            w = r.right - r.left
            h = r.bottom - r.top
            if w > 0 and h > 0:
                resoluciones.append(f"{w}x{h}")
            return 1

        ctypes.windll.user32.EnumDisplayMonitors(
            None, None, MonitorEnumProc(_callback), 0
        )
    except Exception:
        # Fallback: resolucion primaria
        try:
            w = ctypes.windll.user32.GetSystemMetrics(0)
            h = ctypes.windll.user32.GetSystemMetrics(1)
            if w > 0 and h > 0:
                resoluciones.append(f"{w}x{h}")
        except Exception:
            pass

    return resoluciones


def get_monitors() -> list:
    """Modelos de monitores via WMI + resoluciones via ctypes."""
    monitores = []

    # Modelos: WmiMonitorID en root\wmi
    output = _run_ps(
        "try {"
        "  Get-WmiObject WmiMonitorID -Namespace root\\wmi |"
        "  ForEach-Object {"
        "    $mfg  = ($_.ManufacturerName | Where-Object {$_} | ForEach-Object {[char][int]$_}) -join '';"
        "    $name = ($_.UserFriendlyName  | Where-Object {$_} | ForEach-Object {[char][int]$_}) -join '';"
        "    \"$($mfg.Trim())|$($name.Trim())\""
        "  }"
        "} catch {}"
    )

    modelos = []
    if output:
        for linea in output.splitlines():
            linea = linea.strip()
            if "|" in linea:
                mfg, name = linea.split("|", 1)
                completo  = f"{mfg.strip()} {name.strip()}".strip()
                if completo:
                    modelos.append(completo)

    resoluciones = get_monitor_resolutions()
    total = max(len(modelos), len(resoluciones))
    if total == 0:
        total = 1  # al menos un monitor siempre hay

    for i in range(total):
        monitores.append({
            "modelo":     modelos[i] if i < len(modelos) else f"Monitor {i + 1}",
            "resolucion": resoluciones[i] if i < len(resoluciones) else "",
        })

    return monitores


# ---------------------------------------------------------------------------
# Punto de entrada
# ---------------------------------------------------------------------------

def main():
    SEP = "=" * 55

    print(SEP)
    print("  SEDUC Inventario Agent v1.0")
    print("  Recopilando informacion del equipo...")
    print(SEP)

    print("[1/5] Datos del equipo...")
    equipo = get_computer_info()

    print("[2/5] Procesador y RAM...")
    proc   = get_processor()
    ram_gb = get_ram_gb()

    print("[3/5] Almacenamiento...")
    discos = get_disks()

    print("[4/5] Sistema operativo...")
    so = get_os_info()

    print("[5/5] Monitores...")
    monitores = get_monitors()

    resultado = {
        "nombre_equipo":   equipo["nombre"],
        "fabricante":      equipo["fabricante"],
        "modelo":          equipo["modelo"],
        "serial":          equipo["serial"],
        "procesador":      proc["nombre"],
        "procesador_fab":  proc["fabricante"],
        "procesador_vel":  proc["velocidad"],
        "ram_gb":          ram_gb,
        "windows":         so["windows"],
        "usuario_windows": so["usuario"],
        "almacenamiento":  discos,
        "monitores":       monitores,
        "generado_en":     datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "version_agente":  "1.0",
    }

    nombre_archivo = f"inventario_{equipo['nombre']}.json"
    with open(nombre_archivo, "w", encoding="utf-8") as f:
        json.dump(resultado, f, ensure_ascii=False, indent=2)

    print()
    print(f"  Archivo generado: {nombre_archivo}")
    print()
    print(f"  Equipo:      {equipo['nombre']}")
    print(f"  Fabricante:  {equipo['fabricante']} {equipo['modelo']}")
    print(f"  Serie:       {equipo['serial'] or '(no detectado)'}")
    print(f"  Procesador:  {proc['nombre']}")
    print(f"  RAM:         {ram_gb} GB")
    print(f"  Discos:      {len(discos)} ({', '.join(d['tipo'] + ' ' + d['capacidad'] for d in discos)})")
    print(f"  SO:          {so['windows']}")
    print(f"  Monitores:   {len(monitores)}")
    print()
    print("  Suba este archivo al sistema SEDUC para completar")
    print("  el inventario automaticamente.")
    print(SEP)
    input("\n  Presione ENTER para cerrar...")


if __name__ == "__main__":
    main()
