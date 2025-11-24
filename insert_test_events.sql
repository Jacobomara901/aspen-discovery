-- Test data for Events functionality
-- This creates event field sets, event types, events, and multiple event instances
-- docker exec -i aspen-db mariadb -uroot -paspen aspen < insert_test_events.sql

-- Insert event field sets
INSERT INTO event_field_set (id, name) VALUES
(1, 'Standard Event Fields'),
(2, 'Community Event Fields');

-- Insert event types
INSERT INTO event_type (id, eventFieldSetId, title, titleCustomizable, description, descriptionCustomizable, cover, coverCustomizable, eventLength, lengthCustomizable, archived) VALUES
(1, 1, 'Book Club', 1, 'Monthly book discussion group', 1, NULL, 1, 1.5, 1, 0),
(2, 1, 'Story Time', 1, 'Children''s story time', 1, NULL, 1, 1, 1, 0),
(3, 1, 'Computer Class', 1, 'Basic computer skills training', 1, NULL, 1, 2, 1, 0),
(4, 2, 'Author Talk', 1, 'Meet the author event', 1, NULL, 1, 1, 1, 0),
(5, 2, 'Film Screening', 1, 'Community film screening', 1, NULL, 1, 2, 1, 0);

-- Insert test events (these will generate multiple instances)
INSERT INTO event (id, eventTypeId, locationId, sublocationId, title, description, private, startDate, startTime, eventLength, recurrenceOption, recurrenceFrequency, recurrenceInterval, weekDays, monthlyOption, monthDay, monthDate, monthOffset, endOption, recurrenceEnd, recurrenceCount, dateUpdated, deleted, weekNumber) VALUES
-- Event 1: Book Club - meets every 2 weeks (8 instances)
(1, 1, 1, NULL, 'Mystery Book Club', 'Join us for discussion of this month''s mystery novel', 0, '2026-01-15', '18:00:00', 90, 1, 1, 2, NULL, NULL, NULL, NULL, NULL, 2, NULL, 8, UNIX_TIMESTAMP(), 0, 1),

-- Event 2: Story Time - meets weekly on Tuesdays (10 instances)
(2, 2, 2, NULL, 'Toddler Story Time', 'Stories, songs, and crafts for ages 2-4', 0, '2026-01-14', '10:00:00', 45, 1, 1, 1, 'Tuesday', NULL, NULL, NULL, NULL, 2, NULL, 10, UNIX_TIMESTAMP(), 0, 1),

-- Event 3: Computer Class - meets twice a week (12 instances)
(3, 3, 1, NULL, 'Intro to Email', 'Learn the basics of email communication', 0, '2026-01-13', '14:00:00', 120, 1, 1, 1, 'Monday,Thursday', NULL, NULL, NULL, NULL, 2, NULL, 12, UNIX_TIMESTAMP(), 0, 1),

-- Event 4: Author Talk - single event (1 instance)
(4, 4, 3, NULL, 'An Evening with Jane Author', 'Bestselling author discusses her latest novel', 0, '2026-02-20', '19:00:00', 60, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, UNIX_TIMESTAMP(), 0, 1),

-- Event 5: Film Screening - monthly (6 instances)
(5, 5, 1, NULL, 'Classic Film Series', 'Monthly screening of classic films', 0, '2026-01-25', '18:30:00', 150, 1, 3, 1, NULL, 1, NULL, 25, NULL, 2, NULL, 6, UNIX_TIMESTAMP(), 0, 1);

-- Insert event instances for Event 1: Book Club (8 biweekly instances)
INSERT INTO event_instance (eventId, date, time, length, status, note, dateUpdated, deleted, sublocationId) VALUES
(1, '2026-01-15', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-01-29', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-02-12', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-02-26', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-03-12', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-03-26', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-04-09', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(1, '2026-04-23', '18:00:00', 90, 1, NULL, UNIX_TIMESTAMP(), 0, NULL);

-- Insert event instances for Event 2: Story Time (10 weekly instances)
INSERT INTO event_instance (eventId, date, time, length, status, note, dateUpdated, deleted, sublocationId) VALUES
(2, '2026-01-14', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-01-21', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-01-28', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-02-04', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-02-11', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-02-18', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-02-25', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-03-04', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-03-11', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(2, '2026-03-18', '10:00:00', 45, 1, NULL, UNIX_TIMESTAMP(), 0, NULL);

-- Insert event instances for Event 3: Computer Class (12 instances - Mon/Thu)
INSERT INTO event_instance (eventId, date, time, length, status, note, dateUpdated, deleted, sublocationId) VALUES
(3, '2026-01-13', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-01-16', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-01-20', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-01-23', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-01-27', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-01-30', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-03', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-06', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-10', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-13', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-17', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(3, '2026-02-20', '14:00:00', 120, 1, NULL, UNIX_TIMESTAMP(), 0, NULL);

-- Insert event instance for Event 4: Author Talk (single event)
INSERT INTO event_instance (eventId, date, time, length, status, note, dateUpdated, deleted, sublocationId) VALUES
(4, '2026-02-20', '19:00:00', 60, 1, NULL, UNIX_TIMESTAMP(), 0, NULL);

-- Insert event instances for Event 5: Film Screening (6 monthly instances)
INSERT INTO event_instance (eventId, date, time, length, status, note, dateUpdated, deleted, sublocationId) VALUES
(5, '2026-01-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(5, '2026-02-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(5, '2026-03-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(5, '2026-04-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(5, '2026-05-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL),
(5, '2026-06-25', '18:30:00', 150, 1, NULL, UNIX_TIMESTAMP(), 0, NULL);

-- Verify the data
SELECT 'Event Field Sets:' as Section;
SELECT * FROM event_field_set;

SELECT 'Event Types:' as Section;
SELECT id, title, eventLength FROM event_type;

SELECT 'Events with Instance Counts:' as Section;
SELECT e.id, e.title, COUNT(ei.id) as instance_count
FROM event e
LEFT JOIN event_instance ei ON e.id = ei.eventId
WHERE e.deleted = 0
GROUP BY e.id;

SELECT 'Sample Event Instances:' as Section;
SELECT ei.id, e.title, ei.date, ei.time
FROM event_instance ei
JOIN event e ON ei.eventId = e.id
WHERE ei.deleted = 0
ORDER BY ei.date
LIMIT 10;
