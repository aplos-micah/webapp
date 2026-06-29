import SwiftUI

struct RootView: View {
    @EnvironmentObject private var authManager: AuthManager

    var body: some View {
        if authManager.isAuthenticated {
            TabView {
                AccountsListView()
                    .tabItem { Label("Accounts", systemImage: "building.2") }
                ContactsListView()
                    .tabItem { Label("Contacts", systemImage: "person.2") }
                OpportunitiesListView()
                    .tabItem { Label("Opportunities", systemImage: "chart.line.uptrend.xyaxis") }
                ProductsListView()
                    .tabItem { Label("Products", systemImage: "shippingbox") }
                ActivitiesListView()
                    .tabItem { Label("Activities", systemImage: "clock") }
                ActivityTypesListView()
                    .tabItem { Label("Activity Types", systemImage: "tag") }
            }
            .tint(Color.aplosGreen)
        } else {
            LoginView()
        }
    }
}
