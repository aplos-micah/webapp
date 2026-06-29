import SwiftUI

struct RootView: View {
    @EnvironmentObject private var authManager: AuthManager

    var body: some View {
        if authManager.isAuthenticated {
            AccountsListView()
        } else {
            LoginView()
        }
    }
}
