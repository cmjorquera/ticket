@echo off
setlocal
set "SEDUC_AGENTDIR=%~dp0"
set "SELF=%~f0"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$c=[IO.File]::ReadAllText($env:SELF,[Text.Encoding]::UTF8);$m='::__SEDUC_PS__';& ([ScriptBlock]::Create($c.Substring($c.LastIndexOf($m)+$m.Length)))" -- "%SEDUC_AGENTDIR%" "%SELF%"
endlocal
exit /b

::__SEDUC_PS__
# ============================================================
#  SEDUC Inventario Agent v1.2
#  Agente nativo Windows -- sin Python ni dependencias externas
#  Compatible: Windows 7 SP1 / 8 / 10 / 11  (PowerShell 3.0+)
# ============================================================

# Recibir argumentos pasados desde el .bat
$argDir  = $args[0]
$argSelf = $args[1]

# --- AUTO-ELEVACION A ADMINISTRADOR -------------------------
$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    # Pasar la ruta original como argumento para que el proceso elevado la reciba
    $escaped = $argSelf -replace '"','\"'
    $dirEscaped = $argDir -replace '"','\"'
    Start-Process -FilePath 'cmd.exe' `
        -ArgumentList "/c `"$escaped`" `"$dirEscaped`"" `
        -Verb RunAs
    exit
}

# --- RESOLVER DIRECTORIO DE SALIDA --------------------------
# Prioridad: argumento recibido > variable de entorno > carpeta actual
$scriptDir = ''
if ($argDir -and (Test-Path $argDir)) {
    $scriptDir = $argDir.TrimEnd('\').TrimEnd('/')
}
if (-not $scriptDir -and $env:SEDUC_AGENTDIR -and (Test-Path $env:SEDUC_AGENTDIR)) {
    $scriptDir = ($env:SEDUC_AGENTDIR).TrimEnd('\').TrimEnd('/')
}
if (-not $scriptDir) {
    $scriptDir = (Get-Location).Path
}

$ErrorActionPreference = 'SilentlyContinue'

$SEP = '=' * 55
Write-Host ''
Write-Host $SEP
Write-Host '  SEDUC Inventario Agent v1.2'
Write-Host '  Recopilando informacion del equipo...'
Write-Host $SEP

# --- 1. EQUIPO -----------------------------------------------
Write-Host '[1/5] Datos del equipo...'
$cs   = Get-WmiObject Win32_ComputerSystem
$bios = Get-WmiObject Win32_BIOS
$nombreEquipo = "$env:COMPUTERNAME"
$fabricante   = if ($cs)   { "$($cs.Manufacturer)".Trim()   } else { '' }
$modelo       = if ($cs)   { "$($cs.Model)".Trim()          } else { '' }
$serial       = if ($bios) { "$($bios.SerialNumber)".Trim() } else { '' }
$invalidos    = @('to be filled by o.e.m.', 'none', 'system serial number', 'default string')
if ($invalidos -contains $serial.ToLower()) { $serial = '' }

# --- 2. PROCESADOR Y RAM ------------------------------------
Write-Host '[2/5] Procesador y RAM...'
$cpu        = Get-WmiObject Win32_Processor | Select-Object -First 1
$procNombre = if ($cpu) { "$($cpu.Name)".Trim() } else { '' }
$procVelMhz = [int]$(if ($cpu -and $cpu.MaxClockSpeed) { $cpu.MaxClockSpeed } else { 0 })
$procVelStr = if ($procVelMhz -gt 0) { ('{0:F1} GHz' -f ($procVelMhz / 1000.0)) } else { '' }
$procFab    = if     ($procNombre -match 'Intel') { 'Intel' }
              elseif ($procNombre -match 'AMD')   { 'AMD'   }
              else                                { ''      }

$ramInfo  = Get-WmiObject Win32_PhysicalMemory | Measure-Object -Property Capacity -Sum
$ramBytes = [long]$(if ($ramInfo -and $ramInfo.Sum) { $ramInfo.Sum } else { 0 })
$ramGb    = if ($ramBytes -gt 0) { [int][Math]::Round($ramBytes / 1073741824) } else { 0 }

# --- 3. ALMACENAMIENTO --------------------------------------
Write-Host '[3/5] Almacenamiento...'
$discos = @()
Get-WmiObject Win32_DiskDrive | ForEach-Object {
    $sizeBytes = [long]$(if ($_.Size) { $_.Size } else { 0 })
    $sizeGb    = if ($sizeBytes -gt 0) { [int][Math]::Round($sizeBytes / 1073741824) } else { 0 }
    if ($sizeGb -eq 0) { return }
    $cap    = if ($sizeGb -ge 950) { ('{0} TB' -f [int][Math]::Round($sizeGb / 1000.0)) } else { ('{0} GB' -f $sizeGb) }
    $mdisco = "$($_.Model)".Trim()
    $media  = "$($_.MediaType)".ToLower()
    $mlow   = $mdisco.ToLower()
    $tipo   = if     ($mlow -match 'nvme' -or $media -match 'nvme')                          { 'SSD NVMe' }
              elseif ($mlow -match 'ssd'  -or $media -match 'ssd' -or $media -match 'solid') { 'SSD'      }
              else                                                                             { 'HDD'      }
    $discos += [PSCustomObject]@{ modelo = $mdisco; capacidad = $cap; tipo = $tipo }
}

# --- 4. SISTEMA OPERATIVO ------------------------------------
Write-Host '[4/5] Sistema operativo...'
$so      = Get-WmiObject Win32_OperatingSystem
$windows = if ($so) { "$($so.Caption)".Trim() } else { '' }
$usuario = "$env:USERNAME"

# --- 5. MONITORES -------------------------------------------
Write-Host '[5/5] Monitores...'
$modelos = @()
Get-WmiObject -Namespace 'root\wmi' -Class WmiMonitorID | ForEach-Object {
    $mfg  = ($_.ManufacturerName | Where-Object { $_ } | ForEach-Object { [char]$_ }) -join ''
    $name = ($_.UserFriendlyName  | Where-Object { $_ } | ForEach-Object { [char]$_ }) -join ''
    $full = "$($mfg.Trim()) $($name.Trim())".Trim()
    if ($full) { $modelos += $full }
}

$resoluciones = @()
Add-Type -AssemblyName System.Windows.Forms
[System.Windows.Forms.Screen]::AllScreens | ForEach-Object {
    $resoluciones += "$($_.Bounds.Width)x$($_.Bounds.Height)"
}
if ($resoluciones.Count -eq 0) {
    Get-WmiObject Win32_VideoController | ForEach-Object {
        if ($_.CurrentHorizontalResolution -gt 0) {
            $resoluciones += "$($_.CurrentHorizontalResolution)x$($_.CurrentVerticalResolution)"
        }
    }
}

$total = [Math]::Max($modelos.Count, $resoluciones.Count)
if ($total -eq 0) { $total = 1 }
$monitores = @()
for ($i = 0; $i -lt $total; $i++) {
    $mod = if ($i -lt $modelos.Count) { $modelos[$i] } else { "Monitor $($i+1)" }
    $res = if ($i -lt $resoluciones.Count) { $resoluciones[$i] } else { '' }
    $monitores += [PSCustomObject]@{ modelo = $mod; resolucion = $res }
}

# --- CONSTRUIR JSON ------------------------------------------
$resultado = [ordered]@{
    nombre_equipo   = $nombreEquipo
    fabricante      = $fabricante
    modelo          = $modelo
    serial          = $serial
    procesador      = $procNombre
    procesador_fab  = $procFab
    procesador_vel  = $procVelStr
    ram_gb          = $ramGb
    windows         = $windows
    usuario_windows = $usuario
    almacenamiento  = [array]@($discos)
    monitores       = [array]@($monitores)
    generado_en     = (Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
    version_agente  = '1.2'
}

$nombreArchivo = "inventario_${nombreEquipo}.json"
$rutaSalida    = Join-Path $scriptDir $nombreArchivo

$json = ConvertTo-Json -InputObject $resultado -Depth 10
[IO.File]::WriteAllText($rutaSalida, $json, [Text.Encoding]::UTF8)

Write-Host ''
Write-Host "  Archivo generado: $nombreArchivo"
Write-Host "  Guardado en:      $scriptDir"
Write-Host ''
Write-Host "  Equipo:      $nombreEquipo"
Write-Host "  Fabricante:  $fabricante $modelo"
Write-Host "  Serie:       $(if ($serial) { $serial } else { '(no detectado)' })"
Write-Host "  Procesador:  $procNombre"
Write-Host "  RAM:         $ramGb GB"
$discStr = ($discos | ForEach-Object { "$($_.tipo) $($_.capacidad)" }) -join ', '
Write-Host "  Discos:      $($discos.Count)$(if ($discStr) { ' (' + $discStr + ')' })"
Write-Host "  SO:          $windows"
Write-Host "  Monitores:   $($monitores.Count)"
Write-Host ''
Write-Host '  Suba este archivo al sistema SEDUC para completar'
Write-Host '  el inventario automaticamente.'
Write-Host $SEP
Write-Host ''
Read-Host '  Presione ENTER para cerrar'