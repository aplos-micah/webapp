import SwiftUI

@MainActor
final class AccountsListViewModel: ObservableObject {
    @Published var accounts: [Account] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            accounts = try await APIClient(accessToken: token).fetchAccounts()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct AccountsListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = AccountsListViewModel()

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.accounts.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.accounts.isEmpty {
                    ContentUnavailableMessage(error: "No accounts found.")
                } else {
                    List(viewModel.accounts) { account in
                        NavigationLink(value: account.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(account.name).font(.headline)
                                if let type = account.type, !type.isEmpty {
                                    Text(type)
                                        .font(.caption)
                                        .foregroundStyle(.secondary)
                                }
                            }
                        }
                    }
                    .navigationDestination(for: Int.self) { accountID in
                        AccountDetailView(accountID: accountID)
                    }
                }
            }
            .navigationTitle("Accounts")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Sign Out") { authManager.signOut() }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}

private struct ContentUnavailableMessage: View {
    let error: String

    var body: some View {
        VStack(spacing: 8) {
            Image(systemName: "exclamationmark.triangle")
                .font(.largeTitle)
                .foregroundStyle(.secondary)
            Text(error)
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 32)
        }
    }
}
