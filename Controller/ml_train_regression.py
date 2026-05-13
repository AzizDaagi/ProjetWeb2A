#!/usr/bin/env python3
"""
Train regression models for price and calories prediction.
Uses HuggingFace sentence transformers for embeddings + scikit-learn for regression.
"""
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

import numpy as np
import pandas as pd
import pymysql
from sklearn.linear_model import Ridge
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score
import joblib


def load_products_from_mysql() -> pd.DataFrame:
    """Load product data from MySQL."""
    host = os.getenv("DB_HOST", "localhost")
    user = os.getenv("DB_USER", "root")
    password = os.getenv("DB_PASS", "")
    database = os.getenv("DB_NAME", "smart_nutrition")

    conn = pymysql.connect(
        host=host,
        user=user,
        password=password,
        database=database,
        charset="utf8",
        cursorclass=pymysql.cursors.DictCursor,
    )

    query = (
        "SELECT id, name, description, price, calories "
        "FROM produit "
        "WHERE name IS NOT NULL AND description IS NOT NULL "
        "AND price > 0 AND calories > 0"
    )

    with conn:
        cursor = conn.cursor()
        cursor.execute(query)
        results = cursor.fetchall()
        df = pd.DataFrame(results)

    if len(df) < 20:
        raise RuntimeError(f"Not enough training data: {len(df)} samples (need at least 20).")

    df["name"] = df["name"].fillna("").astype(str)
    df["description"] = df["description"].fillna("").astype(str)
    df["text"] = (df["name"] + " " + df["description"]).str.strip()
    df["price"] = pd.to_numeric(df["price"], errors='coerce')
    df["calories"] = pd.to_numeric(df["calories"], errors='coerce')
    df = df.dropna(subset=['price', 'calories'])
    
    return df


def get_embeddings(texts: list) -> np.ndarray:
    """Generate embeddings using HuggingFace Sentence Transformers."""
    try:
        from sentence_transformers import SentenceTransformer
        model = SentenceTransformer("all-MiniLM-L6-v2")
        embeddings = model.encode(texts, show_progress_bar=False)
    except ImportError:
        from sklearn.feature_extraction.text import HashingVectorizer
        print("sentence-transformers is not installed; using local hashing embeddings.")
        vectorizer = HashingVectorizer(n_features=384, alternate_sign=False, norm="l2")
        embeddings = vectorizer.transform(texts).toarray()
    except Exception as exc:
        from sklearn.feature_extraction.text import HashingVectorizer
        msg = str(exc).replace("\n", " ")
        print(f"SentenceTransformer unavailable ({msg[:160]}). Using local hashing embeddings.")
        vectorizer = HashingVectorizer(n_features=384, alternate_sign=False, norm="l2")
        embeddings = vectorizer.transform(texts).toarray()

    return np.array(embeddings)


def train_regression_models(df: pd.DataFrame) -> dict:
    """Train separate regression models for price and calories."""
    
    print(f"Generating embeddings for {len(df)} products...")
    embeddings = get_embeddings(df["text"].tolist())
    
    results = {}

    # Train price model
    print("Training price regression model...")
    X_train, X_test, y_train, y_test = train_test_split(
        embeddings, df["price"], test_size=0.2, random_state=42
    )

    price_model = Ridge(alpha=1.0)
    price_model.fit(X_train, y_train)
    
    price_pred = price_model.predict(X_test)
    price_mse = mean_squared_error(y_test, price_pred)
    price_r2 = r2_score(y_test, price_pred)
    
    results["price"] = {
        "mse": float(price_mse),
        "r2": float(price_r2),
        "test_samples": len(y_test),
        "train_samples": len(y_train),
    }
    print(f"  Price Model - R²: {price_r2:.4f}, MSE: {price_mse:.4f}")

    # Train calories model
    print("Training calories regression model...")
    X_train, X_test, y_train, y_test = train_test_split(
        embeddings, df["calories"], test_size=0.2, random_state=42
    )

    calories_model = Ridge(alpha=1.0)
    calories_model.fit(X_train, y_train)
    
    calories_pred = calories_model.predict(X_test)
    calories_mse = mean_squared_error(y_test, calories_pred)
    calories_r2 = r2_score(y_test, calories_pred)
    
    results["calories"] = {
        "mse": float(calories_mse),
        "r2": float(calories_r2),
        "test_samples": len(y_test),
        "train_samples": len(y_train),
    }
    print(f"  Calories Model - R²: {calories_r2:.4f}, MSE: {calories_mse:.4f}")

    return price_model, calories_model, results


def save_models(price_model, calories_model, results: dict, model_dir: Path):
    """Save trained models and metrics."""
    model_dir.mkdir(parents=True, exist_ok=True)
    
    joblib.dump(price_model, model_dir / "price_model.pkl")
    joblib.dump(calories_model, model_dir / "calories_model.pkl")
    
    with open(model_dir / "metrics.json", "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2)
    
    print(f"\nModels saved to {model_dir}")


def main() -> int:
    try:
        df = load_products_from_mysql()
        print(f"Loaded {len(df)} valid products for training.")
        
        price_model, calories_model, results = train_regression_models(df)
        
        model_dir = BASE_DIR / "ml_models" / "prediction"
        save_models(price_model, calories_model, results, model_dir)
        
        print("\n✓ Training complete!")
        print(json.dumps(results, indent=2))
        
        return 0
        
    except Exception as e:
        print(f"Error: {e}", flush=True)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
