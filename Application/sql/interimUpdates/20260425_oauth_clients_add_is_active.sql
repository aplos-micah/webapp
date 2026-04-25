ALTER TABLE oauth_clients
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER redirect_uris;
