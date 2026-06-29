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
