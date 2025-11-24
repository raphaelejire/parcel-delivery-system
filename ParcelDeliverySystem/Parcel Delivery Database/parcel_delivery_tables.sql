USE parcel_delivery;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tracking_history;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS parcels;
DROP TABLE IF EXISTS delivery_personnel;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS customers;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Customers
CREATE TABLE customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_uuid CHAR(36) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) UNIQUE,
  address TEXT,
  date_registered DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Admins
CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150),
  email VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Delivery Personnel
CREATE TABLE delivery_personnel (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  personnel_uuid CHAR(36) UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30),
  email VARCHAR(150),
  vehicle_type ENUM('Bike','Van','Truck','Other') DEFAULT 'Bike',
  vehicle_registration VARCHAR(50),
  employment_status ENUM('Active','On Leave','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

-- 4. Parcels
CREATE TABLE parcels (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tracking_number VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT UNSIGNED NOT NULL,
  recipient_name VARCHAR(150) NOT NULL,
  recipient_address TEXT NOT NULL,
  recipient_phone VARCHAR(30),
  description TEXT,
  weight_kg DECIMAL(8,2) DEFAULT 0.00,
  service_type ENUM('Standard','Express','Overnight') DEFAULT 'Standard',
  order_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
  delivery_status ENUM('Order Received','Assigned to Courier','Picked Up','In Transit','Out for Delivery','Delivered','Failed Attempt') DEFAULT 'Order Received',
  shipping_cost DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Assignments & Tracking History
CREATE TABLE assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcel_id INT UNSIGNED NOT NULL,
  personnel_id INT UNSIGNED NOT NULL,
  assigned_by INT UNSIGNED,
  status ENUM('Assigned','Picked Up','Completed','Cancelled') DEFAULT 'Assigned'
) ENGINE=InnoDB;

CREATE TABLE tracking_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcel_id INT UNSIGNED NOT NULL,
  status ENUM('Order Received','Assigned to Courier','Picked Up','In Transit','Out for Delivery','Delivered','Failed Attempt') NOT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  location_note VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO admins (username, password_hash, full_name, email) VALUES
('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator',   'admin@parcel.com'),
('admin1',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Admin',           'alice@company.com'),
('admin2',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael Supervisor',    'michael@company.com'),
('admin3',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rita Operations',       'rita@company.com'),
('admin4',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Control',         'david@company.com');

INSERT INTO customers (customer_uuid, full_name, phone, email, address) VALUES
('cust-1001', 'Acme Logistics', '+2348011111111', 'contact@acmelog.com', '12 Market Street, Lagos'),
('cust-1002', 'John Doe', '+2348022222222', 'john.doe@gmail.com', '45 Ikorodu Road, Lagos'),
('cust-1003', 'Mary Johnson', '+2348033333333', 'maryj@yahoo.com', '18 Lekki Phase 1, Lagos'),
('cust-1004', 'Primetech Ltd.', '+2348044444444', 'info@primetech.com', '22 Allen Avenue, Ikeja'),
('cust-1005', 'Blessed Stores', '+2348055555555', 'sales@blessedstores.com', '77 Garki, Abuja');

INSERT INTO delivery_personnel (personnel_uuid, full_name, phone, email, vehicle_type, vehicle_registration, employment_status) VALUES
('pers-2001', 'Chike Rider', '+2348066666666', 'chike@courier.com', 'Bike', 'ABC-111', 'Active'),
('pers-2002', 'Amaka Driver', '+2348077777777', 'amaka@courier.com', 'Van', 'KJA-222', 'Active'),
('pers-2003', 'Tunde Dispatch', '+2348088888888', 'tunde@courier.com', 'Truck', 'LAG-333', 'On Leave'),
('pers-2004', 'Ola Rider', '+2348099999999', 'ola@courier.com', 'Bike', 'RBC-444', 'Active'),
('pers-2005', 'Precious Courier', '+2348010101010', 'precious@courier.com', 'Van', 'ABJ-555', 'Inactive');

INSERT INTO parcels (tracking_number, customer_id, recipient_name, recipient_address, recipient_phone, description, weight_kg, service_type, shipping_cost) VALUES
('TRK000001', 1, 'Sarah Bassey', 'Victoria Island, Lagos', '+2348111111111', 'Documents', 0.5, 'Express', 1500),
('TRK000002', 2, 'Emeka Udo', 'Enugu City Center', '+2348122222222', 'Clothing package', 1.2, 'Standard', 2000),
('TRK000003', 3, 'Fatima Bello', 'Wuse II, Abuja', '+2348133333333', 'Electronics', 2.4, 'Express', 3500),
('TRK000004', 4, 'Chisom Nwokoro', 'Port Harcourt Township', '+2348144444444', 'Shoes & Bag', 1.0, 'Standard', 1800),
('TRK000005', 5, 'Mr. Adeyemi', 'Ibadan Ring Road', '+2348155555555', 'Home Supplies', 3.0, 'Overnight', 5000);

INSERT INTO assignments (parcel_id, personnel_id, assigned_by, status) VALUES
(1,1,1,'Assigned'),(2,2,1,'Assigned'),(3,3,1,'Assigned'),(4,4,1,'Assigned'),(5,5,1,'Assigned');

INSERT INTO tracking_history (parcel_id, status, location_note) VALUES
(1,'Picked Up','Ikeja Hub'),(1,'In Transit','Third Mainland Bridge'),(2,'Picked Up','Enugu Depot'),(3,'Out for Delivery','Abuja Central'),(4,'Delivered','Port Harcourt');

ALTER TABLE assignments
ADD CONSTRAINT fk_assignments_parcel 
    FOREIGN KEY (parcel_id) REFERENCES parcels(id) 
    ON DELETE CASCADE,
ADD CONSTRAINT fk_assignments_personnel 
    FOREIGN KEY (personnel_id) REFERENCES delivery_personnel(id) 
    ON DELETE RESTRICT,
ADD CONSTRAINT fk_assignments_admin 
    FOREIGN KEY (assigned_by) REFERENCES admins(id) 
    ON DELETE SET NULL;

ALTER TABLE tracking_history
ADD CONSTRAINT fk_tracking_parcel 
    FOREIGN KEY (parcel_id) REFERENCES parcels(id) 
    ON DELETE CASCADE;
    
ALTER TABLE parcels AUTO_INCREMENT = 1;