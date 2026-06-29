import SwiftUI

struct LocationFormView: View {
    let accountID: Int
    let mode: FormMode
    let existing: Location?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: LocationRequest
    @State private var isPrimary: Bool
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(accountID: Int, mode: FormMode, existing: Location? = nil, onSaved: @escaping () -> Void) {
        self.accountID = accountID
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: LocationRequest(from: existing))
        _isPrimary = State(initialValue: (existing?.isPrimary ?? 0) == 1)
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Name", text: $draft.locationName)
                        .font(AplosFont.body(15))
                    Picker("Type", selection: $draft.locationType.orEmpty) {
                        Text("None").tag("")
                        ForEach(LocationEnums.types, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Status", selection: $draft.locationStatus.orEmpty) {
                        ForEach(LocationEnums.statuses, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Validation Status", selection: $draft.validationStatus.orEmpty) {
                        ForEach(LocationEnums.validationStatuses, id: \.self) { Text($0).tag($0) }
                    }
                    Toggle("Primary Location", isOn: $isPrimary)
                }

                Section(header: SectionHeader("Address")) {
                    TextField("Street Address 1", text: $draft.streetAddress1.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Street Address 2", text: $draft.streetAddress2.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("City", text: $draft.city.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("State / Province", text: $draft.stateProvince.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Zip / Postal Code", text: $draft.zipPostalCode.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Country / Region", text: $draft.countryRegion.orEmpty)
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
            .navigationTitle(isEditing ? "Edit Location" : "New Location")
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
                            .disabled(draft.locationName.trimmingCharacters(in: .whitespaces).isEmpty)
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

        draft.isPrimary = isPrimary ? 1 : 0

        do {
            let client = APIClient(accessToken: token)
            switch mode {
            case .create:
                _ = try await client.createLocation(accountID: accountID, draft)
            case .edit(let id):
                _ = try await client.updateLocation(accountID: accountID, id: id, draft)
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
