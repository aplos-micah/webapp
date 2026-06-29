import SwiftUI

@MainActor
final class ProductsListViewModel: ObservableObject {
    @Published var products: [Product] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            products = try await APIClient(accessToken: token).fetchProducts()
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ProductsListView: View {
    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ProductsListViewModel()
    @State private var creatingProduct = false

    var body: some View {
        NavigationStack {
            Group {
                if viewModel.isLoading && viewModel.products.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage {
                    ContentUnavailableMessage(error: error)
                } else if viewModel.products.isEmpty {
                    ContentUnavailableMessage(error: "No products found.")
                } else {
                    List(viewModel.products) { product in
                        NavigationLink(value: product.id) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(product.productName)
                                    .font(AplosFont.headline(17, weight: .semibold))
                                    .foregroundStyle(Color.aplosNavy)
                                if let sku = product.sku, !sku.isEmpty {
                                    Text(sku)
                                        .font(AplosFont.body(13))
                                        .foregroundStyle(Color.aplosMidBlue)
                                }
                            }
                        }
                    }
                    .scrollContentBackground(.hidden)
                    .background(Color.aplosIce)
                    .navigationDestination(for: Int.self) { productID in
                        ProductDetailView(productID: productID)
                    }
                }
            }
            .navigationTitle("Products")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { creatingProduct = true } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $creatingProduct) {
                ProductFormView(mode: .create) {
                    Task { await viewModel.load(authManager: authManager) }
                }
            }
            .refreshable { await viewModel.load(authManager: authManager) }
            .task { await viewModel.load(authManager: authManager) }
        }
    }
}
