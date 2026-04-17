#!/usr/bin/env bash
# Crea una rama local desde origin/develop y la publica en el remoto (tracking).
set -euo pipefail

if [[ -z "${1:-}" ]]; then
  echo "Uso: scripts/git/new-branch.sh <nombre-de-rama>" >&2
  echo "Ejemplo: scripts/git/new-branch.sh fix/login-timeout" >&2
  exit 1
fi

BRANCH="$1"
if [[ "$BRANCH" == *" "* ]] || [[ "$BRANCH" == ".."* ]] || [[ "$BRANCH" == *".." ]]; then
  echo "Nombre de rama inválido: sin espacios ni '..' (ej: feature/foo-bar)." >&2
  exit 1
fi

REMOTE="${GIT_REMOTE:-origin}"
BASE="${BASE_BRANCH:-develop}"

echo "→ fetch $REMOTE"
git fetch "$REMOTE"

if ! git rev-parse --verify --quiet "$REMOTE/$BASE" >/dev/null; then
  echo "No existe $REMOTE/$BASE. Comprueba el remoto y que la rama develop exista en GitHub." >&2
  exit 1
fi

if git show-ref --verify --quiet "refs/heads/$BRANCH"; then
  echo "La rama local '$BRANCH' ya existe. Elige otro nombre o elimínala antes." >&2
  exit 1
fi

echo "→ checkout -b $BRANCH desde $REMOTE/$BASE"
git checkout -b "$BRANCH" "$REMOTE/$BASE"

echo "→ push -u $REMOTE $BRANCH"
git push -u "$REMOTE" "$BRANCH"

echo "Listo. Rama '$BRANCH' creada desde $REMOTE/$BASE y publicada."
