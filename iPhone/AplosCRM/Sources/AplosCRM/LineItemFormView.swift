import SwiftUI

struct LineItemFormView: View {
    let opportunityID: Int
    let mode: FormMode
    let existing: LineItem?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: LineItemRequest
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(opportunityID: Int, mode: FormMode, existing: LineItem? = nil, onSaved: @escaping () -> Void) {
        self.opportunityID = opportunityID
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: LineItemRequest(from: existing))
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Product Name", text: $draft.productName)
                        .font(AplosFont.body(15))
                    TextField("Product Definition ID", text: $draft.productDefinitionID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    Picker("Revenue Schedule", selection: $draft.revenueScheduleType.orEmpty) {
                        ForEach(RevenueScheduleType.all, id: \.self) { Text($0).tag($0) }
                    }
                }

                Section(header: SectionHeader("Pricing")) {
                    TextField("Unit Price", text: $draft.unitPrice.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Quantity", text: $draft.quantity.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Discount %", text: $draft.discountPercentage.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Discount Amount", text: $draft.discountAmount.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Fulfillment")) {
                    TextField("Service Date (YYYY-MM-DD)", text: $draft.serviceDate.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Subscription Term (months)", text: $draft.subscriptionTerm.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Ship To Location ID", text: $draft.shipToLocationID.orEmptyString)
                        .keyboardType(.numberPad)
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
            .navigationTitle(isEditing ? "Edit Line Item" : "New Line Item")
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
                            .disabled(draft.productName.trimmingCharacters(in: .whitespaces).isEmpty)
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
                _ = try await client.createLineItem(opportunityID: opportunityID, draft)
            case .edit(let id):
                _ = try await client.updateLineItem(opportunityID: opportunityID, id: id, draft)
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
