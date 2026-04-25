CREATE EVENT IF NOT EXISTS expire_oauth_tokens
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM oauth_tokens
  WHERE expires_at <= NOW() - INTERVAL 10 DAY;

CREATE EVENT IF NOT EXISTS expire_oauth_codes
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM oauth_codes
  WHERE used_at IS NOT NULL
     OR expires_at <= NOW();
