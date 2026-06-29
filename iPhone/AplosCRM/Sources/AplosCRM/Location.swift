import Foundation

enum LocationEnums {
    static let types = ["Bill To", "Ship To"]
    static let statuses = ["Active", "Inactive", "Closed", "Temporary"]
    static let validationStatuses = ["Verified", "Pending", "Invalid"]
}

struct Location: Identifiable, Decodable {
    let id: Int
    let accountID: Int
    let locationName: String?
    let locationType: String?
    let locationStatus: String?
    let isPrimary: Int?
    let validationStatus: String?
    let streetAddress1: String?
    let streetAddress2: String?
    let streetAddress3: String?
    let city: String?
    let stateProvince: String?
    let zipPostalCode: String?
    let countryRegion: String?
    let county: String?
    let districtNeighborhood: String?
    let buildingNameNumber: String?
    let floorSuiteApartment: String?
    let intersectionCrossStreet: String?
    let poBox: String?
    let latitude: String?
    let longitude: String?
    let timezoneUTCOffset: String?
    let geofenceRadius: Int?
    let dockInstructions: String?
    let receivingHours: String?
    let liftgateRequired: Int?
    let vehicleClearance: String?
    let forkliftAvailable: Int?
    let gateEntryCode: String?
    let preferredCarrier: String?

    enum CodingKeys: String, CodingKey {
        case id, city, county, latitude, longitude
        case accountID = "account_id"
        case locationName = "location_name"
        case locationType = "location_type"
        case locationStatus = "location_status"
        case isPrimary = "is_primary"
        case validationStatus = "validation_status"
        case streetAddress1 = "street_address_1"
        case streetAddress2 = "street_address_2"
        case streetAddress3 = "street_address_3"
        case stateProvince = "state_province"
        case zipPostalCode = "zip_postal_code"
        case countryRegion = "country_region"
        case districtNeighborhood = "district_neighborhood"
        case buildingNameNumber = "building_name_number"
        case floorSuiteApartment = "floor_suite_apartment"
        case intersectionCrossStreet = "intersection_cross_street"
        case poBox = "po_box"
        case timezoneUTCOffset = "timezone_utc_offset"
        case geofenceRadius = "geofence_radius"
        case dockInstructions = "dock_instructions"
        case receivingHours = "receiving_hours"
        case liftgateRequired = "liftgate_required"
        case vehicleClearance = "vehicle_clearance"
        case forkliftAvailable = "forklift_available"
        case gateEntryCode = "gate_entry_code"
        case preferredCarrier = "preferred_carrier"
    }
}

struct LocationsResponse: Decodable {
    let ok: Bool
    let data: [Location]
}

struct LocationDetailResponse: Decodable {
    let ok: Bool
    let data: Location
}

struct LocationRequest: Encodable {
    var locationName: String
    var locationType: String?
    var locationStatus: String?
    var isPrimary: Int?
    var validationStatus: String?
    var streetAddress1: String?
    var streetAddress2: String?
    var city: String?
    var stateProvince: String?
    var zipPostalCode: String?
    var countryRegion: String?

    enum CodingKeys: String, CodingKey {
        case city
        case locationName = "location_name"
        case locationType = "location_type"
        case locationStatus = "location_status"
        case isPrimary = "is_primary"
        case validationStatus = "validation_status"
        case streetAddress1 = "street_address_1"
        case streetAddress2 = "street_address_2"
        case stateProvince = "state_province"
        case zipPostalCode = "zip_postal_code"
        case countryRegion = "country_region"
    }

    init(from location: Location? = nil) {
        locationName = location?.locationName ?? ""
        locationType = location?.locationType
        locationStatus = location?.locationStatus ?? "Active"
        isPrimary = location?.isPrimary
        validationStatus = location?.validationStatus ?? "Pending"
        streetAddress1 = location?.streetAddress1
        streetAddress2 = location?.streetAddress2
        city = location?.city
        stateProvince = location?.stateProvince
        zipPostalCode = location?.zipPostalCode
        countryRegion = location?.countryRegion
    }
}
