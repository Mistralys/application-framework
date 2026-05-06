#!/usr/bin/env bash
# Interactive developer menu for the Application Framework.
# Thin wrapper that invokes the PHP menu script.
#
# Usage:
#   ./menu.sh
cd "$(dirname "$0")" || exit 1
php tools/menu.php
