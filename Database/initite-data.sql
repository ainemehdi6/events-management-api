-- Create test users
INSERT INTO user (id, email, roles, password, firstname, lastname, created_at, updated_at) VALUES
-- Password for all users is 'Password123!'
-- Admin user
(UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')), 'admin@example.com', '["ROLE_ADMIN", "ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Admin', 'User', NOW(), NOW()),
-- Regular users
(UNHEX(REPLACE('0194a352-7a07-73cf-9714-0bf85969305e', '-', '')), 'john.doe@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'John', 'Doe', NOW(), NOW()),
(UNHEX(REPLACE('0194a352-9d66-7bee-9bfd-c5a18e02e2c1', '-', '')), 'jane.smith@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Jane', 'Smith', NOW(), NOW()),
(UNHEX(REPLACE('0194a352-e7a4-7ba4-9597-44ffd9bb3223', '-', '')), 'bob.wilson@example.com', '["ROLE_USER"]', '$2y$13$a5KqNiPjfdI9SZ/ByMAd4Ok7jsEyRvM22QXV.Jdl0N0YuJF7x1duS', 'Bob', 'Wilson', NOW(), NOW());

-- Create categories
INSERT INTO category (id, name, description, color) VALUES
                                                        (UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')), 'Conference', 'Large scale professional events', '#4F46E5'),
                                                        (UNHEX(REPLACE('0194a352-7a07-73cf-9714-0bf85969305e', '-', '')), 'Workshop', 'Interactive learning sessions', '#16A34A'),
                                                        (UNHEX(REPLACE('0194a352-9d66-7bee-9bfd-c5a18e02e2c1', '-', '')), 'Seminar', 'Educational presentations', '#EA580C'),
                                                        (UNHEX(REPLACE('0194a352-e7a4-7ba4-9597-44ffd9bb3223', '-', '')), 'Networking', 'Professional networking events', '#0891B2'),
                                                        (UNHEX(REPLACE('0194a353-2917-7c1e-85aa-40c86e21c168', '-', '')), 'Other', 'Miscellaneous events', '#6B7280');

-- Create events
INSERT INTO event (
    id, title, description, date, end_date, location, capacity,
    registered_count, category_id, status, image_url, price,
    organizer_id, features, created_at, updated_at
) VALUES
-- Upcoming events
(
    UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')),
    'Tech Conference 2024',
    'Join us for our annual technology conference featuring industry leaders and innovative workshops.',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    DATE_ADD(NOW(), INTERVAL 31 DAY),
    'Convention Center, New York',
    30,
    0,
    UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')),
    'published',
    'https://images.unsplash.com/photo-1540575467063-178a50c2df87',
    299.99,
    UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')),
    '["Full day access to all sessions", "Networking lunch", "Workshop materials", "Certificate of attendance"]',
    NOW(),
    NOW()
),
(
    UNHEX(REPLACE('0194a352-7a07-73cf-9714-0bf85969305e', '-', '')),
    'Web Development Workshop',
    'Hands-on workshop covering modern web development techniques and best practices.',
    DATE_ADD(NOW(), INTERVAL 15 DAY),
    DATE_ADD(NOW(), INTERVAL 15 DAY),
    'Tech Hub, San Francisco',
    50,
    0,
    UNHEX(REPLACE('0194a352-7a07-73cf-9714-0bf85969305e', '-', '')),
    'published',
    'https://images.unsplash.com/photo-1531482615713-2afd69097998',
    149.99,
    UNHEX(REPLACE('0194a352-1e63-7eb9-a5d1-1d0402ff51f6', '-', '')),
    '["Hands-on coding sessions", "Project templates", "Certificate of completion"]',
    NOW(),
    NOW()
);

