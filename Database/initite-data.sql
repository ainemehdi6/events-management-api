-- Create test usersa
INSERT INTO `user` (id, email, roles, password, firstname, lastname, created_at, updated_at) VALUES
-- Password for all users is 'Password123!'
-- Admin user
("0194a352-1e63-7eb9-a5d1-1d0402ff51f6", 'admin@example.com', '["ROLE_ADMIN", "ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Admin', 'User', NOW(), NOW()),
-- Regular users
("0194a352-7a07-73cf-9714-0bf85969305e", 'john.doe@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'John', 'Doe', NOW(), NOW()),
("0194a352-9d66-7bee-9bfd-c5a18e02e2c1", 'jane.smith@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Jane', 'Smith', NOW(), NOW()),
("0194a352-e7a4-7ba4-9597-44ffd9bb3223", 'bob.wilson@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Bob', 'Wilson', NOW(), NOW());

-- Create categories
INSERT INTO category (id, name, description, color) VALUES
                                                        ('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', 'Conference', 'Large scale professional events', '#4F46E5'),
                                                        ('0194a352-7a07-73cf-9714-0bf85969305e', 'Workshop', 'Interactive learning sessions', '#16A34A'),
                                                        ('0194a352-9d66-7bee-9bfd-c5a18e02e2c1', 'Seminar', 'Educational presentations', '#EA580C'),
                                                        ('0194a352-e7a4-7ba4-9597-44ffd9bb3223', 'Networking', 'Professional networking events', '#0891B2'),
                                                        ('0194a353-2917-7c1e-85aa-40c86e21c168', 'Other', 'Miscellaneous events', '#6B7280');

-- Create events
INSERT INTO event (
    id, title, description, date, end_date, location, capacity,
    registered_count, category_id, status, image_url, price,
    organizer_id, features, created_at, updated_at
) VALUES
-- Upcoming events
(
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    'Tech Conference 2024',
    'Join us for our annual technology conference featuring industry leaders and innovative workshops.',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    DATE_ADD(NOW(), INTERVAL 31 DAY),
    'Convention Center, New York',
    500,
    350,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    'published',
    'https://images.unsplash.com/photo-1540575467063-178a50c2df87',
    299.99,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    '["Full day access to all sessions", "Networking lunch", "Workshop materials", "Certificate of attendance"]',
    NOW(),
    NOW()
),
(
    '0194a352-7a07-73cf-9714-0bf85969305e',
    'Web Development Workshop',
    'Hands-on workshop covering modern web development techniques and best practices.',
    DATE_ADD(NOW(), INTERVAL 15 DAY),
    DATE_ADD(NOW(), INTERVAL 15 DAY),
    'Tech Hub, San Francisco',
    50,
    45,
    '0194a352-7a07-73cf-9714-0bf85969305e',
    'published',
    'https://images.unsplash.com/photo-1531482615713-2afd69097998',
    149.99,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    '["Hands-on coding sessions", "Project templates", "Certificate of completion"]',
    NOW(),
    NOW()
),
(
    '0194a352-9d66-7bee-9bfd-c5a18e02e2c1',
    'Digital Marketing Seminar',
    'Learn the latest digital marketing strategies from industry experts.',
    DATE_ADD(NOW(), INTERVAL 7 DAY),
    DATE_ADD(NOW(), INTERVAL 7 DAY),
    'Business Center, Chicago',
    100,
    25,
    '0194a352-9d66-7bee-9bfd-c5a18e02e2c1',
    'published',
    'https://images.unsplash.com/photo-1557804506-669a67965ba0',
    79.99,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    '["Expert presentations", "Case studies", "Networking session", "Resource materials"]',
    NOW(),
    NOW()
),
-- Draft event
(
    '0194a352-e7a4-7ba4-9597-44ffd9bb3223',
    'Startup Networking Night',
    'Connect with fellow entrepreneurs and investors in a casual setting.',
    DATE_ADD(NOW(), INTERVAL 45 DAY),
    DATE_ADD(NOW(), INTERVAL 45 DAY),
    'Innovation Hub, Boston',
    75,
    0,
    '0194a352-e7a4-7ba4-9597-44ffd9bb3223',
    'draft',
    'https://images.unsplash.com/photo-1515187029135-18ee286d815b',
    25.00,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    '["Speed networking", "Refreshments", "Pitch opportunities"]',
    NOW(),
    NOW()
),
-- Past event
(
    '0194a353-2917-7c1e-85aa-40c86e21c168',
    'Past Tech Meetup',
    'A casual meetup for tech enthusiasts to share ideas and experiences.',
    DATE_SUB(NOW(), INTERVAL 7 DAY),
    DATE_SUB(NOW(), INTERVAL 7 DAY),
    'Tech Space, Seattle',
    50,
    48,
    '0194a353-2917-7c1e-85aa-40c86e21c168',
    'completed',
    'https://images.unsplash.com/photo-1523580494863-6f3031224c94',
    0.00,
    '0194a352-1e63-7eb9-a5d1-1d0402ff51f6',
    '["Open discussions", "Networking", "Refreshments"]',
    NOW(),
    NOW()
);

-- Create event registrations
INSERT INTO event_registration (id, event_id, user_id, status, created_at, updated_at) VALUES
-- Registrations for Tech Conference
('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '0194a352-7a07-73cf-9714-0bf85969305e', 'confirmed', NOW(), NOW()),
('0194a352-7a07-73cf-9714-0bf85969305e', '0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '0194a352-9d66-7bee-9bfd-c5a18e02e2c1', 'confirmed', NOW(), NOW()),
('0194a352-9d66-7bee-9bfd-c5a18e02e2c1', '0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '0194a352-e7a4-7ba4-9597-44ffd9bb3223', 'pending', NOW(), NOW()),

-- Registrations for Web Development Workshop
('0194a352-e7a4-7ba4-9597-44ffd9bb3223', '0194a352-7a07-73cf-9714-0bf85969305e', '0194a352-7a07-73cf-9714-0bf85969305e', 'confirmed', NOW(), NOW()),
('0194a353-2917-7c1e-85aa-40c86e21c168', '0194a352-7a07-73cf-9714-0bf85969305e', '0194a352-9d66-7bee-9bfd-c5a18e02e2c1', 'cancelled', NOW(), NOW()),

-- Registrations for Digital Marketing Seminar
('0194a353-5cf1-703f-81c4-ba1ad49400ac', '0194a352-9d66-7bee-9bfd-c5a18e02e2c1', '0194a352-7a07-73cf-9714-0bf85969305e', 'confirmed', NOW(), NOW()),
('0194a353-83ed-777e-91a5-18d87e404847', '0194a352-9d66-7bee-9bfd-c5a18e02e2c1', '0194a352-e7a4-7ba4-9597-44ffd9bb3223', 'confirmed', NOW(), NOW()),

-- Registrations for Past Tech Meetup
('0194a353-a77f-7a80-9639-9148816e41de', '0194a353-2917-7c1e-85aa-40c86e21c168', '0194a352-7a07-73cf-9714-0bf85969305e', 'confirmed', NOW(), NOW()),
('0194a353-c862-7c34-ac50-e992847a173c', '0194a353-2917-7c1e-85aa-40c86e21c168', '0194a352-9d66-7bee-9bfd-c5a18e02e2c1', 'confirmed', NOW(), NOW());

