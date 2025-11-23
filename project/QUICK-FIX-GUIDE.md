# Quick Fix Guide - Catalog Error

## Problem
"Error loading catalog. Please try again."

## Solution

### Option 1: Automatic Update (Easiest)

1. **Open your browser**
2. **Go to:** `http://localhost/project/update-database-v2.php`
3. **Wait for it to complete** - It will show checkmarks (✓) when done
4. **Then try the catalog again:** `http://localhost/project/client-catalog.html`

### Option 2: Manual Update via phpMyAdmin

1. **Open phpMyAdmin:** `http://localhost/phpmyadmin`
2. **Select database:** `furniture_orders` (from left sidebar)
3. **Click SQL tab**
4. **Copy and paste this SQL:**

```sql
-- Add role column if it doesn't exist
ALTER TABLE users ADD COLUMN role ENUM('admin', 'client') DEFAULT 'client' AFTER password;

-- Update admin user role
UPDATE users SET role = 'admin' WHERE email = 'admin@furniture.com';

-- Create furniture table
CREATE TABLE IF NOT EXISTS furniture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    default_measurements TEXT,
    price_range VARCHAR(100),
    image_url VARCHAR(255),
    features TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add furniture_id to orders table
ALTER TABLE orders ADD COLUMN furniture_id INT NULL AFTER user_id;
ALTER TABLE orders ADD FOREIGN KEY (furniture_id) REFERENCES furniture(id) ON DELETE SET NULL;
ALTER TABLE orders ADD INDEX idx_furniture_id (furniture_id);

-- Insert sample furniture (10 items)
INSERT INTO furniture (name, category, description, default_measurements, price_range, features) VALUES
('Modern Sofa Set', 'Living Room', 'Comfortable 3-seater sofa with matching armchairs. Available in various fabrics and colors.', 'Sofa: 220cm x 95cm x 85cm, Armchair: 90cm x 95cm x 85cm', '$800 - $1,500', 'Removable covers, High-density foam, Wooden legs, Multiple color options'),
('Dining Table Set', 'Dining Room', 'Elegant dining table with 6 matching chairs. Extendable design for larger gatherings.', 'Table: 180cm x 90cm x 75cm (extends to 240cm), Chairs: 45cm x 45cm x 95cm', '$600 - $1,200', 'Extendable leaves, Padded seats, Durable hardwood, Easy to clean'),
('Bookshelf Unit', 'Storage', '5-shelf bookshelf with adjustable shelves. Perfect for home office or living room.', 'Width: 120cm, Height: 200cm, Depth: 35cm', '$300 - $600', 'Adjustable shelves, Cable management, Multiple finishes, Wall-mountable'),
('Bed Frame with Storage', 'Bedroom', 'Queen size bed frame with built-in storage drawers. Modern design with headboard.', 'Width: 160cm, Length: 200cm, Height: 50cm', '$700 - $1,400', 'Storage drawers, Upholstered headboard, Solid wood construction, Easy assembly'),
('Coffee Table', 'Living Room', 'Modern coffee table with storage compartment. Glass top with wooden base.', 'Length: 120cm, Width: 60cm, Height: 40cm', '$200 - $500', 'Tempered glass top, Storage compartment, Soft-close mechanism, Multiple finishes'),
('Wardrobe System', 'Bedroom', '3-door wardrobe with hanging space, shelves, and drawers. Full-length mirror included.', 'Width: 180cm, Height: 220cm, Depth: 60cm', '$1,000 - $2,000', 'LED lighting, Full-length mirror, Adjustable shelves, Soft-close doors'),
('Office Desk', 'Office', 'L-shaped office desk with cable management and filing cabinet. Ergonomic design.', 'Main: 150cm x 75cm, Side: 120cm x 60cm, Height: 75cm', '$500 - $1,000', 'Cable management, Filing cabinet, Ergonomic height, Multiple finishes'),
('TV Stand', 'Living Room', 'Floating TV stand with media storage. Accommodates up to 65" TV.', 'Length: 200cm, Height: 50cm, Depth: 40cm', '$300 - $700', 'Cable management, Ventilation, Media storage, Wall-mountable'),
('Kitchen Cabinet Set', 'Kitchen', 'Custom kitchen cabinets with soft-close hinges. Available in various styles.', 'Custom sizes available', '$2,000 - $5,000', 'Soft-close hinges, Adjustable shelves, Multiple finishes, Custom sizing'),
('Dresser with Mirror', 'Bedroom', '6-drawer dresser with attached mirror. Ample storage space.', 'Width: 120cm, Height: 150cm, Depth: 50cm', '$400 - $800', '6 drawers, Attached mirror, Soft-close drawers, Multiple finishes');
```

5. **Click "Go" button**
6. **Done!** Now try the catalog again.

## Verify It Worked

After updating, test:
- **Browse Catalog:** `http://localhost/project/client-catalog.html`
- **Check Images:** `http://localhost/project/check-furniture-images.php` (optional)

You should see 10 furniture items in the catalog!

## What Gets Added

✅ `role` column to users table  
✅ `furniture` table with 10 sample items  
✅ `furniture_id` column to orders table  
✅ Sample furniture data (sofa, table, bed, etc.)

## Troubleshooting

**If you get "Table already exists" error:**
- That's okay! It means the table is already there.
- Just continue with the INSERT statements.

**If you get "Column already exists" error:**
- That's also okay! Skip that line and continue.

**If INSERT fails:**
- The furniture might already be there.
- Check if items exist: `SELECT COUNT(*) FROM furniture;`

Good luck! 🚀




