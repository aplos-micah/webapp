import SwiftUI

struct OpportunityFormView: View {
    let mode: FormMode
    let existing: OpportunityDetail?
    let onSaved: () -> Void

    @EnvironmentObject private var authManager: AuthManager
    @Environment(\.dismiss) private var dismiss

    @State private var draft: OpportunityRequest
    @State private var isSaving = false
    @State private var errorMessage: String?

    init(mode: FormMode, existing: OpportunityDetail? = nil, onSaved: @escaping () -> Void) {
        self.mode = mode
        self.existing = existing
        self.onSaved = onSaved
        _draft = State(initialValue: OpportunityRequest(from: existing))
    }

    var body: some View {
        NavigationStack {
            Form {
                Section(header: SectionHeader("Overview")) {
                    TextField("Name", text: $draft.opportunityName)
                        .font(AplosFont.body(15))
                    Picker("Stage", selection: $draft.stage.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityStage.all, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Type", selection: $draft.opportunityType.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.types, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Lead Source", selection: $draft.leadSource.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.leadSources, id: \.self) { Text($0).tag($0) }
                    }
                    TextField("Account ID", text: $draft.accountID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    TextField("Contact ID", text: $draft.contactID.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Forecast")) {
                    TextField("Amount", text: $draft.amount.orEmpty)
                        .keyboardType(.decimalPad)
                        .font(AplosFont.body(15))
                    TextField("Probability (%)", text: $draft.probability.orEmptyString)
                        .keyboardType(.numberPad)
                        .font(AplosFont.body(15))
                    Picker("Forecast Category", selection: $draft.forecastCategory.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.forecastCategories, id: \.self) { Text($0).tag($0) }
                    }
                    TextField("Close Date (YYYY-MM-DD)", text: $draft.closeDate.orEmpty)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Deal Details")) {
                    Toggle("Budget Confirmed", isOn: Binding(
                        get: { draft.budgetConfirmed == 1 },
                        set: { draft.budgetConfirmed = $0 ? 1 : 0 }
                    ))
                    Picker("Decision Timeline", selection: $draft.decisionTimeline.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.decisionTimelines, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Plan Type", selection: $draft.planType.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.planTypes, id: \.self) { Text($0).tag($0) }
                    }
                    Picker("Billing Term", selection: $draft.billingTerm.orEmpty) {
                        Text("None").tag("")
                        ForEach(OpportunityEnums.billingTerms, id: \.self) { Text($0).tag($0) }
                    }
                    if draft.stage.map({ OpportunityStage.closed.contains($0) }) == true {
                        Picker("Loss Reason", selection: $draft.lossReason.orEmpty) {
                            Text("None").tag("")
                            ForEach(OpportunityEnums.lossReasons, id: \.self) { Text($0).tag($0) }
                        }
                    }
                }

                Section(header: SectionHeader("Stakeholders Identified")) {
                    TextField("Stakeholders Identified", text: $draft.stakeholdersIdentified.orEmpty, axis: .vertical)
                        .font(AplosFont.body(15))
                }

                Section(header: SectionHeader("Competitor")) {
                    TextField("Competitor", text: $draft.competitor.orEmpty, axis: .vertical)
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
            .navigationTitle(isEditing ? "Edit Opportunity" : "New Opportunity")
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
                            .disabled(draft.opportunityName.trimmingCharacters(in: .whitespaces).isEmpty)
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
                _ = try await client.createOpportunity(draft)
            case .edit(let id):
                _ = try await client.updateOpportunity(id: id, draft)
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
