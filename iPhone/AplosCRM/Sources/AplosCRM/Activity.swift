import Foundation

enum ActivityOutcome {
    static let all = [
        "Positive", "Neutral", "Negative",
        "Completed", "No Response", "Follow-up Required", "Cancelled",
    ]
}

struct Activity: Identifiable, Decodable {
    let id: Int
    let activityDate: String
    let outcome: String?
    let cost: String?
    let durationMinutes: Int?
    let accountID: Int?
    let contactID: Int?
    let opportunityID: Int?
    let typeName: String?
    let accountName: String?
    let contactName: String?
    let opportunityName: String?
    let ownerName: String?

    enum CodingKeys: String, CodingKey {
        case id, outcome, cost
        case activityDate = "activity_date"
        case durationMinutes = "duration_minutes"
        case accountID = "account_id"
        case contactID = "contact_id"
        case opportunityID = "opportunity_id"
        case typeName = "type_name"
        case accountName = "account_name"
        case contactName = "contact_name"
        case opportunityName = "opportunity_name"
        case ownerName = "owner_name"
    }
}

struct ActivitiesResponse: Decodable {
    let ok: Bool
    let data: [Activity]
}

struct ActivityDetail: Identifiable, Decodable {
    let id: Int
    let activityTypeID: Int
    let accountID: Int?
    let contactID: Int?
    let opportunityID: Int?
    let activityDate: String
    let durationMinutes: Int?
    let outcome: String?
    let notes: String?
    let cost: String?
    let typeName: String?
    let typeAvgCost: String?
    let accountName: String?
    let contactName: String?
    let opportunityName: String?
    let ownerName: String?
    let createdAt: String?
    let updatedAt: String?

    enum CodingKeys: String, CodingKey {
        case id, outcome, notes, cost
        case activityTypeID = "activity_type_id"
        case accountID = "account_id"
        case contactID = "contact_id"
        case opportunityID = "opportunity_id"
        case activityDate = "activity_date"
        case durationMinutes = "duration_minutes"
        case typeName = "type_name"
        case typeAvgCost = "type_avg_cost"
        case accountName = "account_name"
        case contactName = "contact_name"
        case opportunityName = "opportunity_name"
        case ownerName = "owner_name"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
    }
}

struct ActivityDetailResponse: Decodable {
    let ok: Bool
    let data: ActivityDetail
}

struct ActivityRequest: Encodable {
    var activityTypeID: Int?
    var accountID: Int?
    var contactID: Int?
    var opportunityID: Int?
    var activityDate: String
    var durationMinutes: Int?
    var outcome: String?
    var notes: String?
    var cost: String?

    enum CodingKeys: String, CodingKey {
        case outcome, notes, cost
        case activityTypeID = "activity_type_id"
        case accountID = "account_id"
        case contactID = "contact_id"
        case opportunityID = "opportunity_id"
        case activityDate = "activity_date"
        case durationMinutes = "duration_minutes"
    }

    init(from detail: ActivityDetail? = nil) {
        activityTypeID = detail?.activityTypeID
        accountID = detail?.accountID
        contactID = detail?.contactID
        opportunityID = detail?.opportunityID
        activityDate = detail?.activityDate ?? ""
        durationMinutes = detail?.durationMinutes
        outcome = detail?.outcome
        notes = detail?.notes
        cost = detail?.cost
    }
}
