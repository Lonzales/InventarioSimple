CREATE SCHEMA IF NOT EXISTS inventariosimple;

USE inventariosimple;

CREATE TABLE IF NOT EXISTS tags (
	id INT AUTO_INCREMENT PRIMARY KEY,
    tag VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(10) NOT NULL
);

INSERT INTO tags (tag, color) VALUES 
('Beverages', '#3498DB'),
('Snacks', '#E67E22'),
('Dairy', '#F1C40F'),
('Frozen Food', '#9B59B6'),
('Tobacco/Vape', '#7F8C8D'),
('Promo/Sale', '#E74C3C');

SELECT * FROM tags;

DROP TABLE tags;

CREATE TABLE IF NOT EXISTS items (
	id INT AUTO_INCREMENT PRIMARY KEY,
    itemName VARCHAR(25) NOT NULL UNIQUE,
    description VARCHAR(60),
    price FLOAT NOT NULL CHECK (price >= 1)
);

INSERT INTO items (itemName, description, price) VALUES 
('Energy Drink 16oz', 'High-caffeine sugar-free energy drink', 2.99),
('Potato Chips BBQ', 'Large bag of crispy barbecue flavored chips', 1.89);

SELECT * FROM items;

DELETE FROM items WHERE items.id = 2;

DROP TABLE items;

CREATE TABLE IF NOT EXISTS tags_items (
	item_id INT,
    tag_id INT,
    PRIMARY KEY (item_id, tag_id),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);

INSERT INTO tags_items (item_id, tag_id) VALUES
(5, 1),
(5, 2),
(6, 4);

SELECT * FROM tags_items WHERE item_id = 5;

DROP TABLE tags_items;

SELECT ti.tag_id AS id, t.tag, t.color FROM tags_items AS ti
INNER JOIN tags AS t
ON ti.tag_id = t.id
WHERE ti.item_id = 5;