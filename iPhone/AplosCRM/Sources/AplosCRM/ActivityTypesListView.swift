import SwiftUI

@MainActor
final class ActivityTypesListViewModel: ObservableObject {
    @Published var activityTypes: [ActivityType] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            activityTypes = try await APIClient(accessToken: token).fetchActivityTypes()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ActivityTypesListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ActivityTypesListViewModel()
    @State private var creatingActivityType = false

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.activityTypes.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.activityTypes.isEmpty {
                    ContentUnavailableMessage(error: "No activity types found.")
                } else {
                    List(viewModel.activityTypes) { activityType in
                        NavigationLink(value: activityType.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(activityType.name)
                                    .font(AplosFont.headline(17, weight: .semibold))
                                    .foregroundStyle(Color.aplosNavy)
                                Text(activityType.isActive == 1 ? "Active" : "Inactive")
                                    .font(AplosFont.body(13))
                                    .foregroundStyle(activityType.isActive == 1 ? Color.aplosGreen : Color.aplosOrange)
                            }
                        }
                    }
                    .scrollContentBackground(.hidden)
                    .background(Color.aplosIce)
                    .navigationDestination(for: Int.self) { activityTypeID in
                        ActivityTypeDetailView(activityTypeID: activityTypeID)
                    }
                }
            }
            .navigationTitle("Activity Types")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { creatingActivityType = true } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $creatingActivityType) {
                ActivityTypeFormView(mode: .create) {
                    Task { await viewModel.load(authManager: authManager) }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}
