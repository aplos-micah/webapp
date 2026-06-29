import SwiftUI

@MainActor
final class ContactDetailViewModel: ObservableObject {
    @Published var contact: ContactDetail?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            contact = try await APIClient(accessToken: token).fetchContact(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ContactDetailView: View {
    let contactID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ContactDetailViewModel()
    @State private var isEditingPresented = false

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.contact == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let contact = viewModel.contact {
                Form {
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Name", contact.name)
                        LabeledRow("Job Title", contact.jobTitle)
                        LabeledRow("Company", contact.company)
                        LabeledRow("Status", contact.status)
                        LabeledRow("Lifecycle Stage", contact.lifecycleStage)
                        LabeledRow("Lead Source", contact.leadSource)
                        LabeledRow("Industry", contact.industry)
                        LabeledRow("Buying Role", contact.buyingRole)
                    }

                    Section(header: SectionHeader("Contact Info")) {
                        LabeledRow("Email", contact.email)
                        LabeledRow("Work Phone", contact.workPhone)
                        LabeledRow("Mobile Phone", contact.mobilePhone)
                        if let linkedin = contact.linkedinURL, !linkedin.isEmpty {
                            LabeledLinkRow("LinkedIn", linkedin)
                        }
                        LabeledRow("Communication Preference", contact.communicationPreference)
                    }

                    if let mailing = contact.mailingAddress, !mailing.isEmpty {
                        Section(header: SectionHeader("Mailing Address")) {
                            Text(mailing).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Lead & Renewal")) {
                        LabeledRow("Lead Score", contact.leadScore.map(String.init))
                        LabeledRow("Renewal Date", contact.renewalDate)
                        LabeledRow("Last Contact", contact.lastContactAt)
                    }

                    if let history = contact.interactionHistory, !history.isEmpty {
                        Section(header: SectionHeader("Interaction History")) {
                            Text(history).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Created", contact.createdAt)
                        LabeledRow("Updated", contact.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.contact?.name ?? "Contact")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.contact != nil {
                    Button("Edit") { isEditingPresented = true }
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            ContactFormView(mode: .edit(contactID), existing: viewModel.contact) {
                Task { await viewModel.load(id: contactID, authManager: authManager) }
            }
        }
        .task { await viewModel.load(id: contactID, authManager: authManager) }
    }
}
