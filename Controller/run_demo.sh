#!/usr/bin/env bash
set -e

python -m venv .venv
source .venv/bin/activate
pip install --upgrade pip
pip install -r ml_demo_requirements.txt
python -m notebook
