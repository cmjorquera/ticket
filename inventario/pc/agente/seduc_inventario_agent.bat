@echo off
setlocal
set "BAT_PATH=%~f0"

net session >nul 2>&1
if %errorlevel% equ 0 goto :ESADMIN

powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -Command "Start-Process -FilePath '%~f0' -Verb RunAs -WindowStyle Normal"
exit /b

:ESADMIN
set "TMPPS=%TEMP%\seduc_agent.ps1"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$f='%BAT_PATH%'; $bat=[IO.File]::ReadAllText($f,[Text.Encoding]::UTF8); $m='##PS1START##'; [IO.File]::WriteAllText($env:TMPPS, $bat.Substring($bat.LastIndexOf($m)+$m.Length), [Text.Encoding]::UTF8)"
powershell -NoProfile -ExecutionPolicy Bypass -File "%TMPPS%"
del "%TMPPS%" >nul 2>&1
endlocal
exit /b

##PS1START##
$ErrorActionPreference = "SilentlyContinue"
$SEP = "=" * 55
Write-Host ""
Write-Host $SEP
Write-Host "  SEDUC Inventario Agent v1.7"
Write-Host "  Recopilando informacion del equipo..."
Write-Host $SEP

function Get-WmiOrWmic {
    param($WmiClass, $Property, $WmicAlias, $WmicProperty)
    $val = $null
    try { $val = (Get-WmiObject $WmiClass).$Property } catch {}
    if (-not $val) {
        try {
            $out = (wmic $WmicAlias get $WmicProperty /value 2>$null) -join ""
            $val = ($out -split "=", 2)[1].Trim()
        } catch {}
    }
    return if ($val) { $val.Trim() } else { "" }
}

Write-Host "[1/5] Datos del equipo..."
$nombreEquipo = $env:COMPUTERNAME
$fabricante   = Get-WmiOrWmic -WmiClass Win32_ComputerSystem -Property Manufacturer -WmicAlias computersystem -WmicProperty Manufacturer
$modelo       = Get-WmiOrWmic -WmiClass Win32_ComputerSystem -Property Model        -WmicAlias computersystem -WmicProperty Model
$serial       = Get-WmiOrWmic -WmiClass Win32_BIOS           -Property SerialNumber  -WmicAlias bios           -WmicProperty SerialNumber
$invalidos    = @("to be filled by o.e.m.", "none", "system serial number", "default string")
if ($invalidos -contains $serial.ToLower()) { $serial = "" }

Write-Host "[2/5] Procesador y RAM..."
$procNombre = Get-WmiOrWmic -WmiClass Win32_Processor -Property Name          -WmicAlias cpu -WmicProperty Name
$velRaw     = Get-WmiOrWmic -WmiClass Win32_Processor -Property MaxClockSpeed -WmicAlias cpu -WmicProperty MaxClockSpeed
$velClean   = $velRaw -replace "[^0-9]", ""
$procVelMhz = if ($velClean) { [int]$velClean } else { 0 }
$procVelStr = if ($procVelMhz -gt 0) { "{0:F1} GHz" -f ($procVelMhz / 1000.0) } else { "" }
$procFab    = if ($procNombre -match "Intel") { "Intel" } elseif ($procNombre -match "AMD") { "AMD" } else { "" }
$ramBytes   = [long]0
try {
    $ramInfo  = Get-WmiObject Win32_PhysicalMemory | Measure-Object -Property Capacity -Sum
    $ramBytes = [long](if ($ramInfo -and $ramInfo.Sum) { $ramInfo.Sum } else { 0 })
} catch {}
if ($ramBytes -eq 0) {
    try {
        $wmicMem = wmic memorychip get Capacity /value 2>$null
        foreach ($mline in $wmicMem) {
            if ($mline -match "^Capacity=") {
                $cs = ($mline -split "=", 2)[1].Trim()
                if ($cs) { $ramBytes += [long]$cs }
            }
        }
    } catch {}
}
$ramGb = if ($ramBytes -gt 0) { [int][Math]::Round($ramBytes / 1073741824) } else { 0 }

Write-Host "[3/5] Almacenamiento..."
$discos = @()
try {
    Get-WmiObject Win32_DiskDrive | ForEach-Object {
        $sg = [long](if ($_.Size) { $_.Size } else { 0 })
        $gb = if ($sg -gt 0) { [int][Math]::Round($sg / 1073741824) } else { 0 }
        if ($gb -eq 0) { return }
        $cap  = if ($gb -ge 950) { "{0} TB" -f [int][Math]::Round($gb / 1000.0) } else { "{0} GB" -f $gb }
        $md   = if ($_.Model)     { $_.Model.Trim()        } else { "" }
        $mt   = if ($_.MediaType) { $_.MediaType.ToLower() } else { "" }
        $ml   = $md.ToLower()
        $tipo = if ($ml -match "nvme" -or $mt -match "nvme") { "SSD NVMe" } elseif ($ml -match "ssd" -or $mt -match "ssd" -or $mt -match "solid") { "SSD" } else { "HDD" }
        $discos += [PSCustomObject]@{ modelo = $md; capacidad = $cap; tipo = $tipo }
    }
} catch {}
if ($discos.Count -eq 0) {
    try {
        $wmicDisk = wmic diskdrive get Model,Size,MediaType /value 2>$null
        $cur      = @{}
        foreach ($dline in $wmicDisk) {
            if ($dline -match "^(\w+)=(.*)") {
                $cur[$Matches[1]] = $Matches[2].Trim()
            } elseif ($dline.Trim() -eq "" -and $cur.Count -gt 0) {
                $ss = $cur["Size"] -replace "[^0-9]", ""
                $sg = if ($ss) { [long]$ss } else { [long]0 }
                $gb = if ($sg -gt 0) { [int][Math]::Round($sg / 1073741824) } else { 0 }
                if ($gb -gt 0) {
                    $cap  = if ($gb -ge 950) { "{0} TB" -f [int][Math]::Round($gb / 1000.0) } else { "{0} GB" -f $gb }
                    $md   = if ($cur["Model"])     { $cur["Model"]               } else { "" }
                    $mt   = if ($cur["MediaType"]) { $cur["MediaType"].ToLower() } else { "" }
                    $ml   = $md.ToLower()
                    $tipo = if ($ml -match "nvme" -or $mt -match "nvme") { "SSD NVMe" } elseif ($ml -match "ssd" -or $mt -match "ssd" -or $mt -match "solid") { "SSD" } else { "HDD" }
                    $discos += [PSCustomObject]@{ modelo = $md; capacidad = $cap; tipo = $tipo }
                }
                $cur = @{}
            }
        }
        if ($cur.Count -gt 0) {
            $ss = $cur["Size"] -replace "[^0-9]", ""
            $sg = if ($ss) { [long]$ss } else { [long]0 }
            $gb = if ($sg -gt 0) { [int][Math]::Round($sg / 1073741824) } else { 0 }
            if ($gb -gt 0) {
                $cap  = if ($gb -ge 950) { "{0} TB" -f [int][Math]::Round($gb / 1000.0) } else { "{0} GB" -f $gb }
                $md   = if ($cur["Model"])     { $cur["Model"]               } else { "" }
                $mt   = if ($cur["MediaType"]) { $cur["MediaType"].ToLower() } else { "" }
                $ml   = $md.ToLower()
                $tipo = if ($ml -match "nvme" -or $mt -match "nvme") { "SSD NVMe" } elseif ($ml -match "ssd" -or $mt -match "ssd" -or $mt -match "solid") { "SSD" } else { "HDD" }
                $discos += [PSCustomObject]@{ modelo = $md; capacidad = $cap; tipo = $tipo }
            }
        }
    } catch {}
}

Write-Host "[4/5] Sistema operativo..."
$windows = Get-WmiOrWmic -WmiClass Win32_OperatingSystem -Property Caption -WmicAlias os -WmicProperty Caption
$usuario = $env:USERNAME

Write-Host "[5/5] Monitores..."
$modelos = @()
try {
    Get-WmiObject -Namespace "root\wmi" -Class WmiMonitorID | ForEach-Object {
        $mfg  = ($_.ManufacturerName | Where-Object { $_ } | ForEach-Object { [char]$_ }) -join ""
        $name = ($_.UserFriendlyName  | Where-Object { $_ } | ForEach-Object { [char]$_ }) -join ""
        $full = ($mfg.Trim() + " " + $name.Trim()).Trim()
        if ($full) { $modelos += $full }
    }
} catch {}
$resoluciones = @()
try {
    Add-Type -AssemblyName System.Windows.Forms
    [System.Windows.Forms.Screen]::AllScreens | ForEach-Object {
        $resoluciones += ("{0}x{1}" -f $_.Bounds.Width, $_.Bounds.Height)
    }
} catch {}
if ($resoluciones.Count -eq 0) {
    try {
        Get-WmiObject Win32_VideoController | ForEach-Object {
            if ($_.CurrentHorizontalResolution -gt 0) {
                $resoluciones += ("{0}x{1}" -f $_.CurrentHorizontalResolution, $_.CurrentVerticalResolution)
            }
        }
    } catch {}
}
$total = [Math]::Max($modelos.Count, $resoluciones.Count)
if ($total -eq 0) { $total = 1 }
$monitores = @()
for ($i = 0; $i -lt $total; $i++) {
    $mod = if ($i -lt $modelos.Count)      { $modelos[$i]      } else { "Monitor " + ($i + 1) }
    $res = if ($i -lt $resoluciones.Count) { $resoluciones[$i] } else { "" }
    $monitores += [PSCustomObject]@{ modelo = $mod; resolucion = $res }
}

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
    generado_en     = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
    version_agente  = "1.7"
}

$dir = [Environment]::GetFolderPath("Desktop")
if (-not $dir -or -not (Test-Path $dir)) { $dir = $env:USERPROFILE + "\Desktop" }
if (-not $dir -or -not (Test-Path $dir)) { $dir = $env:TEMP }

$nombreArchivo = "inventario_" + $nombreEquipo + ".json"
$rutaSalida    = Join-Path $dir $nombreArchivo
$json          = ConvertTo-Json -InputObject $resultado -Depth 10
[IO.File]::WriteAllText($rutaSalida, $json, [Text.Encoding]::UTF8)

Write-Host ""
Write-Host ("  Archivo generado: " + $nombreArchivo)
Write-Host ("  Guardado en:      " + $dir)
Write-Host ""
Write-Host ("  Equipo:      " + $nombreEquipo)
Write-Host ("  Fabricante:  " + $fabricante + " " + $modelo)
Write-Host ("  Serie:       " + $(if ($serial) { $serial } else { "(no detectado)" }))
Write-Host ("  Procesador:  " + $procNombre)
Write-Host ("  RAM:         " + $ramGb + " GB")
$discStr = ($discos | ForEach-Object { $_.tipo + " " + $_.capacidad }) -join ", "
Write-Host ("  Discos:      " + $discos.Count + $(if ($discStr) { " (" + $discStr + ")" }))
Write-Host ("  SO:          " + $windows)
Write-Host ("  Monitores:   " + $monitores.Count)
Write-Host ""
Write-Host "  Suba este archivo al sistema SEDUC para completar"
Write-Host "  el inventario automaticamente."
Write-Host $SEP
Write-Host ""
Read-Host "  Presione ENTER para cerrar"
