param([int[]]$ForceIds = @())
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$assetRoot = Join-Path $PSScriptRoot 'assets'
$encoder = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object MimeType -eq 'image/jpeg'
$parameters = [System.Drawing.Imaging.EncoderParameters]::new(1)
$parameters.Param[0] = [System.Drawing.Imaging.EncoderParameter]::new([System.Drawing.Imaging.Encoder]::Quality, [long]88)
foreach ($file in Get-ChildItem -LiteralPath $assetRoot -Filter '*.png') {
    if ($file.BaseName -notmatch '^\d+$') { continue }
    $destination = [System.IO.Path]::ChangeExtension($file.FullName, '.jpg')
    if (!(Test-Path -LiteralPath $destination) -or $ForceIds -contains [int]$file.BaseName) {
        $image = [System.Drawing.Image]::FromFile($file.FullName)
        try { $image.Save($destination, $encoder, $parameters) } finally { $image.Dispose() }
    }
    $metadataPath = [System.IO.Path]::ChangeExtension($file.FullName, '.json')
    $metadata = Get-Content -LiteralPath $metadataPath -Raw | ConvertFrom-Json
    $metadata.file = 'assets/' + $file.BaseName + '.jpg'
    $metadata | Add-Member -NotePropertyName sha256 -NotePropertyValue (Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash.ToLowerInvariant() -Force
    $metadata | Add-Member -NotePropertyName bytes -NotePropertyValue (Get-Item -LiteralPath $destination).Length -Force
    $json = $metadata | ConvertTo-Json -Depth 8
    for ($attempt = 0; $attempt -lt 5; $attempt++) {
        try { [System.IO.File]::WriteAllText($metadataPath, $json, [System.Text.UTF8Encoding]::new($false)); break }
        catch { if ($attempt -eq 4) { throw }; Start-Sleep -Milliseconds 200 }
    }
}
$parameters.Dispose()
