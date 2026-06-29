import Foundation

enum AplosConfig {
    static let baseURL = URL(string: "https://crmdev.aplosuite.com")!
    static let clientID = "com.aplos.crm.ios"
    static let redirectURI = "com.aplos.crm://oauth/callback"
    static let redirectScheme = "com.aplos.crm"

    static var authorizeURL: URL { baseURL.appendingPathComponent("authorize") }
    static var tokenURL: URL { baseURL.appendingPathComponent("oauth/token") }
    static var accountsURL: URL { baseURL.appendingPathComponent("api_v2/crm/accounts") }
    static var contactsURL: URL { baseURL.appendingPathComponent("api_v2/crm/contacts") }
    static var opportunitiesURL: URL { baseURL.appendingPathComponent("api_v2/crm/opportunities") }
    static var productsURL: URL { baseURL.appendingPathComponent("api_v2/crm/products") }
    static var activitiesURL: URL { baseURL.appendingPathComponent("api_v2/crm/activities") }
    static var activityTypesURL: URL { baseURL.appendingPathComponent("api_v2/crm/activitytypes") }
    static var locationsURL: URL { baseURL.appendingPathComponent("api_v2/crm/locations") }
    static var lineItemsURL: URL { baseURL.appendingPathComponent("api_v2/crm/lineitems") }
}
