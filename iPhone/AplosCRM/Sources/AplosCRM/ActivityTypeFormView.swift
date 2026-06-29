import SwiftUI

struct ActivityTypeFormView: View {
    let mode: FormMode
    let existing: ActivityType?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: ActivityTypeRequest
    @State private var isActive: Bool
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: ActivityType? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: ActivityTypeRequest(from: existing))
        _isActive = State(initialValue: (existing?.isActive ?? 1) == 1)
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Name", text: $draft.name)
                        .font(AplosFont.body(15))
                    TextField("Average Cost", text: $draft.averageCost.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    Toggle("Active", isOn: $isActive)
                }

                Section(header: SectionHeader("Description")) {
                    TextField("Description", text: $draft.description.orEmpty, axis: .vertical)
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
            .navigationTitle(isEditing ? "Edit Activity Type" : "New Activity Type")
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
                            .disabled(draft.name.trimmingCharacters(in: .whitespaces).isEmpty)
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

        draft.isActive = isActive ? 1 : 0

        do {
            let client = APIClient(accessToken: token)
            switch mode {
            case .create:
                _ = try await client.createActivityType(draft)
            case .edit(let id):
                _ = try await client.updateActivityType(id: id, draft)
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
