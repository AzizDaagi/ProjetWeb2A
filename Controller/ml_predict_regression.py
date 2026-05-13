#!/usr/bin/env python3
"""
Predict price and calories for a product using trained regression models.
"""
import argparse
import json
import os
import tempfile
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
CACHE_DIR = Path(os.getenv("HF_HOME", str(Path(tempfile.gettempdir()) / "projetweb1_hf_cache")))
CACHE_DIR.mkdir(parents=True, exist_ok=True)
os.environ.setdefault("HF_HOME", str(CACHE_DIR))
os.environ.setdefault("HUGGINGFACE_HUB_CACHE", str(CACHE_DIR / "hub"))
os.environ.setdefault("TRANSFORMERS_CACHE", str(CACHE_DIR / "transformers"))

try:
    import joblib
except Exception as e:
    print(json.dumps({"error": f"Missing Python dependency: joblib ({e}). Install with: pip3 install joblib"}))
    raise SystemExit(0)


def parse_args():
    parser = argparse.ArgumentParser(description="Predict price and calories for a product")
    parser.add_argument('--name', type=str, default='', help='Product name')
    parser.add_argument('--description', type=str, default='', help='Product description')
    return parser.parse_args()


def main() -> int:
    args = parse_args()

    model_dir = BASE_DIR / "ml_models" / "prediction"
    
    if not model_dir.exists():
        print(json.dumps({
            "error": "Models not trained yet. Train from backoffice first."
        }))
        return 1

    # Load models
    try:
        price_model = joblib.load(model_dir / "price_model.pkl")
        calories_model = joblib.load(model_dir / "calories_model.pkl")
    except Exception as e:
        print(json.dumps({"error": f"Failed to load models: {str(e)}"}))
        return 1

    # Generate embedding
    try:
        if os.getenv("LOCAL_EMBEDDINGS_ONLY") == "1":
            raise RuntimeError("Local embeddings forced.")
        from sentence_transformers import SentenceTransformer
        transformer = SentenceTransformer("all-MiniLM-L6-v2", local_files_only=True)
    except Exception:
        transformer = None

    text = f"{args.name} {args.description}".strip()
    try:
        if transformer is not None:
            embedding = transformer.encode([text], show_progress_bar=False)[0]
        else:
            from sklearn.feature_extraction.text import HashingVectorizer
            vectorizer = HashingVectorizer(n_features=384, alternate_sign=False, norm="l2")
            embedding = vectorizer.transform([text]).toarray()[0]
    except Exception as e:
        msg = str(e).replace("\n", " ")
        print(json.dumps({"error": f"Embedding failed: {msg[:220]}"}))
        return 1

    # Make predictions
    try:
        predicted_price = float(price_model.predict([embedding])[0])
        predicted_calories = int(calories_model.predict([embedding])[0])
    except Exception as e:
        msg = str(e).replace("\n", " ")
        print(json.dumps({"error": f"Prediction failed: {msg[:220]}"}))
        return 1

    # Ensure realistic ranges
    predicted_price = max(0.5, min(100, predicted_price))  # 0.5 - 100 DT
    predicted_calories = max(10, min(1200, predicted_calories))  # 10 - 1200 kcal

    result = {
        "predicted_price": round(predicted_price, 2),
        "predicted_calories": predicted_calories,
    }

    print(json.dumps(result))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
