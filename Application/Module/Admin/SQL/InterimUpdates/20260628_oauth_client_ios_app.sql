-- Register the AplosCRM iOS app as an OAuth client (PKCE, custom URL scheme redirect).
-- Platform version: 1.0.0

INSERT IGNORE INTO oauth_clients (client_id, name, redirect_uris)
VALUES ('com.aplos.crm.ios', 'AplosCRM iOS App',
        '["com.aplos.crm://oauth/callback"]');
