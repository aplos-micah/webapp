import Foundation

enum APIError: Error, LocalizedError {
    case unauthorized
    case server(String)

    var errorDescription: String? {
        switch self {
        case .unauthorized: return "Your session has expired. Please sign in again."
        case .server(let message): return message
        }
    }
}

private struct APIErrorBody: Decodable {
    let error: String?
}

struct APIClient {
    let accessToken: String

    func fetchAccounts(search: String = "") async throws -> [Account] {
        var components = URLComponents(url: AplosConfig.accountsURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        let decoded = try JSONDecoder().decode(AccountsResponse.self, from: data)
        return decoded.data
    }

    func fetchAccount(id: Int) async throws -> AccountDetail {
        var components = URLComponents(url: AplosConfig.accountsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        let decoded = try JSONDecoder().decode(AccountDetailResponse.self, from: data)
        return decoded.data
    }

    func createAccount(_ body: AccountRequest) async throws -> AccountDetail {
        let data = try await send(url: AplosConfig.accountsURL, method: "POST", body: body)
        return try JSONDecoder().decode(AccountDetailResponse.self, from: data).data
    }

    func updateAccount(id: Int, _ body: AccountRequest) async throws -> AccountDetail {
        var components = URLComponents(url: AplosConfig.accountsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(AccountDetailResponse.self, from: data).data
    }

    func fetchContacts(search: String = "") async throws -> [Contact] {
        var components = URLComponents(url: AplosConfig.contactsURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ContactsResponse.self, from: data).data
    }

    func fetchContact(id: Int) async throws -> ContactDetail {
        var components = URLComponents(url: AplosConfig.contactsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ContactDetailResponse.self, from: data).data
    }

    func createContact(_ body: ContactRequest) async throws -> ContactDetail {
        let data = try await send(url: AplosConfig.contactsURL, method: "POST", body: body)
        return try JSONDecoder().decode(ContactDetailResponse.self, from: data).data
    }

    func updateContact(id: Int, _ body: ContactRequest) async throws -> ContactDetail {
        var components = URLComponents(url: AplosConfig.contactsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(ContactDetailResponse.self, from: data).data
    }

    func fetchProducts(search: String = "") async throws -> [Product] {
        var components = URLComponents(url: AplosConfig.productsURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ProductsResponse.self, from: data).data
    }

    func fetchProduct(id: Int) async throws -> ProductDetail {
        var components = URLComponents(url: AplosConfig.productsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ProductDetailResponse.self, from: data).data
    }

    func createProduct(_ body: ProductRequest) async throws -> ProductDetail {
        let data = try await send(url: AplosConfig.productsURL, method: "POST", body: body)
        return try JSONDecoder().decode(ProductDetailResponse.self, from: data).data
    }

    func updateProduct(id: Int, _ body: ProductRequest) async throws -> ProductDetail {
        var components = URLComponents(url: AplosConfig.productsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(ProductDetailResponse.self, from: data).data
    }

    func fetchActivities(search: String = "") async throws -> [Activity] {
        var components = URLComponents(url: AplosConfig.activitiesURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ActivitiesResponse.self, from: data).data
    }

    func fetchActivity(id: Int) async throws -> ActivityDetail {
        var components = URLComponents(url: AplosConfig.activitiesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ActivityDetailResponse.self, from: data).data
    }

    func createActivity(_ body: ActivityRequest) async throws -> ActivityDetail {
        let data = try await send(url: AplosConfig.activitiesURL, method: "POST", body: body)
        return try JSONDecoder().decode(ActivityDetailResponse.self, from: data).data
    }

    func updateActivity(id: Int, _ body: ActivityRequest) async throws -> ActivityDetail {
        var components = URLComponents(url: AplosConfig.activitiesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(ActivityDetailResponse.self, from: data).data
    }

    func fetchActivityTypes(search: String = "") async throws -> [ActivityType] {
        var components = URLComponents(url: AplosConfig.activityTypesURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ActivityTypesResponse.self, from: data).data
    }

    func fetchActivityType(id: Int) async throws -> ActivityType {
        var components = URLComponents(url: AplosConfig.activityTypesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(ActivityTypeDetailResponse.self, from: data).data
    }

    func createActivityType(_ body: ActivityTypeRequest) async throws -> ActivityType {
        let data = try await send(url: AplosConfig.activityTypesURL, method: "POST", body: body)
        return try JSONDecoder().decode(ActivityTypeDetailResponse.self, from: data).data
    }

    func updateActivityType(id: Int, _ body: ActivityTypeRequest) async throws -> ActivityType {
        var components = URLComponents(url: AplosConfig.activityTypesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(ActivityTypeDetailResponse.self, from: data).data
    }

    func fetchOpportunities(search: String = "") async throws -> [Opportunity] {
        var components = URLComponents(url: AplosConfig.opportunitiesURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(OpportunitiesResponse.self, from: data).data
    }

    func fetchOpportunity(id: Int) async throws -> OpportunityDetail {
        var components = URLComponents(url: AplosConfig.opportunitiesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(OpportunityDetailResponse.self, from: data).data
    }

    func createOpportunity(_ body: OpportunityRequest) async throws -> OpportunityDetail {
        let data = try await send(url: AplosConfig.opportunitiesURL, method: "POST", body: body)
        return try JSONDecoder().decode(OpportunityDetailResponse.self, from: data).data
    }

    func updateOpportunity(id: Int, _ body: OpportunityRequest) async throws -> OpportunityDetail {
        var components = URLComponents(url: AplosConfig.opportunitiesURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "id", value: String(id))]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(OpportunityDetailResponse.self, from: data).data
    }

    func fetchLocations(accountID: Int) async throws -> [Location] {
        var components = URLComponents(url: AplosConfig.locationsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "account_id", value: String(accountID))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(LocationsResponse.self, from: data).data
    }

    func createLocation(accountID: Int, _ body: LocationRequest) async throws -> Location {
        var components = URLComponents(url: AplosConfig.locationsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "account_id", value: String(accountID))]
        let data = try await send(url: components.url!, method: "POST", body: body)
        return try JSONDecoder().decode(LocationDetailResponse.self, from: data).data
    }

    func updateLocation(accountID: Int, id: Int, _ body: LocationRequest) async throws -> Location {
        var components = URLComponents(url: AplosConfig.locationsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [
            URLQueryItem(name: "account_id", value: String(accountID)),
            URLQueryItem(name: "id", value: String(id)),
        ]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(LocationDetailResponse.self, from: data).data
    }

    func deleteLocation(accountID: Int, id: Int) async throws {
        var components = URLComponents(url: AplosConfig.locationsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [
            URLQueryItem(name: "account_id", value: String(accountID)),
            URLQueryItem(name: "id", value: String(id)),
        ]
        var request = URLRequest(url: components.url!)
        request.httpMethod = "DELETE"
        request.setValue("Bearer \(accessToken)", forHTTPHeaderField: "Authorization")
        _ = try await perform(request)
    }

    func fetchLineItems(opportunityID: Int) async throws -> [LineItem] {
        var components = URLComponents(url: AplosConfig.lineItemsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "opportunity_id", value: String(opportunityID))]

        let data = try await get(url: components.url!)
        return try JSONDecoder().decode(LineItemsResponse.self, from: data).data
    }

    func createLineItem(opportunityID: Int, _ body: LineItemRequest) async throws -> LineItem {
        var components = URLComponents(url: AplosConfig.lineItemsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [URLQueryItem(name: "opportunity_id", value: String(opportunityID))]
        let data = try await send(url: components.url!, method: "POST", body: body)
        return try JSONDecoder().decode(LineItemDetailResponse.self, from: data).data
    }

    func updateLineItem(opportunityID: Int, id: Int, _ body: LineItemRequest) async throws -> LineItem {
        var components = URLComponents(url: AplosConfig.lineItemsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [
            URLQueryItem(name: "opportunity_id", value: String(opportunityID)),
            URLQueryItem(name: "id", value: String(id)),
        ]
        let data = try await send(url: components.url!, method: "PUT", body: body)
        return try JSONDecoder().decode(LineItemDetailResponse.self, from: data).data
    }

    func deleteLineItem(opportunityID: Int, id: Int) async throws {
        var components = URLComponents(url: AplosConfig.lineItemsURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [
            URLQueryItem(name: "opportunity_id", value: String(opportunityID)),
            URLQueryItem(name: "id", value: String(id)),
        ]
        var request = URLRequest(url: components.url!)
        request.httpMethod = "DELETE"
        request.setValue("Bearer \(accessToken)", forHTTPHeaderField: "Authorization")
        _ = try await perform(request)
    }

    private func get(url: URL) async throws -> Data {
        var request = URLRequest(url: url)
        request.setValue("Bearer \(accessToken)", forHTTPHeaderField: "Authorization")
        return try await perform(request)
    }

    private func send<Body: Encodable>(url: URL, method: String, body: Body) async throws -> Data {
        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("Bearer \(accessToken)", forHTTPHeaderField: "Authorization")
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.httpBody = try JSONEncoder().encode(body)
        return try await perform(request)
    }

    private func perform(_ request: URLRequest) async throws -> Data {
        let (data, response) = try await URLSession.shared.data(for: request)
        guard let http = response as? HTTPURLResponse else {
            throw APIError.server("No response from server.")
        }
        if http.statusCode == 401 {
            throw APIError.unauthorized
        }
        guard (200...299).contains(http.statusCode) else {
            let message = (try? JSONDecoder().decode(APIErrorBody.self, from: data))?.error
            throw APIError.server(message ?? "Server returned status \(http.statusCode).")
        }
        return data
    }
}
