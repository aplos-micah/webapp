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

struct APIClient {
    let accessToken: String

    func fetchAccounts(search: String = "") async throws -> [Account] {
        var components = URLComponents(url: AplosConfig.accountsURL, resolvingAgainstBaseURL: false)!
        if !search.isEmpty {
            components.queryItems = [URLQueryItem(name: "search", value: search)]
        }

        var request = URLRequest(url: components.url!)
        request.setValue("Bearer \(accessToken)", forHTTPHeaderField: "Authorization")

        let (data, response) = try await URLSession.shared.data(for: request)
        guard let http = response as? HTTPURLResponse else {
            throw APIError.server("No response from server.")
        }
        if http.statusCode == 401 {
            throw APIError.unauthorized
        }
        guard http.statusCode == 200 else {
            throw APIError.server("Server returned status \(http.statusCode).")
        }

        let decoded = try JSONDecoder().decode(AccountsResponse.self, from: data)
        return decoded.data
    }
}
