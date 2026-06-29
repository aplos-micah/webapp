import Foundation

enum OpportunityStage {
    static let all = [
        "New", "Building", "Review", "Quote", "Negotiating", "Closed Won", "Closed Lost",
    ]
    static let closed = ["Closed Won", "Closed Lost"]
}

enum OpportunityEnums {
    static let types = [
        "New Business", "Existing Business - Renewal",
        "Existing Business - Upgrade", "Existing Business - Downgrade",
    ]
    static let leadSources = [
        "Webinar", "Trade Show", "Referral", "Cold Outreach", "Inbound Inquiry", "Organic Search",
    ]
    static let forecastCategories = ["Omitted", "Pipeline", "Best Case", "Commit", "Closed"]
    static let lossReasons = [
        "Lost to Competitor", "Price", "Features/Functionality", "No Budget",
        "Project Cancelled", "Poor Relationship",
    ]
    static let decisionTimelines = ["Immediately", "1-3 Months", "3-6 Months", "6+ Months", "Unknown"]
    static let planTypes = ["Basic", "Professional", "Enterprise", "Custom"]
    static let billingTerms = ["Monthly", "Annual", "Multi-Year"]
}

struct Opportunity: Identifiable, Decodable {
    let id: Int
    let opportunityName: String
    let stage: String?
    let amount: String?
    let closeDate: String?
    let probability: Int?
    let forecastCategory: String?
    let accountID: Int?
    let accountName: String?
    let ownerID: Int?
    let createdAt: String?

    enum CodingKeys: String, CodingKey {
        case id, stage, amount, probability
        case opportunityName = "opportunity_name"
        case closeDate = "close_date"
        case forecastCategory = "forecast_category"
        case accountID = "account_id"
        case accountName = "account_name"
        case ownerID = "owner_id"
        case createdAt = "created_at"
    }
}

struct OpportunitiesResponse: Decodable {
    let ok: Bool
    let data: [Opportunity]
}

struct OpportunityDetail: Identifiable, Decodable {
    let id: Int
    let opportunityName: String
    let opportunityType: String?
    let leadSource: String?
    let accountID: Int?
    let contactID: Int?
    let amount: String?
    let probability: Int?
    let forecastCategory: String?
    let closeDate: String?
    let stage: String?
    let lossReason: String?
    let budgetConfirmed: Int?
    let decisionTimeline: String?
    let stakeholdersIdentified: String?
    let competitor: String?
    let planType: String?
    let billingTerm: String?
    let description: String?
    let billToLocationID: Int?
    let createdAt: String?
    let updatedAt: String?
    let accountName: String?
    let contactName: String?
    let billToLocationName: String?
    let billToStreet: String?
    let billToCity: String?
    let billToState: String?
    let billToZip: String?
    let lineItems: [LineItem]?

    enum CodingKeys: String, CodingKey {
        case id, stage, amount, probability, competitor, description
        case opportunityName = "opportunity_name"
        case opportunityType = "opportunity_type"
        case leadSource = "lead_source"
        case accountID = "account_id"
        case contactID = "contact_id"
        case forecastCategory = "forecast_category"
        case closeDate = "close_date"
        case lossReason = "loss_reason"
        case budgetConfirmed = "budget_confirmed"
        case decisionTimeline = "decision_timeline"
        case stakeholdersIdentified = "stakeholders_identified"
        case planType = "plan_type"
        case billingTerm = "billing_term"
        case billToLocationID = "bill_to_location_id"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
        case accountName = "account_name"
        case contactName = "contact_name"
        case billToLocationName = "bill_to_location_name"
        case billToStreet = "bill_to_street"
        case billToCity = "bill_to_city"
        case billToState = "bill_to_state"
        case billToZip = "bill_to_zip"
        case lineItems = "line_items"
    }
}

struct OpportunityDetailResponse: Decodable {
    let ok: Bool
    let data: OpportunityDetail
}

struct OpportunityRequest: Encodable {
    var opportunityName: String
    var opportunityType: String?
    var leadSource: String?
    var accountID: Int?
    var contactID: Int?
    var amount: String?
    var probability: Int?
    var forecastCategory: String?
    var closeDate: String?
    var stage: String?
    var lossReason: String?
    var budgetConfirmed: Int?
    var decisionTimeline: String?
    var stakeholdersIdentified: String?
    var competitor: String?
    var planType: String?
    var billingTerm: String?
    var description: String?
    var billToLocationID: Int?

    enum CodingKeys: String, CodingKey {
        case stage, amount, probability, competitor, description
        case opportunityName = "opportunity_name"
        case opportunityType = "opportunity_type"
        case leadSource = "lead_source"
        case accountID = "account_id"
        case contactID = "contact_id"
        case forecastCategory = "forecast_category"
        case closeDate = "close_date"
        case lossReason = "loss_reason"
        case budgetConfirmed = "budget_confirmed"
        case decisionTimeline = "decision_timeline"
        case stakeholdersIdentified = "stakeholders_identified"
        case planType = "plan_type"
        case billingTerm = "billing_term"
        case billToLocationID = "bill_to_location_id"
    }

    init(from detail: OpportunityDetail? = nil) {
        opportunityName = detail?.opportunityName ?? ""
        opportunityType = detail?.opportunityType
        leadSource = detail?.leadSource
        accountID = detail?.accountID
        contactID = detail?.contactID
        amount = detail?.amount
        probability = detail?.probability
        forecastCategory = detail?.forecastCategory
        closeDate = detail?.closeDate
        stage = detail?.stage
        lossReason = detail?.lossReason
        budgetConfirmed = detail?.budgetConfirmed
        decisionTimeline = detail?.decisionTimeline
        stakeholdersIdentified = detail?.stakeholdersIdentified
        competitor = detail?.competitor
        planType = detail?.planType
        billingTerm = detail?.billingTerm
        description = detail?.description
        billToLocationID = detail?.billToLocationID
    }
}
