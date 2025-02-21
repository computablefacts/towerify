function DirectoryExists {
  param (
    [string]$directoryPath
  )

  return Test-Path -Path $directoryPath -PathType Container
}

function FileExists {
  param (
    [string]$filePath
  )

  return Test-Path -Path $filePath -PathType Leaf
}

function ListFiles {
  param (
    [string]$Path
  )

  try {
    Get-ChildItem -Path $Path -ErrorAction Stop | Select-Object -ExpandProperty Name
  }
  catch {
    throw $_
  }
}

function FetchFile {
  param (
    [string]$file
  )

  try {
    Get-Content -Path $file -ErrorAction Stop
  }
  catch {
    throw $_
  }
}
