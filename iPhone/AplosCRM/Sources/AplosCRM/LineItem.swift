import Foundation

enum RevenueScheduleType {
    static let all = ["One-time", "Monthly", "Quarterly", "Annually"]
}

struct LineItem: Identifiable, Decodable {
    let id: Int
    let productDefinitionID: Int?
    let productName: String
    let unitPrice: String?
    let quantity: String?
    let discountPercentage: String?
    let discountAmount: String?
    let totalPrice: String?
    let serviceDate: String?
    let subscriptionTerm: Int?
    let revenueScheduleType: String?
    let shipToLocationID: Int?
    let sku: String?
    let shipToLocationName: String?
    let shipToStreet: String?
    let shipToCity: String?
    let shipToState: String?
    let shipToZip: String?

    enum CodingKeys: String, CodingKey {
        case id, quantity, sku
        case productDefinitionID = "product_definition_id"
        case productName = "product_name"
        case unitPrice = "unit_price"
        case discountPercentage = "discount_percentage"
        case discountAmount = "discount_amount"
        case totalPrice = "total_price"
        case serviceDate = "service_date"
        case subscriptionTerm = "subscription_term"
        case revenueScheduleType = "revenue_schedule_type"
        case shipToLocationID = "ship_to_location_id"
        case shipToLocationName = "ship_to_location_name"
        case shipToStreet = "ship_to_street"
        case shipToCity = "ship_to_city"
        case shipToState = "ship_to_state"
        case shipToZip = "ship_to_zip"
    }
}

struct LineItemsResponse: Decodable {
    let ok: Bool
    let data: [LineItem]
}

struct LineItemDetailResponse: Decodable {
    let ok: Bool
    let data: LineItem
}

struct LineItemRequest: Encodable {
    var productName: String
    var productDefinitionID: Int?
    var unitPrice: String?
    var quantity: String?
    var discountPercentage: String?
    var discountAmount: String?
    var serviceDate: String?
    var subscriptionTerm: Int?
    var revenueScheduleType: String?
    var shipToLocationID: Int?

    enum CodingKeys: String, CodingKey {
        case quantity
        case productName = "product_name"
        case productDefinitionID = "product_definition_id"
        case unitPrice = "unit_price"
        case discountPercentage = "discount_percentage"
        case discountAmount = "discount_amount"
        case serviceDate = "service_date"
        case subscriptionTerm = "subscription_term"
        case revenueScheduleType = "revenue_schedule_type"
        case shipToLocationID = "ship_to_location_id"
    }

    init(from lineItem: LineItem? = nil) {
        productName = lineItem?.productName ?? ""
        productDefinitionID = lineItem?.productDefinitionID
        unitPrice = lineItem?.unitPrice
        quantity = lineItem?.quantity
        discountPercentage = lineItem?.discountPercentage
        discountAmount = lineItem?.discountAmount
        serviceDate = lineItem?.serviceDate
        subscriptionTerm = lineItem?.subscriptionTerm
        revenueScheduleType = lineItem?.revenueScheduleType ?? "One-time"
        shipToLocationID = lineItem?.shipToLocationID
    }
}
