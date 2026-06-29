import AuthenticationServices
import CryptoKit
import Foundation
import UIKit

@MainActor
final class AuthManager: NSObject, ObservableObject {
    @Published private(set) var isAuthenticated = false
    @Published var lastError: String?

    private static let tokenAccount = "access_token"

    private var webAuthSession: ASWebAuthenticationSession?
    private var codeVerifier: String?

    var accessToken: String? {
        KeychainStore.read(account: Self.tokenAccount)
    }

    override init() {
        super.init()
        isAuthenticated = accessToken != nil
    }

    func signIn() {
        let verifier = Self.randomURLSafeString(length: 64)
        let challenge = Self.codeChallenge(forVerifier: verifier)
        let state = Self.randomURLSafeString(length: 16)
        codeVerifier = verifier

        var components = URLComponents(url: AplosConfig.authorizeURL, resolvingAgainstBaseURL: false)!
        components.queryItems = [
            URLQueryItem(name: "client_id", value: AplosConfig.clientID),
            URLQueryItem(name: "redirect_uri", value: AplosConfig.redirectURI),
            URLQueryItem(name: "response_type", value: "code"),
            URLQueryItem(name: "code_challenge", value: challenge),
            URLQueryItem(name: "code_challenge_method", value: "S256"),
            URLQueryItem(name: "state", value: state),
        ]

        let session = ASWebAuthenticationSession(
            url: components.url!,
            callbackURLScheme: AplosConfig.redirectScheme
        ) { [weak self] callbackURL, error in
            Task { @MainActor in
                self?.handleCallback(callbackURL: callbackURL, error: error, expectedState: state)
            }
        }
        session.presentationContextProvider = self
        session.prefersEphemeralWebBrowserSession = false
        webAuthSession = session
        session.start()
    }

    func signOut() {
        KeychainStore.delete(account: Self.tokenAccount)
        isAuthenticated = false
    }

    private func handleCallback(callbackURL: URL?, error: Error?, expectedState: String) {
        guard let callbackURL, error == nil else {
            if let error, (error as NSError).code != ASWebAuthenticationSessionError.canceledLogin.rawValue {
                lastError = error.localizedDescription
            }
            return
        }

        let components = URLComponents(url: callbackURL, resolvingAgainstBaseURL: false)
        let items = components?.queryItems ?? []
        let code = items.first(where: { $0.name == "code" })?.value
        let returnedState = items.first(where: { $0.name == "state" })?.value

        guard let code, returnedState == expectedState, let verifier = codeVerifier else {
            lastError = "Sign-in failed: invalid callback."
            return
        }

        Task { await exchangeCode(code, verifier: verifier) }
    }

    private func exchangeCode(_ code: String, verifier: String) async {
        var request = URLRequest(url: AplosConfig.tokenURL)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")

        let body: [String: String] = [
            "grant_type": "authorization_code",
            "code": code,
            "code_verifier": verifier,
            "client_id": AplosConfig.clientID,
            "redirect_uri": AplosConfig.redirectURI,
        ]
        request.httpBody = try? JSONSerialization.data(withJSONObject: body)

        do {
            let (data, response) = try await URLSession.shared.data(for: request)
            guard let http = response as? HTTPURLResponse, http.statusCode == 200 else {
                lastError = "Sign-in failed: server rejected the authorization code."
                return
            }
            let token = try JSONDecoder().decode(TokenResponse.self, from: data)
            KeychainStore.save(token.accessToken, account: Self.tokenAccount)
            isAuthenticated = true
        } catch {
            lastError = "Sign-in failed: \(error.localizedDescription)"
        }
    }

    private static func randomURLSafeString(length: Int) -> String {
        var bytes = [UInt8](repeating: 0, count: length)
        _ = SecRandomCopyBytes(kSecRandomDefault, length, &bytes)
        return Data(bytes).base64URLEncodedString()
    }

    private static func codeChallenge(forVerifier verifier: String) -> String {
        let digest = SHA256.hash(data: Data(verifier.utf8))
        return Data(digest).base64URLEncodedString()
    }
}

extension AuthManager: ASWebAuthenticationPresentationContextProviding {
    func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        let scenes = UIApplication.shared.connectedScenes
        let windowScene = scenes.first as? UIWindowScene
        return windowScene?.windows.first { $0.isKeyWindow } ?? ASPresentationAnchor()
    }
}

private struct TokenResponse: Decodable {
    let accessToken: String
    let tokenType: String
    let expiresIn: Int

    enum CodingKeys: String, CodingKey {
        case accessToken = "access_token"
        case tokenType = "token_type"
        case expiresIn = "expires_in"
    }
}

private extension Data {
    func base64URLEncodedString() -> String {
        base64EncodedString()
            .replacingOccurrences(of: "+", with: "-")
            .replacingOccurrences(of: "/", with: "_")
            .replacingOccurrences(of: "=", with: "")
    }
}
