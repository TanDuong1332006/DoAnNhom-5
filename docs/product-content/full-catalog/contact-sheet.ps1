param([int]$Start = 1, [int]$End = 24, [int[]]$Ids = @())
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$files = @(Get-ChildItem (Join-Path $PSScriptRoot 'assets/*.jpg') | Where-Object { if ($Ids.Count) { $Ids -contains [int]$_.BaseName } else { [int]$_.BaseName -ge $Start -and [int]$_.BaseName -le $End } } | Sort-Object { [int]$_.BaseName })
if (!$files.Count) { throw 'No assets in selected range' }
$sheet = [System.Drawing.Bitmap]::new(1440, [int]([Math]::Ceiling($files.Count / 6.0) * 270))
$graphics = [System.Drawing.Graphics]::FromImage($sheet)
$graphics.Clear([System.Drawing.Color]::White)
$font = [System.Drawing.Font]::new('Arial', 12)
$i = 0
try {
    foreach ($file in $files) {
        $image = [System.Drawing.Image]::FromFile($file.FullName)
        $x = ($i % 6) * 240; $y = [Math]::Floor($i / 6) * 270
        try { $graphics.DrawImage($image, [int]$x, [int]$y, 240, 240) } finally { $image.Dispose() }
        $graphics.DrawString($file.BaseName, $font, [System.Drawing.Brushes]::Black, [float]$x, [float]($y + 243))
        $i++
    }
    $sheet.Save((Join-Path $PSScriptRoot "review-$Start-$End.jpg"), [System.Drawing.Imaging.ImageFormat]::Jpeg)
} finally { $graphics.Dispose(); $sheet.Dispose(); $font.Dispose() }
