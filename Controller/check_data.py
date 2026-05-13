#!/usr/bin/env python3
import pymysql
import json

try:
    conn = pymysql.connect(
        host='localhost',
        user='root',
        password='',
        database='smart_nutrition',
        charset='utf8',
        cursorclass=pymysql.cursors.DictCursor,
    )

    with conn:
        with conn.cursor() as cursor:
            cursor.execute('SELECT COUNT(*) as count FROM produit')
            count = cursor.fetchone()['count']
            print(f"Total products: {count}")
            
            cursor.execute('SELECT id, name, description, price, calories FROM produit LIMIT 3')
            results = cursor.fetchall()
            print(f"\nSample products:")
            for row in results:
                print(f"  ID: {row['id']}, Name: {row['name'][:30]}, Price: {row['price']}, Calories: {row['calories']}")
            
            cursor.execute('SELECT COUNT(*) as count FROM produit WHERE price IS NULL OR price = 0 OR calories IS NULL OR calories = 0')
            invalid = cursor.fetchone()['count']
            print(f"\nProducts with missing/zero price or calories: {invalid}")
            
            cursor.execute('SELECT price, calories FROM produit WHERE price > 0 AND calories > 0')
            valid_products = cursor.fetchall()
            print(f"Valid products for training: {len(valid_products)}")

except Exception as e:
    print(f"Error: {e}")
