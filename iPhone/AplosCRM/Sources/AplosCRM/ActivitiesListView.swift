import SwiftUI

@MainActor
final class ActivitiesListViewModel: ObservableObject {
    @Published var activities: [Activity] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            activities = try await APIClient(accessToken: token).fetchActivities()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ActivitiesListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ActivitiesListViewModel()
    @State private var creatingActivity = false

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.activities.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.activities.isEmpty {
                    ContentUnavailableMessage(error: "No activities found.")
                } else {
                    List(viewModel.activities) { activity in
                        NavigationLink(value: activity.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(activity.typeName ?? "Activity")
                                    .font(AplosFont.headline(17, weight: .semibold))
                                    .foregroundStyle(Color.aplosNavy)
                                Text(activity.accountName ?? activity.contactName ?? activity.opportunityName ?? activity.activityDate)
                                    .font(AplosFont.body(13))
                                    .foregroundStyle(Color.aplosMidBlue)
                            }
                        }
                    }
                    .scrollContentBackground(.hidden)
                    .background(Color.aplosIce)
                    .navigationDestination(for: Int.self) { activityID in
                        ActivityDetailView(activityID: activityID)
                    }
                }
            }
            .navigationTitle("Activities")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { creatingActivity = true } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $creatingActivity) {
                ActivityFormView(mode: .create) {
                    Task { await viewModel.load(authManager: authManager) }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}
