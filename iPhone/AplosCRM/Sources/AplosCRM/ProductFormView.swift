import SwiftUI

struct ProductFormView: View {
    let mode: FormMode
    let existing: ProductDetail?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: ProductRequest
    @State private var isActive: Bool
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: ProductDetail? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: ProductRequest(from: existing))
        _isActive = State(initialValue: (existing?.isActive ?? 1) == 1)
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Name", text: $draft.productName)
                        .font(AplosFont.body(15))
                    TextField("SKU", text: $draft.sku.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Family", text: $draft.productFamily.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Type", text: $draft.productType.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Lifecycle Status", text: $draft.lifecycleStatus.orEmpty)
                        .font(AplosFont.body(15))
                    Toggle("Active", isOn: $isActive)
                }

                Section(header: SectionHeader("Pricing")) {
                    TextField("List Price", text: $draft.listPrice.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Currency", text: $draft.currency.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Unit Cost", text: $draft.unitCost.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Unit of Measure", text: $draft.unitOfMeasure.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Pricing Model", text: $draft.pricingModel.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Tax Category", text: $draft.taxCategory.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Subscription Term (months)", text: $draft.subscriptionTermMonths.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Specifications")) {
                    TextField("Weight", text: $draft.weight.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Dimensions", text: $draft.dimensions.orEmpty)
                        .font(AplosFont.body(15))
                    TextField("Material", text: $draft.material.orEmpty)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Description")) {
                    TextField("Description", text: $draft.productDescription.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Usage Metrics")) {
                    TextField("Usage Metrics", text: $draft.usageMetrics.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Competitive Notes")) {
                    TextField("Competitive Notes", text: $draft.competitiveNotes.orEmpty, axis: .vertical)
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
            .navigationTitle(isEditing ? "Edit Product" : "New Product")
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

        draft.isActive = isActive ? 1 : 0

        do {
            let client = APIClient(accessToken: token)
            switch mode {
            case .create:
                _ = try await client.createProduct(draft)
            case .edit(let id):
                _ = try await client.updateProduct(id: id, draft)
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
