<?php
/**
 * Database Migration for multi-product cart system
 * Access via: http://localhost/ProjetWeb1/Controller/index.php?action=migrate.cart
 */

namespace App\Controller;

use App\Model\Database;

class MigrationController extends BaseController
{
    public function migrateCart()
    {
        $conn = Database::connection();
        
        $sql = "CREATE TABLE IF NOT EXISTS commande_item (
            id INT PRIMARY KEY AUTO_INCREMENT,
            commande_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE RESTRICT,
            INDEX idx_commande_id (commande_id),
            INDEX idx_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql) === TRUE) {
            $message = "✅ Migration successful: commande_item table created";
            
            // Get table structure
            $result = $conn->query("DESCRIBE commande_item");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row;
            }
            
            $this->render('migration/success', [
                'message' => $message,
                'columns' => $columns
            ]);
        } else {
            $this->render('migration/error', [
                'error' => $conn->error
            ]);
        }
    }

    /**
     * Make product_id nullable in commande table for multi-product cart orders
     */
    public function migrateNullableProductId()
    {
        $conn = Database::connection();
        
        $messages = [];
        
        // Check current column definition
        $result = $conn->query("DESCRIBE commande product_id");
        $columnInfo = $result->fetch_assoc();
        
        // If already nullable, skip
        if (strpos($columnInfo['Null'], 'YES') !== false) {
            $messages[] = "✅ Column product_id is already nullable";
        } else {
            // First, update any 0 values to NULL
            $updateSql = "UPDATE commande SET product_id = NULL WHERE product_id = 0";
            if ($conn->query($updateSql)) {
                $messages[] = "✅ Updated zero product_id values to NULL: " . $conn->affected_rows . " rows";
            } else {
                $messages[] = "⚠️ Warning updating zero values: " . $conn->error;
            }

            // Drop the old foreign key constraint
            $dropFK = "ALTER TABLE commande DROP FOREIGN KEY commande_ibfk_1";
            if ($conn->query($dropFK)) {
                $messages[] = "✅ Dropped old foreign key constraint";
            } else {
                $messages[] = "⚠️ Note: " . $conn->error;
            }

            // Modify column to be nullable
            $modifySql = "ALTER TABLE commande MODIFY COLUMN product_id INT NULL";
            if ($conn->query($modifySql)) {
                $messages[] = "✅ Modified product_id column to be nullable";
            } else {
                $messages[] = "❌ Error modifying column: " . $conn->error;
                $this->render('migration/error', ['error' => $conn->error]);
                return;
            }

            // Add back the foreign key constraint with proper handling
            $addFK = "ALTER TABLE commande ADD CONSTRAINT commande_ibfk_1 
                      FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE SET NULL";
            if ($conn->query($addFK)) {
                $messages[] = "✅ Added foreign key constraint with ON DELETE SET NULL";
            } else {
                $messages[] = "❌ Error adding constraint: " . $conn->error;
                $this->render('migration/error', ['error' => $conn->error]);
                return;
            }
        }

        $this->render('migration/nullable-success', [
            'messages' => $messages
        ]);
    }
}
