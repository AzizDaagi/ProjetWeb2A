#!/usr/bin/env python3
"""
Check product data in MySQL and generate synthetic data if needed.
Ensures we have enough training data (target: 50-100 samples per regression target).
"""
import pymysql
import random
from datetime import datetime

# Synthetic product templates
FOOD_ADJECTIVES = [
    'Organic', 'Fresh', 'Natural', 'Premium', 'Delicious',
    'Healthy', 'Crispy', 'Tender', 'Golden', 'Smooth',
    'Creamy', 'Light', 'Rich', 'Pure', 'Artisan'
]

FOOD_TYPES = [
    'Salad', 'Sandwich', 'Burger', 'Pizza', 'Pasta',
    'Soup', 'Stew', 'Bowl', 'Wrap', 'Smoothie',
    'Juice', 'Yogurt', 'Granola', 'Cake', 'Cookie',
    'Chicken Breast', 'Salmon Fillet', 'Tofu', 'Quinoa',
    'Brown Rice', 'Sweet Potato', 'Kale Chip', 'Trail Mix'
]

DESCRIPTIONS = [
    'Made with fresh ingredients sourced locally. Perfect for a healthy lunch.',
    'High in protein and fiber. Great for post-workout meals.',
    'Low in calories, high in nutrients. Ideal for weight management.',
    'Packed with vitamins and minerals. Helps boost immunity.',
    'Contains natural probiotics. Supports digestive health.',
    'Rich in antioxidants. Promotes overall wellness.',
    'Gluten-free and vegan. Suitable for dietary restrictions.',
    'No artificial additives or preservatives. 100% natural.',
    'Balanced macronutrients. Perfect for daily nutrition.',
    'Delicious taste with minimal processing. Quality guaranteed.'
]

SELLERS = ['HealthyChoices', 'NutriMart', 'FreshFarm', 'OrganicHub', 'GreenEats', 'VitaminPlus']

def connect_db():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='',
        database='smart_nutrition',
        charset='utf8',
        cursorclass=pymysql.cursors.DictCursor,
    )

def generate_synthetic_product(product_id):
    """Generate a realistic synthetic product."""
    adj = random.choice(FOOD_ADJECTIVES)
    food = random.choice(FOOD_TYPES)
    desc = random.choice(DESCRIPTIONS)
    seller = random.choice(SELLERS)
    
    # Generate price: 2-50 DT
    price = round(random.uniform(2, 50), 2)
    
    # Generate calories: inversely correlated with price (cheaper often higher cal)
    # But add randomness
    base_cal = max(50, 500 - (price * 5))
    calories = int(base_cal + random.randint(-100, 200))
    calories = max(50, min(1000, calories))  # Keep in realistic range
    
    name = f"{adj} {food}"
    
    return {
        'name': name,
        'description': desc,
        'price': price,
        'calories': calories,
        'added_by': seller,
        'is_approved': random.choice([0, 1]),  # Mix approved and pending
    }

def count_valid_products(conn):
    """Count products with valid price and calorie data."""
    with conn.cursor() as cursor:
        cursor.execute('SELECT COUNT(*) as count FROM produit WHERE price > 0 AND calories > 0')
        return cursor.fetchone()['count']

def insert_synthetic_products(conn, count):
    """Insert synthetic products into database."""
    products = []
    for i in range(count):
        products.append(generate_synthetic_product(i))
    
    with conn.cursor() as cursor:
        for prod in products:
            cursor.execute(
                '''INSERT INTO produit (name, description, price, calories, image, added_by, is_approved)
                   VALUES (%s, %s, %s, %s, %s, %s, %s)''',
                (prod['name'], prod['description'], prod['price'], prod['calories'],
                 'synthetic.png', prod['added_by'], prod['is_approved'])
            )
        conn.commit()
    
    return len(products)

def main():
    conn = connect_db()
    
    try:
        # Check current data
        with conn.cursor() as cursor:
            cursor.execute('SELECT COUNT(*) as count FROM produit')
            total = cursor.fetchone()['count']
            print(f"Total products in database: {total}")
        
        valid_count = count_valid_products(conn)
        print(f"Valid products (price > 0, calories > 0): {valid_count}")
        
        # If we have less than 50 valid products, add synthetic data
        target = 80
        if valid_count < target:
            needed = target - valid_count
            print(f"\nGenerating {needed} synthetic products to reach target of {target}...")
            inserted = insert_synthetic_products(conn, needed)
            print(f"Inserted {inserted} synthetic products.")
            
            # Verify
            new_valid = count_valid_products(conn)
            print(f"New valid product count: {new_valid}")
        else:
            print(f"\n✓ Already have sufficient data ({valid_count} >= {target})")
        
        # Show sample
        print("\nSample products:")
        with conn.cursor() as cursor:
            cursor.execute('SELECT id, name, price, calories FROM produit ORDER BY id DESC LIMIT 5')
            for row in cursor.fetchall():
                print(f"  ID {row['id']}: {row['name'][:40]} - Price: {row['price']} DT, Calories: {row['calories']} kcal")
        
    finally:
        conn.close()

if __name__ == '__main__':
    main()
