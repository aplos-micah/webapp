import SwiftUI

struct ActivityFormView: View {
    let mode: FormMode
    let existing: ActivityDetail?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: ActivityRequest
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: ActivityDetail? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: ActivityRequest(from: existing))
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Activity Type ID", text: $draft.activityTypeID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Date (YYYY-MM-DD)", text: $draft.activityDate)
                        .font(AplosFont.body(15))
                    Picker("Outcome", selection: $draft.outcome.orEmpty) {
                        Text("None").tag("")
                        ForEach(ActivityOutcome.all, id: \.self) { outcome in
                            Text(outcome).tag(outcome)
                        }
                    }
                    .font(AplosFont.body(15))
                    TextField("Duration (minutes)", text: $draft.durationMinutes.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Cost", text: $draft.cost.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Related To (at least one)")) {
                    TextField("Account ID", text: $draft.accountID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Contact ID", text: $draft.contactID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Opportunity ID", text: $draft.opportunityID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Notes")) {
                    TextField("Notes", text: $draft.notes.orEmpty, axis: .vertical)
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
            .navigationTitle(isEditing ? "Edit Activity" : "New Activity")
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
                            .disabled(draft.activityDate.trimmingCharacters(in: .whitespaces).isEmpty)
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
                _ = try await client.createActivity(draft)
            case .edit(let id):
                _ = try await client.updateActivity(id: id, draft)
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
