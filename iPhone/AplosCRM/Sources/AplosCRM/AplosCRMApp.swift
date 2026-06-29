import SwiftUI

@main
struct AplosCRMApp: App {
    @StateObject private var authManager = AuthManager()

    init() {
        let appearance = UINavigationBarAppearance()
        appearance.configureWithOpaqueBackground()
        appearance.backgroundColor = UIColor(Color.aplosNavbar)
        appearance.titleTextAttributes = [.foregroundColor: UIColor.white]
        appearance.largeTitleTextAttributes = [.foregroundColor: UIColor.white]
        UINavigationBar.appearance().standardAppearance = appearance
        UINavigationBar.appearance().scrollEdgeAppearance = appearance
        UINavigationBar.appearance().compactAppearance = appearance
        UINavigationBar.appearance().tintColor = UIColor(Color.aplosGreen)
    }

    var body: some Scene {
        WindowGroup {
            RootView()
                .environmentObject(authManager)
                .tint(.aplosGreen)
        }
    }
}
