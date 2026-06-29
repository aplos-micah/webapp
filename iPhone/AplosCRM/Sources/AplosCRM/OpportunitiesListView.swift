import SwiftUI

@MainActor
final class OpportunitiesListViewModel: ObservableObject {
    @Published var opportunities: [Opportunity] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            opportunities = try await APIClient(accessToken: token).fetchOpportunities()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct OpportunitiesListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = OpportunitiesListViewModel()
    @State private var creatingOpportunity = false

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.opportunities.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.opportunities.isEmpty {
                    ContentUnavailableMessage(error: "No opportunities found.")
                } else {
                    List(viewModel.opportunities) { opportunity in
                        NavigationLink(value: opportunity.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(opportunity.opportunityName)
                                    .font(AplosFont.headline(17, weight: .semibold))
                                    .foregroundStyle(Color.aplosNavy)
                                HStack {
                                    if let stage = opportunity.stage {
                                        Text(stage)
                                            .font(AplosFont.body(13))
                                            .foregroundStyle(
                                                OpportunityStage.closed.contains(stage) ? Color.aplosGreen : Color.aplosMidBlue
                                            )
                                    }
                                    if let amount = opportunity.amount {
                                        Text("$\(amount)")
                                            .font(AplosFont.body(13))
                                            .foregroundStyle(Color.aplosMidBlue)
                                    }
                                }
                            }
                        }
                    }
                    .scrollContentBackground(.hidden)
                    .background(Color.aplosIce)
                    .navigationDestination(for: Int.self) { opportunityID in
                        OpportunityDetailView(opportunityID: opportunityID)
                    }
                }
            }
            .navigationTitle("Opportunities")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { creatingOpportunity = true } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $creatingOpportunity) {
                OpportunityFormView(mode: .create) {
                    Task { await viewModel.load(authManager: authManager) }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}
