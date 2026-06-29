import Foundation

struct Product: Identifiable, Decodable {
    let id: Int
    let productName: String
    let sku: String?
    let productFamily: String?
    let productType: String?
    let listPrice: String?
    let currency: String?
    let lifecycleStatus: String?
    let isActive: Int?

    enum CodingKeys: String, CodingKey {
        case id, sku, currency
        case productName = "product_name"
        case productFamily = "product_family"
        case productType = "product_type"
        case listPrice = "list_price"
        case lifecycleStatus = "lifecycle_status"
        case isActive = "is_active"
    }
}

struct ProductsResponse: Decodable {
    let ok: Bool
    let data: [Product]
}

struct ProductDetail: Identifiable, Decodable {
    let id: Int
    let productName: String
    let sku: String?
    let productDescription: String?
    let productFamily: String?
    let productType: String?
    let isActive: Int?
    let lifecycleStatus: String?
    let listPrice: String?
    let currency: String?
    let unitCost: String?
    let unitOfMeasure: String?
    let pricingModel: String?
    let taxCategory: String?
    let subscriptionTermMonths: Int?
    let weight: String?
    let dimensions: String?
    let material: String?
    let usageMetrics: String?
    let competitiveNotes: String?
    let createdAt: String?
    let updatedAt: String?

    enum CodingKeys: String, CodingKey {
        case id, sku, currency, dimensions, material, weight
        case productName = "product_name"
        case productDescription = "product_description"
        case productFamily = "product_family"
        case productType = "product_type"
        case isActive = "is_active"
        case lifecycleStatus = "lifecycle_status"
        case listPrice = "list_price"
        case unitCost = "unit_cost"
        case unitOfMeasure = "unit_of_measure"
        case pricingModel = "pricing_model"
        case taxCategory = "tax_category"
        case subscriptionTermMonths = "subscription_term_months"
        case usageMetrics = "usage_metrics"
        case competitiveNotes = "competitive_notes"
        case createdAt = "created_at"
        case updatedAt = "updated_at"
    }
}

struct ProductDetailResponse: Decodable {
    let ok: Bool
    let data: ProductDetail
}

struct ProductRequest: Encodable {
    var productName: String
    var sku: String?
    var productDescription: String?
    var productFamily: String?
    var productType: String?
    var isActive: Int?
    var lifecycleStatus: String?
    var listPrice: String?
    var currency: String?
    var unitCost: String?
    var unitOfMeasure: String?
    var pricingModel: String?
    var taxCategory: String?
    var subscriptionTermMonths: Int?
    var weight: String?
    var dimensions: String?
    var material: String?
    var usageMetrics: String?
    var competitiveNotes: String?

    enum CodingKeys: String, CodingKey {
        case sku, currency, dimensions, material, weight
        case productName = "product_name"
        case productDescription = "product_description"
        case productFamily = "product_family"
        case productType = "product_type"
        case isActive = "is_active"
        case lifecycleStatus = "lifecycle_status"
        case listPrice = "list_price"
        case unitCost = "unit_cost"
        case unitOfMeasure = "unit_of_measure"
        case pricingModel = "pricing_model"
        case taxCategory = "tax_category"
        case subscriptionTermMonths = "subscription_term_months"
        case usageMetrics = "usage_metrics"
        case competitiveNotes = "competitive_notes"
    }

    init(from detail: ProductDetail? = nil) {
        productName = detail?.productName ?? ""
        sku = detail?.sku
        productDescription = detail?.productDescription
        productFamily = detail?.productFamily
        productType = detail?.productType
        isActive = detail?.isActive
        lifecycleStatus = detail?.lifecycleStatus
        listPrice = detail?.listPrice
        currency = detail?.currency
        unitCost = detail?.unitCost
        unitOfMeasure = detail?.unitOfMeasure
        pricingModel = detail?.pricingModel
        taxCategory = detail?.taxCategory
        subscriptionTermMonths = detail?.subscriptionTermMonths
        weight = detail?.weight
        dimensions = detail?.dimensions
        material = detail?.material
        usageMetrics = detail?.usageMetrics
        competitiveNotes = detail?.competitiveNotes
    }
}
