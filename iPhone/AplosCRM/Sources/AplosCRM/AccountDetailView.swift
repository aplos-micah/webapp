import SwiftUI

@MainActor
final class AccountLocationsViewModel: ObservableObject {
    @Published var locations: [Location] = []
    @Published var errorMessage: String?

    func load(accountID: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        errorMessage = nil
        do {
            locations = try await APIClient(accessToken: token).fetchLocations(accountID: accountID)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func delete(accountID: Int, locationID: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        do {
            try await APIClient(accessToken: token).deleteLocation(accountID: accountID, id: locationID)
            locations.removeAll { $0.id == locationID }
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

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
    @StateObject private var locationsViewModel = AccountLocationsViewModel()
    @State private var isEditingPresented = false
    @State private var locationFormPresentation: LocationFormPresentation?

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
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Name", account.name)
                        LabeledRow("Account Number", account.accountNumber)
                        LabeledRow("Type", account.type)
                        LabeledRow("Industry", account.industry)
                        LabeledRow("Status", account.status)
                        LabeledRow("Ownership", account.ownership)
                        LabeledRow("Site", account.site)
                    }

                    Section(header: SectionHeader("Business")) {
                        LabeledRow("Annual Revenue", account.annualRevenue.map { "$\($0)" })
                        LabeledRow("Employees", account.employeeCount.map(String.init))
                        if let website = account.website, !website.isEmpty {
                            LabeledLinkRow("Website", website)
                        }
                    }

                    if let billing = account.billingAddress, !billing.isEmpty {
                        Section(header: SectionHeader("Billing Address")) {
                            Text(billing).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let shipping = account.shippingAddress, !shipping.isEmpty {
                        Section(header: SectionHeader("Shipping Address")) {
                            Text(shipping).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let description = account.description, !description.isEmpty {
                        Section(header: SectionHeader("Description")) {
                            Text(description).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Locations")) {
                        ForEach(locationsViewModel.locations) { location in
                            Button {
                                locationFormPresentation = LocationFormPresentation(mode: .edit(location.id), location: location)
                            } label: {
                                VStack(alignment: .leading, spacing: 2) {
                                    Text(location.locationName ?? "Location")
                                        .font(AplosFont.body(15, weight: .semibold))
                                        .foregroundStyle(Color.aplosNavy)
                                    if let city = location.city, !city.isEmpty {
                                        Text(city)
                                            .font(AplosFont.body(13))
                                            .foregroundStyle(Color.aplosMidBlue)
                                    }
                                }
                            }
                        }
                        .onDelete { offsets in
                            for index in offsets {
                                let location = locationsViewModel.locations[index]
                                Task {
                                    await locationsViewModel.delete(accountID: accountID, locationID: location.id, authManager: authManager)
                                }
                            }
                        }
                        Button {
                            locationFormPresentation = LocationFormPresentation(mode: .create, location: nil)
                        } label: {
                            Text("Add Location")
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Last Activity", account.lastActivityAt)
                        LabeledRow("Created", account.createdAt)
                        LabeledRow("Updated", account.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.account?.name ?? "Account")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.account != nil {
                    Button("Edit") { isEditingPresented = true }
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            AccountFormView(mode: .edit(accountID), existing: viewModel.account) {
                Task { await viewModel.load(id: accountID, authManager: authManager) }
            }
        }
        .sheet(item: $locationFormPresentation) { presentation in
            LocationFormView(accountID: accountID, mode: presentation.mode, existing: presentation.location) {
                Task { await locationsViewModel.load(accountID: accountID, authManager: authManager) }
            }
        }
        .task { await viewModel.load(id: accountID, authManager: authManager) }
        .task { await locationsViewModel.load(accountID: accountID, authManager: authManager) }
    }
}

struct LocationFormPresentation: Identifiable {
    let mode: FormMode
    let location: Location?
    var id: String { mode.id }
}
