import Foundation

struct Contact: Identifiable, Decodable {
    let id: Int
    let firstName: String
    let lastName: String
    let jobTitle: String?
    let accountID: Int?
    let accountName: String?
    let email: String?
    let status: String?
    let lifecycleStage: String?

    var name: String { "\(firstName) \(lastName)" }

    enum CodingKeys: String, CodingKey {
        case id, email, status
        case firstName = "first_name"
        case lastName = "last_name"
        case jobTitle = "job_title"
        case accountID = "account_id"
        case accountName = "account_name"
        case lifecycleStage = "lifecycle_stage"
    }
}

struct ContactsResponse: Decodable {
    let ok: Bool
    let data: [Contact]
}

struct ContactDetail: Identifiable, Decodable {
    let id: Int
    let firstName: String
    let lastName: String
    let jobTitle: String?
    let company: String?
    let accountID: Int?
    let linkedinURL: String?
    let email: String?
    let workPhone: String?
    let mobilePhone: String?
    let mailingAddress: String?
    let communicationPreference: String?
    let lifecycleStage: String?
    let leadSource: String?
    let status: String?
    let lastContactAt: String?
    let lastActivity: String?
    let leadScore: Int?
    let interactionHistory: String?
    let industry: String?
    let buyingRole: String?
    let renewalDate: String?
    let createdAt: String?
    let updatedAt: String?

    var name: String { "\(firstName) \(lastName)" }

    enum CodingKeys: String, CodingKey {
        case id, company, email, status, industry
        case firstName = "first_name"
        case lastName = "last_name"
        case jobTitle = "job_title"
        case accountID = "account_id"
        case linkedinURL = "linkedin_url"
        case workPhone = "work_phone"
        case mobilePhone = "mobile_phone"
        case mailingAddress = "mailing_address"
        case communicationPreference = "communication_preference"
        case lifecycleStage = "lifecycle_stage"
        case leadSource = "lead_source"
        case lastContactAt = "last_contact_at"
        case lastActivity = "last_activity"
        case leadScore = "lead_score"
        case interactionHistory = "interaction_history"
        case buyingRole = "buying_role"
        case renewalDate = "renewal_date"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
    }
}

struct ContactDetailResponse: Decodable {
    let ok: Bool
    let data: ContactDetail
}

struct ContactRequest: Encodable {
    var firstName: String
    var lastName: String
    var jobTitle: String?
    var accountID: Int?
    var linkedinURL: String?
    var email: String?
    var workPhone: String?
    var mobilePhone: String?
    var mailingAddress: String?
    var communicationPreference: String?
    var lifecycleStage: String?
    var leadSource: String?
    var status: String?
    var lastContactAt: String?
    var leadScore: Int?
    var interactionHistory: String?
    var industry: String?
    var buyingRole: String?
    var renewalDate: String?

    enum CodingKeys: String, CodingKey {
        case email, status, industry
        case firstName = "first_name"
        case lastName = "last_name"
        case jobTitle = "job_title"
        case accountID = "account_id"
        case linkedinURL = "linkedin_url"
        case workPhone = "work_phone"
        case mobilePhone = "mobile_phone"
        case mailingAddress = "mailing_address"
        case communicationPreference = "communication_preference"
        case lifecycleStage = "lifecycle_stage"
        case leadSource = "lead_source"
        case lastContactAt = "last_contact_at"
        case leadScore = "lead_score"
        case interactionHistory = "interaction_history"
        case buyingRole = "buying_role"
        case renewalDate = "renewal_date"
    }

    init(from detail: ContactDetail? = nil) {
        firstName = detail?.firstName ?? ""
        lastName = detail?.lastName ?? ""
        jobTitle = detail?.jobTitle
        accountID = detail?.accountID
        linkedinURL = detail?.linkedinURL
        email = detail?.email
        workPhone = detail?.workPhone
        mobilePhone = detail?.mobilePhone
        mailingAddress = detail?.mailingAddress
        communicationPreference = detail?.communicationPreference
        lifecycleStage = detail?.lifecycleStage
        leadSource = detail?.leadSource
        status = detail?.status
        lastContactAt = detail?.lastContactAt
        leadScore = detail?.leadScore
        interactionHistory = detail?.interactionHistory
        industry = detail?.industry
        buyingRole = detail?.buyingRole
        renewalDate = detail?.renewalDate
    }
}
