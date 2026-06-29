import SwiftUI

@MainActor
final class ActivityTypeDetailViewModel: ObservableObject {
    @Published var activityType: ActivityType?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            activityType = try await APIClient(accessToken: token).fetchActivityType(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ActivityTypeDetailView: View {
    let activityTypeID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ActivityTypeDetailViewModel()
    @State private var isEditingPresented = false

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.activityType == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let activityType = viewModel.activityType {
                Form {
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Name", activityType.name)
                        LabeledRow("Average Cost", activityType.averageCost.map { "$\($0)" })
                        LabeledRow("Active", activityType.isActive == 1 ? "Yes" : "No")
                    }

                    if let description = activityType.description, !description.isEmpty {
                        Section(header: SectionHeader("Description")) {
                            Text(description).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Created", activityType.createdAt)
                        LabeledRow("Updated", activityType.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.activityType?.name ?? "Activity Type")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.activityType != nil {
                    Button("Edit") { isEditingPresented = true }
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            ActivityTypeFormView(mode: .edit(activityTypeID), existing: viewModel.activityType) {
                Task { await viewModel.load(id: activityTypeID, authManager: authManager) }
            }
        }
        .task { await viewModel.load(id: activityTypeID, authManager: authManager) }
    }
}
