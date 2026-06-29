import SwiftUI

struct ContactFormView: View {
    let mode: FormMode
    let existing: ContactDetail?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: ContactRequest
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: ContactDetail? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: ContactRequest(from: existing))
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("First Name", text: $draft.firstName)
                        .font(AplosFont.body(15))
                    TextField("Last Name", text: $draft.lastName)
                        .font(AplosFont.body(15))
                    TextField("Job Title", text: $draft.jobTitle.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Status", text: $draft.status.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Lifecycle Stage", text: $draft.lifecycleStage.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Lead Source", text: $draft.leadSource.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Industry", text: $draft.industry.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Buying Role", text: $draft.buyingRole.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Account ID", text: $draft.accountID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Contact Info")) {
                    TextField("Email", text: $draft.email.orEmpty)
                        .keyboardType(.emailAddress)
                        .autocorrectionDisabled()
                        .font(AplosFont.body(15))
                    TextField("Work Phone", text: $draft.workPhone.orEmpty)
                        .keyboardType(.phonePad)
                        .font(AplosFont.body(15))
                    TextField("Mobile Phone", text: $draft.mobilePhone.orEmpty)
                        .keyboardType(.phonePad)
                        .font(AplosFont.body(15))
                    TextField("LinkedIn URL", text: $draft.linkedinURL.orEmpty)
                        .keyboardType(.URL)
                        .autocorrectionDisabled()
                        .font(AplosFont.body(15))
                    TextField("Communication Preference", text: $draft.communicationPreference.orEmpty)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Mailing Address")) {
                    TextField("Mailing Address", text: $draft.mailingAddress.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Lead & Renewal")) {
                    TextField("Lead Score", text: $draft.leadScore.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Renewal Date (YYYY-MM-DD)", text: $draft.renewalDate.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Last Contact (YYYY-MM-DD)", text: $draft.lastContactAt.orEmpty)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Interaction History")) {
                    TextField("Interaction History", text: $draft.interactionHistory.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                }

                if let errorMessage {
                    Text(errorMessage)
                        .font(AplosFont.body(13))
                        .foregroundStyle(Color.aplosOrange)
                }
            }
            .scrollContentBackground(.hidden)
            .background(Color.aplosIce)
            .navigationTitle(isEditing ? "Edit Contact" : "New Contact")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    if isSaving {
                        ProgressView()
                    } else {
                        Button("Save") { Task { await save() } }
                            .disabled(
                                draft.firstName.trimmingCharacters(in: .whitespaces).isEmpty
                                || draft.lastName.trimmingCharacters(in: .whitespaces).isEmpty
                            )
                    }
                }
            }
        }
    }

    private var isEditing: Bool {
        if case .edit = mode { return true }
        return false
    }

    private func save() async {
        guard let token = authManager.accessToken else { return }
        isSaving = true
        errorMessage = nil
        defer { isSaving = false }

        do {
            let client = APIClient(accessToken: token)
            switch mode {
            case .create:
                _ = try await client.createContact(draft)
            case .edit(let id):
                _ = try await client.updateContact(id: id, draft)
            }
            onSaved()
            dismiss()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}
