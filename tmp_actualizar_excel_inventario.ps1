$ErrorActionPreference = 'Stop'

$excelPath = 'C:\Users\cmjor\Downloads\plantilla_inventario_Seduc_SpA_20260619.xlsx'
$usuariosSql = 'C:\Users\cmjor\Downloads\usuarios (2).sql'
$backupPath = 'C:\Users\cmjor\Downloads\plantilla_inventario_Seduc_SpA_20260619.bak.xlsx'

if (-not (Test-Path -LiteralPath $excelPath)) {
    throw "No existe el archivo Excel: $excelPath"
}
if (-not (Test-Path -LiteralPath $usuariosSql)) {
    throw "No existe el SQL de usuarios: $usuariosSql"
}
if (-not (Test-Path -LiteralPath $backupPath)) {
    Copy-Item -LiteralPath $excelPath -Destination $backupPath
}

$tiposPc = @('Desktop', 'Notebook', 'All In One', 'Mini PC', 'Servidor', 'Otro')

$sql = Get-Content -LiteralPath $usuariosSql -Raw
$usuarios = New-Object System.Collections.Generic.List[object]
$usuarios.Add([pscustomobject]@{ Id = 0; Nombre = 'Sin asignar'; Seleccion = '0 - Sin asignar' })
$regex = [regex]"\((\d+), '((?:''|[^'])*)', '((?:''|[^'])*)', '((?:''|[^'])*)',"
foreach ($m in $regex.Matches($sql)) {
    $id = [int]$m.Groups[1].Value
    $nombre = ($m.Groups[2].Value -replace "''", "'").Trim()
    $apPat = ($m.Groups[3].Value -replace "''", "'").Trim()
    $apMat = ($m.Groups[4].Value -replace "''", "'").Trim()
    $completo = (($nombre, $apPat, $apMat) | Where-Object { $_ -ne '' }) -join ' '
    $usuarios.Add([pscustomobject]@{ Id = $id; Nombre = $completo; Seleccion = "$id - $completo" })
}

$headers = @(
    'Usuario asignado',
    'Fabricante',
    'Producto / modelo',
    'Numero de serie',
    'Tipo de PC',
    'Estado',
    'Valor equipo',
    'Proveedor',
    'Numero factura',
    'Fecha compra',
    'Observacion compra',
    'Almacenamiento modelo',
    'Almacenamiento capacidad',
    'Almacenamiento tipo/tamano',
    'Procesador fabricante',
    'Procesador modelo',
    'Procesador velocidad',
    'Windows',
    'Office',
    'Antivirus',
    'Memoria slot/designacion',
    'Memoria formato',
    'Memoria tipo',
    'Memoria tamano',
    'Memoria frecuencia',
    'Memoria marca',
    'Monitor modelo',
    'Monitor codigo',
    'Monitor serie',
    'Monitor tamano',
    'Monitor resolucion'
)

$example = @(
    '0 - Sin asignar',
    'Dell',
    'OptiPlex 3080',
    'SN-12345ABC',
    'Desktop',
    '1 - Activo',
    '350000',
    'TechShop Ltda',
    'FAC-2024-001',
    '2024-01-15',
    'Adquisicion primer semestre',
    'Samsung 860 EVO',
    '256GB',
    '2.5"',
    'Intel',
    'Core i5-10500',
    '3.1 GHz',
    'Windows 10 Pro',
    'Office 2021',
    'Windows Defender',
    'DIMM1',
    'DIMM',
    'DDR4',
    '8GB',
    '2666 MHz',
    'Kingston',
    'Dell P2219H',
    'MON-001',
    'SN-MON-001',
    '22"',
    '1920x1080'
)

$xlValidateList = 3
$xlValidAlertStop = 1
$xlBetween = 1
$xlUp = -4162

$excel = $null
$wb = $null
try {
    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $excel.ScreenUpdating = $false
    $excel.EnableEvents = $false

    $wb = $excel.Workbooks.Open($excelPath)
    $sheet = $wb.Worksheets.Item('Equipos')
    Write-Output 'Abierto'

    $currentB = [string]$sheet.Range('B6').Value2
    $currentG = [string]$sheet.Range('G6').Value2
    if ($currentG -like '*Codigo QR*') {
        $sheet.Columns.Item(7).Delete()
    }
    if ($currentB -like '*Nombre equipo*') {
        $sheet.Columns.Item(2).Delete()
    }

    for ($i = 0; $i -lt $headers.Count; $i++) {
        $col = $i + 1
        $sheet.Cells.Item(6, $col).Value2 = $headers[$i]
        $sheet.Cells.Item(7, $col).Value2 = $example[$i]
    }
    Write-Output 'Columnas actualizadas'
    $sheet.Columns.Item(1).ColumnWidth = 42
    $sheet.Columns.Item(5).ColumnWidth = 18
    $sheet.Columns.Item(6).ColumnWidth = 22

    foreach ($name in @('Tipos PC', 'Usuarios')) {
        try {
            $wb.Worksheets.Item($name).Delete()
        } catch {
        }
    }
    Write-Output 'Hojas antiguas limpiadas'

    $sheetEstados = $wb.Worksheets.Item('Estados')
    $sheetEstados.Cells.Item(1, 3).Value2 = 'seleccion_excel'
    $lastEstado = $sheetEstados.Cells.Item($sheetEstados.Rows.Count, 1).End($xlUp).Row
    if ($lastEstado -lt 2) {
        $defaults = @(
            @(1, 'Activo'),
            @(2, 'Bodega'),
            @(3, 'Reparacion'),
            @(4, 'Baja'),
            @(5, 'Prestado')
        )
        $row = 2
        foreach ($estado in $defaults) {
            $sheetEstados.Cells.Item($row, 1).Value2 = $estado[0]
            $sheetEstados.Cells.Item($row, 2).Value2 = $estado[1]
            $row++
        }
        $lastEstado = 6
    }
    for ($row = 2; $row -le $lastEstado; $row++) {
        $idEstado = [int]$sheetEstados.Cells.Item($row, 1).Value2
        $nombreEstado = [string]$sheetEstados.Cells.Item($row, 2).Value2
        $sheetEstados.Cells.Item($row, 3).Value2 = "$idEstado - $nombreEstado"
    }
    $sheetEstados.Columns.Item(3).ColumnWidth = 35

    $sheetTipos = $wb.Worksheets.Add([System.Type]::Missing, $wb.Worksheets.Item($wb.Worksheets.Count))
    $sheetTipos.Name = 'Tipos PC'
    $sheetTipos.Cells.Item(1, 1).Value2 = 'tipo_pc'
    for ($i = 0; $i -lt $tiposPc.Count; $i++) {
        $sheetTipos.Cells.Item($i + 2, 1).Value2 = $tiposPc[$i]
    }
    $sheetTipos.Columns.Item(1).ColumnWidth = 25
    Write-Output 'Tipos PC cargados'

    $sheetUsuarios = $wb.Worksheets.Add([System.Type]::Missing, $wb.Worksheets.Item($wb.Worksheets.Count))
    $sheetUsuarios.Name = 'Usuarios'
    $sheetUsuarios.Cells.Item(1, 1).Value2 = 'id_usuario'
    $sheetUsuarios.Cells.Item(1, 2).Value2 = 'nombre_usuario'
    $sheetUsuarios.Cells.Item(1, 3).Value2 = 'seleccion_excel'
    for ($i = 0; $i -lt $usuarios.Count; $i++) {
        $row = $i + 2
        $sheetUsuarios.Cells.Item($row, 1).Value2 = $usuarios[$i].Id
        $sheetUsuarios.Cells.Item($row, 2).Value2 = $usuarios[$i].Nombre
        $sheetUsuarios.Cells.Item($row, 3).Value2 = $usuarios[$i].Seleccion
    }
    $sheetUsuarios.Columns.Item(1).ColumnWidth = 14
    $sheetUsuarios.Columns.Item(2).ColumnWidth = 42
    $sheetUsuarios.Columns.Item(3).ColumnWidth = 52
    Write-Output 'Usuarios cargados'

    $lastTipo = $tiposPc.Count + 1
    $lastUsuario = $usuarios.Count + 1
    $validations = @(
        @{ Range = 'A7:A250'; Formula = "=Usuarios!`$C`$2:`$C`$$lastUsuario"; Blank = $true },
        @{ Range = 'E7:E250'; Formula = "='Tipos PC'!`$A`$2:`$A`$$lastTipo"; Blank = $false },
        @{ Range = 'F7:F250'; Formula = "=Estados!`$C`$2:`$C`$$lastEstado"; Blank = $false }
    )

    foreach ($validation in $validations) {
        $target = $sheet.Range($validation.Range)
        $target.Validation.Delete()
        $target.Validation.Add($xlValidateList, $xlValidAlertStop, $xlBetween, $validation.Formula)
        $target.Validation.IgnoreBlank = [bool]$validation.Blank
        $target.Validation.InCellDropdown = $true
        $target.Validation.InputTitle = 'Seleccione de la lista'
        $target.Validation.InputMessage = 'Use el desplegable para elegir un valor disponible.'
        $target.Validation.ErrorTitle = 'Valor no valido'
        $target.Validation.ErrorMessage = 'Seleccione un valor de la lista.'
        $target.Validation.ShowInput = $true
        $target.Validation.ShowError = $true
    }
    Write-Output 'Validaciones aplicadas'

    $sheet.Activate() | Out-Null
    $wb.Save()
    Write-Output "OK: $excelPath"
    Write-Output "Backup: $backupPath"
    Write-Output "Usuarios: $($usuarios.Count)"
    Write-Output "Tipos PC: $($tiposPc.Count)"
    Write-Output "Estados: $($lastEstado - 1)"
} finally {
    if ($wb -ne $null) {
        $wb.Close($true)
        [Runtime.InteropServices.Marshal]::ReleaseComObject($wb) | Out-Null
    }
    if ($excel -ne $null) {
        $excel.Quit()
        [Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
