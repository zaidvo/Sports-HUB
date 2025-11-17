<?php
/**
 * Create Admin Users Script
 * Run this once to create admin users in the database
 * 
 * Usage: php create_admin.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;

// Load environment
Env::bootstrap(__DIR__);

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Hash the password '123'
    $passwordHash = password_hash('123', PASSWORD_BCRYPT);
    
    echo "Creating admin users...\n\n";
    echo "Password hash: $passwordHash\n\n";
    
    // Admin 1: admin@sportzhub.com
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, role, created_at)
        VALUES (:name, :email, :phone, :password, :role, NOW())
        ON CONFLICT (email) DO UPDATE SET
            password = EXCLUDED.password,
            role = 'admin'
        RETURNING id, name, email, role
    ");
    
    $stmt->execute([
        'name' => 'Admin User',
        'email' => 'admin@sportzhub.com',
        'phone' => '1234567890',
        'password' => $passwordHash,
        'role' => 'admin'
    ]);
    
    $admin1 = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Created/Updated: {$admin1['name']} ({$admin1['email']})\n";
    
    // Admin 2: shahzaib@admin.com
    $stmt->execute([
        'name' => 'Shahzaib Admin',
        'email' => 'shahzaib@admin.com',
        'phone' => '1234567890',
        'password' => $passwordHash,
        'role' => 'admin'
    ]);
    
    $admin2 = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Created/Updated: {$admin2['name']} ({$admin2['email']})\n";
    
    echo "\n✅ Admin users created successfully!\n";
    echo "\nLogin credentials:\n";
    echo "  Email: admin@sportzhub.com  | Password: 123\n";
    echo "  Email: shahzaib@admin.com   | Password: 123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
