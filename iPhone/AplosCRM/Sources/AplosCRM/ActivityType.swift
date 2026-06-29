import Foundation

struct ActivityType: Identifiable, Decodable {
    let id: Int
    let name: String
    let description: String?
    let averageCost: String?
    let isActive: Int?
    let createdAt: String?
    let updatedAt: String?

    enum CodingKeys: String, CodingKey {
        case id, name, description
        case averageCost = "average_cost"
        case isActive = "is_active"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
    }
}

struct ActivityTypesResponse: Decodable {
    let ok: Bool
    let data: [ActivityType]
}

struct ActivityTypeDetailResponse: Decodable {
    let ok: Bool
    let data: ActivityType
}

struct ActivityTypeRequest: Encodable {
    var name: String
    var description: String?
    var averageCost: String?
    var isActive: Int?

    enum CodingKeys: String, CodingKey {
        case name, description
        case averageCost = "average_cost"
        case isActive = "is_active"
    }

    init(from detail: ActivityType? = nil) {
        name = detail?.name ?? ""
        description = detail?.description
        averageCost = detail?.averageCost
        isActive = detail?.isActive
    }
}
