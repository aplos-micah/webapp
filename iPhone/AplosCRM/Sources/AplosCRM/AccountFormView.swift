import SwiftUI

enum FormMode: Identifiable {
    case create
    case edit(Int)

    var id: String {
        switch self {
        case .create: return "create"
        case .edit(let id): return "edit-\(id)"
        }
    }
}

struct AccountFormView: View {
    let mode: FormMode
    let existing: AccountDetail?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: AccountRequest
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: AccountDetail? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: AccountRequest(from: existing))
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Name", text: $draft.name)
                        .font(AplosFont.body(15))
                    TextField("Account Number", text: $draft.accountNumber.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Type", text: $draft.type.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Industry", text: $draft.industry.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Status", text: $draft.status.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Ownership", text: $draft.ownership.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Site", text: $draft.site.orEmpty)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Business")) {
                    TextField("Annual Revenue", text: $draft.annualRevenue.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Employees", text: $draft.employeeCount.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Website", text: $draft.website.orEmpty)
                        .keyboardType(.URL)
                        .autocorrectionDisabled()
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Addresses")) {
                    TextField("Billing Address", text: $draft.billingAddress.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                    TextField("Shipping Address", text: $draft.shippingAddress.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
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
            .navigationTitle(isEditing ? "Edit Account" : "New Account")
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

        do {
            let client = APIClient(accessToken: token)
            switch mode {
            case .create:
                _ = try await client.createAccount(draft)
            case .edit(let id):
                _ = try await client.updateAccount(id: id, draft)
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

extension Binding where Value == String? {
    var orEmpty: Binding<String> {
        Binding<String>(
            get: { self.wrappedValue ?? "" },
            set: { newValue in self.wrappedValue = newValue.isEmpty ? nil : newValue }
        )
    }
}

extension Binding where Value == Int? {
    var orEmptyString: Binding<String> {
        Binding<String>(
            get: { self.wrappedValue.map(String.init) ?? "" },
            set: { newValue in self.wrappedValue = Int(newValue) }
        )
    }
}
