#!/bin/bash

# Smart Nutrition - Prediction Model Setup Script
# One-time initialization to train ML models
# Usage: bash setup-prediction-models.sh

set -e

echo "========================================="
echo "Smart Nutrition - ML Prediction Setup"
echo "========================================="
echo ""

CONTROLLER_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
VENV_DIR="$CONTROLLER_DIR/.venv"

# Prevent XAMPP runtime libraries from overriding system C++ runtime used by numpy/torch wheels
unset LD_LIBRARY_PATH

echo "[0/3] Preparing Python virtual environment..."
if [ ! -d "$VENV_DIR" ]; then
    python3 -m venv "$VENV_DIR"
fi

PYTHON_BIN="$VENV_DIR/bin/python3"
PIP_BIN="$VENV_DIR/bin/pip"

echo "[1/3] Installing Python dependencies..."
$PIP_BIN install --upgrade pip >/dev/null 2>&1 || true
$PIP_BIN install pymysql pandas scikit-learn sentence-transformers joblib >/dev/null 2>&1 || {
    echo "✗ ERROR: Failed to install Python dependencies in virtual environment"
    exit 1
}
echo "✓ Dependencies ready in $VENV_DIR"

echo "[2/3] Preparing training data..."
if [ -f "$CONTROLLER_DIR/prepare_data.py" ]; then
    "$PYTHON_BIN" "$CONTROLLER_DIR/prepare_data.py" 2>&1
else
    echo "✗ ERROR: prepare_data.py not found"
    exit 1
fi

echo "[3/3] Training prediction models..."
if [ -f "$CONTROLLER_DIR/ml_train_regression.py" ]; then
    "$PYTHON_BIN" "$CONTROLLER_DIR/ml_train_regression.py" 2>&1
else
    echo "✗ ERROR: ml_train_regression.py not found"
    exit 1
fi

echo ""
echo "========================================="
echo "✓ Setup Complete!"
echo "========================================="
echo ""
echo "  Models trained and ready for use."
echo "  Access prediction dashboard:"
echo "  http://localhost/ProjetWeb1/?action=admin.prediction.panel"
echo ""
