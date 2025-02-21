function Convert-RegistryKey {
  param (
      [string]$Key
  )

  # Dictionnaire des remplacements
  $replacements = @{
      "HKEY_LOCAL_MACHINE\" = "HKLM:\"
      "HKEY_CURRENT_USER\" = "HKCU:\"
      "HKEY_CLASSES_ROOT\" = "HKCR:\"
      "HKEY_USERS\" = "HKU:\"
      "HKEY_CURRENT_CONFIG\" = "HKCC:\"
  }

  foreach ($fullKey in $replacements.Keys) {
      if ($Key -like "$fullKey*") {
          return $Key -replace [regex]::Escape($fullKey), $replacements[$fullKey]
      }
  }

  # Retourner la clé inchangée si aucun remplacement n'est trouvé
  return $Key
}

function RegistryEntryExists {
  param (
    [string]$entry
  )

  $convertedPath = Convert-RegistryKey -Key $entry

  return Test-Path -Path $convertedPath
}

function FetchRegistryKeys {
  param (
    [string]$entry
  )

  try {
    $convertedPath = Convert-RegistryKey -Key $entry
    $keys = (Get-Item -Path $convertedPath -ErrorAction Stop).Property
    return $keys
  }
  catch {
    throw $_
  }
}

function FetchRegistryValue {
  param (
    [string]$entry,
    [string]$propertyName
  )
  
  try {
    $convertedPath = Convert-RegistryKey -Key $entry
    Get-ItemPropertyValue -Path $convertedPath -Name $propertyName -ErrorAction Stop
  }
  catch {
    throw $_
  }
}
