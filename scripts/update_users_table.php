<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = get_db_connection();
    
    // Add columns for password reset if they don't exist
    $sql = "
    DO $$ 
    BEGIN 
        BEGIN
            ALTER TABLE users ADD COLUMN reset_token VARCHAR(255);
            ALTER TABLE users ADD COLUMN reset_expires_at TIMESTAMP;
        EXCEPTION
            WHEN duplicate_column THEN RAISE NOTICE 'column reset_token already exists in users.';
        END;
    END;
    $$;
    ";
    // Since we are likely on sqlite or mysql or postgresql.
    // The previous init_db used SERIAL PRIMARY KEY which is Postgres.
    // The above DO block is Postgres specific.
    
    // Let's check init_db.php again to be sure about the DB type.
    // "CREATE TABLE IF NOT EXISTS categories (id SERIAL PRIMARY KEY..."
    // Yes, SERIAL is Postgres.
    
    $pdo->exec($sql);
    echo "Added reset_token and reset_expires_at columns to users table.\n";

} catch (PDOException $e) {
    // Fallback if the strict PL/pgSQL doesn't work (e.g. basic query)
    try {
         $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255)");
         $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires_at TIMESTAMP");
         echo "Columns added via fallback method.\n";
    } catch (PDOException $e2) {
        echo "Note: " . $e2->getMessage() . "\n";
    }
}
