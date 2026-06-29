import SwiftUI

@MainActor
final class ActivityDetailViewModel: ObservableObject {
    @Published var activity: ActivityDetail?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            activity = try await APIClient(accessToken: token).fetchActivity(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ActivityDetailView: View {
    let activityID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ActivityDetailViewModel()
    @State private var isEditingPresented = false

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.activity == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let activity = viewModel.activity {
                Form {
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Type", activity.typeName)
                        LabeledRow("Date", activity.activityDate)
                        LabeledRow("Outcome", activity.outcome)
                        LabeledRow("Duration (min)", activity.durationMinutes.map(String.init))
                        LabeledRow("Cost", activity.cost.map { "$\($0)" })
                        LabeledRow("Owner", activity.ownerName)
                    }

                    Section(header: SectionHeader("Related To")) {
                        LabeledRow("Account", activity.accountName)
                        LabeledRow("Contact", activity.contactName)
                        LabeledRow("Opportunity", activity.opportunityName)
                    }

                    if let notes = activity.notes, !notes.isEmpty {
                        Section(header: SectionHeader("Notes")) {
                            Text(notes).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Created", activity.createdAt)
                        LabeledRow("Updated", activity.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.activity?.typeName ?? "Activity")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.activity != nil {
                    Button("Edit") { isEditingPresented = true }
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            ActivityFormView(mode: .edit(activityID), existing: viewModel.activity) {
                Task { await viewModel.load(id: activityID, authManager: authManager) }
            }
        }
        .task { await viewModel.load(id: activityID, authManager: authManager) }
    }
}
