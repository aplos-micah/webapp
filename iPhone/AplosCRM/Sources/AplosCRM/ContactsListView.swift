import SwiftUI

@MainActor
final class ContactsListViewModel: ObservableObject {
    @Published var contacts: [Contact] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            contacts = try await APIClient(accessToken: token).fetchContacts()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ContactsListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ContactsListViewModel()
    @State private var creatingContact = false

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.contacts.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.contacts.isEmpty {
                    ContentUnavailableMessage(error: "No contacts found.")
                } else {
                    List(viewModel.contacts) { contact in
                        NavigationLink(value: contact.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(contact.name)
                                    .font(AplosFont.headline(17, weight: .semibold))
                                    .foregroundStyle(Color.aplosNavy)
                                if let accountName = contact.accountName, !accountName.isEmpty {
                                    Text(accountName)
                                        .font(AplosFont.body(13))
                                        .foregroundStyle(Color.aplosMidBlue)
                                }
                            }
                        }
                    }
                    .scrollContentBackground(.hidden)
                    .background(Color.aplosIce)
                    .navigationDestination(for: Int.self) { contactID in
                        ContactDetailView(contactID: contactID)
                    }
                }
            }
            .navigationTitle("Contacts")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { creatingContact = true } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $creatingContact) {
                ContactFormView(mode: .create) {
                    Task { await viewModel.load(authManager: authManager) }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}
