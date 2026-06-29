import SwiftUI

@MainActor
final class OpportunityLineItemsViewModel: ObservableObject {
    @Published var lineItems: [LineItem] = []
    @Published var errorMessage: String?

    func load(opportunityID: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        errorMessage = nil
        do {
            lineItems = try await APIClient(accessToken: token).fetchLineItems(opportunityID: opportunityID)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func delete(opportunityID: Int, lineItemID: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        do {
            try await APIClient(accessToken: token).deleteLineItem(opportunityID: opportunityID, id: lineItemID)
            lineItems.removeAll { $0.id == lineItemID }
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

@MainActor
final class OpportunityDetailViewModel: ObservableObject {
    @Published var opportunity: OpportunityDetail?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            opportunity = try await APIClient(accessToken: token).fetchOpportunity(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct OpportunityDetailView: View {
    let opportunityID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = OpportunityDetailViewModel()
    @StateObject private var lineItemsViewModel = OpportunityLineItemsViewModel()
    @State private var isEditingPresented = false
    @State private var lineItemFormPresentation: LineItemFormPresentation?

    private var isClosed: Bool {
        guard let stage = viewModel.opportunity?.stage else { return false }
        return OpportunityStage.closed.contains(stage)
    }

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.opportunity == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let opportunity = viewModel.opportunity {
                Form {
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Name", opportunity.opportunityName)
                        LabeledRow("Stage", opportunity.stage)
                        LabeledRow("Type", opportunity.opportunityType)
                        LabeledRow("Lead Source", opportunity.leadSource)
                        LabeledRow("Account", opportunity.accountName)
                        LabeledRow("Contact", opportunity.contactName)
                    }

                    Section(header: SectionHeader("Forecast")) {
                        LabeledRow("Amount", opportunity.amount.map { "$\($0)" })
                        LabeledRow("Probability", opportunity.probability.map { "\($0)%" })
                        LabeledRow("Forecast Category", opportunity.forecastCategory)
                        LabeledRow("Close Date", opportunity.closeDate)
                    }

                    Section(header: SectionHeader("Deal Details")) {
                        LabeledRow("Budget Confirmed", opportunity.budgetConfirmed == 1 ? "Yes" : "No")
                        LabeledRow("Decision Timeline", opportunity.decisionTimeline)
                        LabeledRow("Plan Type", opportunity.planType)
                        LabeledRow("Billing Term", opportunity.billingTerm)
                        if let loss = opportunity.lossReason, !loss.isEmpty {
                            LabeledRow("Loss Reason", loss)
                        }
                    }

                    if let stakeholders = opportunity.stakeholdersIdentified, !stakeholders.isEmpty {
                        Section(header: SectionHeader("Stakeholders Identified")) {
                            Text(stakeholders).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let competitor = opportunity.competitor, !competitor.isEmpty {
                        Section(header: SectionHeader("Competitor")) {
                            Text(competitor).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let description = opportunity.description, !description.isEmpty {
                        Section(header: SectionHeader("Description")) {
                            Text(description).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let billTo = opportunity.billToLocationName, !billTo.isEmpty {
                        Section(header: SectionHeader("Bill To")) {
                            LabeledRow("Location", billTo)
                            LabeledRow("Street", opportunity.billToStreet)
                            LabeledRow("City", opportunity.billToCity)
                            LabeledRow("State", opportunity.billToState)
                            LabeledRow("Zip", opportunity.billToZip)
                        }
                    }

                    Section(header: SectionHeader("Line Items")) {
                        ForEach(lineItemsViewModel.lineItems) { item in
                            Button {
                                lineItemFormPresentation = LineItemFormPresentation(mode: .edit(item.id), lineItem: item)
                            } label: {
                                VStack(alignment: .leading, spacing: 2) {
                                    Text(item.productName)
                                        .font(AplosFont.body(15, weight: .semibold))
                                        .foregroundStyle(Color.aplosNavy)
                                    if let total = item.totalPrice {
                                        Text("$\(total)")
                                            .font(AplosFont.body(13))
                                            .foregroundStyle(Color.aplosMidBlue)
                                    }
                                }
                            }
                        }
                        .onDelete { offsets in
                            for index in offsets {
                                let item = lineItemsViewModel.lineItems[index]
                                Task {
                                    await lineItemsViewModel.delete(opportunityID: opportunityID, lineItemID: item.id, authManager: authManager)
                                }
                            }
                        }
                        if !isClosed {
                            Button {
                                lineItemFormPresentation = LineItemFormPresentation(mode: .create, lineItem: nil)
                            } label: {
                                Text("Add Line Item")
                            }
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Created", opportunity.createdAt)
                        LabeledRow("Updated", opportunity.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.opportunity?.opportunityName ?? "Opportunity")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.opportunity != nil {
                    Button("Edit") { isEditingPresented = true }
                        .disabled(isClosed)
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            OpportunityFormView(mode: .edit(opportunityID), existing: viewModel.opportunity) {
                Task { await viewModel.load(id: opportunityID, authManager: authManager) }
            }
        }
        .sheet(item: $lineItemFormPresentation) { presentation in
            LineItemFormView(opportunityID: opportunityID, mode: presentation.mode, existing: presentation.lineItem) {
                Task {
                    await lineItemsViewModel.load(opportunityID: opportunityID, authManager: authManager)
                    await viewModel.load(id: opportunityID, authManager: authManager)
                }
            }
        }
        .task { await viewModel.load(id: opportunityID, authManager: authManager) }
        .task { await lineItemsViewModel.load(opportunityID: opportunityID, authManager: authManager) }
    }
}

struct LineItemFormPresentation: Identifiable {
    let mode: FormMode
    let lineItem: LineItem?
    var id: String { mode.id }
}
