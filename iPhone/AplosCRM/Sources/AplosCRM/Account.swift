import Foundation

struct Account: Identifiable, Decodable {
    let id: Int
    let name: String
    let accountNumber: String?
    let type: String?
    let industry: String?
    let status: String?
    let website: String?

    enum CodingKeys: String, CodingKey {
        case id, name, type, industry, status, website
        case accountNumber = "account_number"
    }
}

struct AccountsResponse: Decodable {
    let ok: Bool
    let data: [Account]
}

struct AccountDetail: Identifiable, Decodable {
    let id: Int
    let name: String
    let accountNumber: String?
    let site: String?
    let parentID: Int?
    let industry: String?
    let type: String?
    let billingAddress: String?
    let shippingAddress: String?
    let annualRevenue: String?
    let employeeCount: Int?
    let ownership: String?
    let website: String?
    let status: String?
    let lastActivityAt: String?
    let description: String?
    let createdAt: String?
    let updatedAt: String?

    enum CodingKeys: String, CodingKey {
        case id, name, site, industry, type, ownership, website, status, description
        case accountNumber = "account_number"
        case parentID = "parent_id"
        case billingAddress = "billing_address"
        case shippingAddress = "shipping_address"
        case annualRevenue = "annual_revenue"
        case employeeCount = "employee_count"
        case lastActivityAt = "last_activity_at"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
    }
}

struct AccountDetailResponse: Decodable {
    let ok: Bool
    let data: AccountDetail
}
