import SwiftUI

@MainActor
final class AccountDetailViewModel: ObservableObject {
    @Published var account: AccountDetail?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            account = try await APIClient(accessToken: token).fetchAccount(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct AccountDetailView: View {
    let accountID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = AccountDetailViewModel()

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.account == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let account = viewModel.account {
                Form {
                    Section("Overview") {
                        LabeledRow("Name", account.name)
                        LabeledRow("Account Number", account.accountNumber)
                        LabeledRow("Type", account.type)
                        LabeledRow("Industry", account.industry)
                        LabeledRow("Status", account.status)
                        LabeledRow("Ownership", account.ownership)
                        LabeledRow("Site", account.site)
                    }

                    Section("Business") {
                        LabeledRow("Annual Revenue", account.annualRevenue.map { "$\($0)" })
                        LabeledRow("Employees", account.employeeCount.map(String.init))
                        if let website = account.website, !website.isEmpty {
                            LabeledLinkRow("Website", website)
                        }
                    }

                    if let billing = account.billingAddress, !billing.isEmpty {
                        Section("Billing Address") { Text(billing) }
                    }

                    if let shipping = account.shippingAddress, !shipping.isEmpty {
                        Section("Shipping Address") { Text(shipping) }
                    }

                    if let description = account.description, !description.isEmpty {
                        Section("Description") { Text(description) }
                    }

                    Section("Activity") {
                        LabeledRow("Last Activity", account.lastActivityAt)
                        LabeledRow("Created", account.createdAt)
                        LabeledRow("Updated", account.updatedAt)
                    }
                }
            }
        }
        .navigationTitle(viewModel.account?.name ?? "Account")
        .navigationBarTitleDisplayMode(.inline)
        .task { await viewModel.load(id: accountID, authManager: authManager) }
    }
}

private struct LabeledRow: View {
    let label: String
    let value: String?

    init(_ label: String, _ value: String?) {
        self.label = label
        self.value = value
    }

    var body: some View {
        if let value, !value.isEmpty {
            HStack {
                Text(label).foregroundStyle(.secondary)
                Spacer()
                Text(value)
            }
        }
    }
}

private struct LabeledLinkRow: View {
    let label: String
    let urlString: String

    init(_ label: String, _ urlString: String) {
        self.label = label
        self.urlString = urlString
    }

    var body: some View {
        HStack {
            Text(label).foregroundStyle(.secondary)
            Spacer()
            if let url = URL(string: urlString.hasPrefix("http") ? urlString : "https://\(urlString)") {
                Link(urlString, destination: url)
                    .lineLimit(1)
            } else {
                Text(urlString)
            }
        }
    }
}
