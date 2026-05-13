#!/bin/bash

# Setup script for pre-training ML prediction models
# Run this once to initialize the prediction system
# Usage: bash setup-prediction-models.sh

set -e

echo "==================================="
echo "Smart Nutrition - Prediction Setup"
echo "==================================="
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CONTROLLER_DIR="$SCRIPT_DIR/Controller"

echo "[1/3] Installing Python dependencies..."
if [ -f "$CONTROLLER_DIR/ml_demo_requirements.txt" ]; then
    pip3 install -q -r "$CONTROLLER_DIR/ml_demo_requirements.txt"
    echo "✓ Dependencies installed"
else
    echo "✗ ml_demo_requirements.txt not found"
    exit 1
fi

echo ""
echo "[2/3] Preparing training data..."
if [ -f "$CONTROLLER_DIR/prepare_data.py" ]; then
    python3 "$CONTROLLER_DIR/prepare_data.py"
    echo "✓ Data preparation complete"
else
    echo "✗ prepare_data.py not found"
    exit 1
fi

echo ""
echo "[3/3] Training prediction models..."
if [ -f "$CONTROLLER_DIR/ml_train_regression.py" ]; then
    python3 "$CONTROLLER_DIR/ml_train_regression.py"
    echo "✓ Model training complete"
else
    echo "✗ ml_train_regression.py not found"
    exit 1
fi

echo ""
echo "==================================="
echo "✓ Setup complete!"
echo "==================================="
echo ""
echo "Models are now ready for prediction."
echo "Access the prediction panel at:"
echo "http://localhost/ProjetWeb1/admin/prediction"
echo ""
