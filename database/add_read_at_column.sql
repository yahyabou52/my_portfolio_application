-- Add read_at column to contact_messages table
-- This migration adds a timestamp for when messages are marked as read

USE portfolio_db;

-- Add read_at column to track when messages were read
ALTER TABLE contact_messages 
ADD COLUMN read_at TIMESTAMP NULL DEFAULT NULL AFTER is_read;

-- Update existing read messages to have a read_at timestamp
UPDATE contact_messages 
SET read_at = CURRENT_TIMESTAMP 
WHERE is_read = 1 AND read_at IS NULL;