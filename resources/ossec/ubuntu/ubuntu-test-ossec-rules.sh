#!/bin/bash

target_distribution="ubuntu"

function check_distribution {
  if [ -f /etc/os-release ]; then
    source /etc/os-release
    if [ "$ID" != "$target_distribution" ]; then
      echo "Erreur: ce script ne fonctionne qu'avec $target_distribution."
      echo "Distribution actuelle : $PRETTY_NAME"
      exit 1
    fi
  else
    echo "Erreur: Le fichier /etc/os-release n'a pas été trouvé."
    exit 2
  fi
}

###################################
# Install PowerShell
# See: https://learn.microsoft.com/en-us/powershell/scripting/install/install-ubuntu?view=powershell-7.5
###################################
function install_powershell {
  # Update the list of packages
  sudo apt-get update
  # Install pre-requisite packages.
  sudo apt-get install -y wget apt-transport-https software-properties-common
  # Get the version of Ubuntu
  source /etc/os-release
  # Download the Microsoft repository keys
  wget -q https://packages.microsoft.com/config/ubuntu/$VERSION_ID/packages-microsoft-prod.deb
  # Register the Microsoft repository keys
  sudo dpkg -i packages-microsoft-prod.deb
  # Delete the Microsoft repository keys file
  rm packages-microsoft-prod.deb
  # Update the list of packages after we added packages.microsoft.com
  sudo apt-get update
  ###################################
  # Install PowerShell
  sudo apt-get install -y powershell
}

###################################
# Uninstall PowerShell
# See: https://learn.microsoft.com/en-us/powershell/scripting/install/install-ubuntu?view=powershell-7.5#uninstall-powershell
###################################
function uninstall_powershell {
  sudo apt-get remove -y powershell
}

# Check if pwsh is already installed
pwsh_already_installed=$(command -v pwsh &>/dev/null && echo true || echo false)
# Install powershell if needed
if [ "$pwsh_already_installed" = false ]; then
  check_distribution
  echo 'Installing powershell...'
  install_powershell &> /dev/null
fi

# Run your PowerShell script
ps_script_name=$(mktemp -t XXXXXXXX.ps1)
cat <<"EOF" > $ps_script_name
__PUT_POWERSHELL_HERE__
EOF
pwsh -File $ps_script_name
rm $ps_script_name

# Uninstall powershell if needed
if [ "$pwsh_already_installed" = false ]; then
  echo 'Removing powershell...'
  uninstall_powershell &> /dev/null
fi
