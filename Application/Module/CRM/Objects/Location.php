<?php

/**
 * Location
 *
 * Data access layer for the locations table.
 * Locations are always scoped to an account_id.
 */
class Location
{
    private DB $db;

    const LOCATION_TYPES = ['Bill To', 'Ship To'];

    const STATUSES = ['Active', 'Inactive', 'Closed', 'Temporary'];

    const VALIDATION_STATUSES = ['Verified', 'Pending', 'Invalid'];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    public function findByAccount(int $accountId): array
    {
        return $this->db->query(
            'SELECT * FROM locations
              WHERE account_id = ?
              ORDER BY is_primary DESC, location_name ASC, id ASC',
            [$accountId]
        );
    }

    public function findById(int $locationId, int $accountId): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM locations WHERE id = ? AND account_id = ?',
            [$locationId, $accountId]
        );
    }

    public function countByAccount(int $accountId): int
    {
        $row = $this->db->queryOne(
            'SELECT COUNT(*) AS n FROM locations WHERE account_id = ?',
            [$accountId]
        );
        return (int) ($row['n'] ?? 0);
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    private function sanitize(array $data): array
    {
        return [
            'location_name'             => trim($data['location_name']             ?? ''),
            'location_type'             => in_array($data['location_type'] ?? '', self::LOCATION_TYPES, true)
                                            ? $data['location_type'] : '',
            'location_status'           => in_array($data['location_status'] ?? '', self::STATUSES, true)
                                            ? $data['location_status'] : 'Active',
            'is_primary'                => !empty($data['is_primary']) ? 1 : 0,
            'validation_status'         => in_array($data['validation_status'] ?? '', self::VALIDATION_STATUSES, true)
                                            ? $data['validation_status'] : 'Pending',
            'street_address_1'          => trim($data['street_address_1']          ?? ''),
            'street_address_2'          => trim($data['street_address_2']          ?? ''),
            'street_address_3'          => trim($data['street_address_3']          ?? ''),
            'city'                      => trim($data['city']                      ?? ''),
            'state_province'            => trim($data['state_province']            ?? ''),
            'zip_postal_code'           => trim($data['zip_postal_code']           ?? ''),
            'country_region'            => trim($data['country_region']            ?? ''),
            'county'                    => trim($data['county']                    ?? ''),
            'district_neighborhood'     => trim($data['district_neighborhood']     ?? ''),
            'building_name_number'      => trim($data['building_name_number']      ?? ''),
            'floor_suite_apartment'     => trim($data['floor_suite_apartment']     ?? ''),
            'intersection_cross_street' => trim($data['intersection_cross_street'] ?? ''),
            'po_box'                    => trim($data['po_box']                    ?? ''),
            'latitude'                  => ($data['latitude']       ?? '') !== '' ? (float) $data['latitude']       : null,
            'longitude'                 => ($data['longitude']      ?? '') !== '' ? (float) $data['longitude']      : null,
            'timezone_utc_offset'       => trim($data['timezone_utc_offset']       ?? ''),
            'geofence_radius'           => ($data['geofence_radius'] ?? '') !== '' ? (int) $data['geofence_radius'] : null,
            'dock_instructions'         => trim($data['dock_instructions']         ?? ''),
            'receiving_hours'           => trim($data['receiving_hours']           ?? ''),
            'liftgate_required'         => !empty($data['liftgate_required'])  ? 1 : 0,
            'vehicle_clearance'         => trim($data['vehicle_clearance']         ?? ''),
            'forklift_available'        => !empty($data['forklift_available']) ? 1 : 0,
            'gate_entry_code'           => trim($data['gate_entry_code']           ?? ''),
            'preferred_carrier'         => trim($data['preferred_carrier']         ?? ''),
        ];
    }

    public function add(int $accountId, array $data): array
    {
        $d = $this->sanitize($data);

        $id = $this->db->insert(
            "INSERT INTO locations (
                account_id,
                location_name, location_type, location_status, is_primary, validation_status,
                street_address_1, street_address_2, street_address_3,
                city, state_province, zip_postal_code, country_region,
                county, district_neighborhood, building_name_number,
                floor_suite_apartment, intersection_cross_street, po_box,
                latitude, longitude, timezone_utc_offset, geofence_radius,
                dock_instructions, receiving_hours, liftgate_required,
                vehicle_clearance, forklift_available, gate_entry_code, preferred_carrier
             ) VALUES (
                ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?
             )",
            [
                $accountId,
                $d['location_name'], $d['location_type'], $d['location_status'],
                $d['is_primary'], $d['validation_status'],
                $d['street_address_1'], $d['street_address_2'], $d['street_address_3'],
                $d['city'], $d['state_province'], $d['zip_postal_code'], $d['country_region'],
                $d['county'], $d['district_neighborhood'], $d['building_name_number'],
                $d['floor_suite_apartment'], $d['intersection_cross_street'], $d['po_box'],
                $d['latitude'], $d['longitude'], $d['timezone_utc_offset'], $d['geofence_radius'],
                $d['dock_instructions'], $d['receiving_hours'], $d['liftgate_required'],
                $d['vehicle_clearance'], $d['forklift_available'], $d['gate_entry_code'],
                $d['preferred_carrier'],
            ]
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $locationId, int $accountId, array $data): array
    {
        $d = $this->sanitize($data);

        $affected = $this->db->execute(
            "UPDATE locations SET
                location_name = ?, location_type = ?, location_status = ?,
                is_primary = ?, validation_status = ?,
                street_address_1 = ?, street_address_2 = ?, street_address_3 = ?,
                city = ?, state_province = ?, zip_postal_code = ?, country_region = ?,
                county = ?, district_neighborhood = ?, building_name_number = ?,
                floor_suite_apartment = ?, intersection_cross_street = ?, po_box = ?,
                latitude = ?, longitude = ?, timezone_utc_offset = ?, geofence_radius = ?,
                dock_instructions = ?, receiving_hours = ?, liftgate_required = ?,
                vehicle_clearance = ?, forklift_available = ?, gate_entry_code = ?,
                preferred_carrier = ?
             WHERE id = ? AND account_id = ?",
            [
                $d['location_name'], $d['location_type'], $d['location_status'],
                $d['is_primary'], $d['validation_status'],
                $d['street_address_1'], $d['street_address_2'], $d['street_address_3'],
                $d['city'], $d['state_province'], $d['zip_postal_code'], $d['country_region'],
                $d['county'], $d['district_neighborhood'], $d['building_name_number'],
                $d['floor_suite_apartment'], $d['intersection_cross_street'], $d['po_box'],
                $d['latitude'], $d['longitude'], $d['timezone_utc_offset'], $d['geofence_radius'],
                $d['dock_instructions'], $d['receiving_hours'], $d['liftgate_required'],
                $d['vehicle_clearance'], $d['forklift_available'], $d['gate_entry_code'],
                $d['preferred_carrier'],
                $locationId, $accountId,
            ]
        );

        return $affected > 0
            ? ['ok' => true,  'error' => null]
            : ['ok' => false, 'error' => 'Location not found.'];
    }

    public function remove(int $locationId, int $accountId): bool
    {
        $affected = $this->db->execute(
            'DELETE FROM locations WHERE id = ? AND account_id = ?',
            [$locationId, $accountId]
        );
        return $affected > 0;
    }
}
