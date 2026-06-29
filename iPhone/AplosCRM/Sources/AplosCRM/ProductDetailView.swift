import SwiftUI

@MainActor
final class ProductDetailViewModel: ObservableObject {
    @Published var product: ProductDetail?
    @Published var isLoading = false
    @Published var errorMessage: String?

    func load(id: Int, authManager: AuthManager) async {
        guard let token = authManager.accessToken else { return }
        isLoading = true
        errorMessage = nil
        defer { isLoading = false }

        do {
            product = try await APIClient(accessToken: token).fetchProduct(id: id)
        } catch APIError.unauthorized {
            authManager.signOut()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct ProductDetailView: View {
    let productID: Int

    @EnvironmentObject private var authManager: AuthManager
    @StateObject private var viewModel = ProductDetailViewModel()
    @State private var isEditingPresented = false

    var body: some View {
        Group {
            if viewModel.isLoading && viewModel.product == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                Text(error)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding()
            } else if let product = viewModel.product {
                Form {
                    Section(header: SectionHeader("Overview")) {
                        LabeledRow("Name", product.productName)
                        LabeledRow("SKU", product.sku)
                        LabeledRow("Family", product.productFamily)
                        LabeledRow("Type", product.productType)
                        LabeledRow("Lifecycle Status", product.lifecycleStatus)
                        LabeledRow("Active", product.isActive == 1 ? "Yes" : "No")
                    }

                    Section(header: SectionHeader("Pricing")) {
                        LabeledRow("List Price", product.listPrice.map { "\(product.currency ?? "$")\($0)" })
                        LabeledRow("Unit Cost", product.unitCost)
                        LabeledRow("Unit of Measure", product.unitOfMeasure)
                        LabeledRow("Pricing Model", product.pricingModel)
                        LabeledRow("Tax Category", product.taxCategory)
                        LabeledRow("Subscription Term (months)", product.subscriptionTermMonths.map(String.init))
                    }

                    Section(header: SectionHeader("Specifications")) {
                        LabeledRow("Weight", product.weight)
                        LabeledRow("Dimensions", product.dimensions)
                        LabeledRow("Material", product.material)
                    }

                    if let description = product.productDescription, !description.isEmpty {
                        Section(header: SectionHeader("Description")) {
                            Text(description).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let usage = product.usageMetrics, !usage.isEmpty {
                        Section(header: SectionHeader("Usage Metrics")) {
                            Text(usage).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    if let notes = product.competitiveNotes, !notes.isEmpty {
                        Section(header: SectionHeader("Competitive Notes")) {
                            Text(notes).font(AplosFont.body(15)).foregroundStyle(Color.aplosNavy)
                        }
                    }

                    Section(header: SectionHeader("Activity")) {
                        LabeledRow("Created", product.createdAt)
                        LabeledRow("Updated", product.updatedAt)
                    }
                }
                .scrollContentBackground(.hidden)
                .background(Color.aplosIce)
            }
        }
        .navigationTitle(viewModel.product?.productName ?? "Product")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                if viewModel.product != nil {
                    Button("Edit") { isEditingPresented = true }
                }
            }
        }
        .sheet(isPresented: $isEditingPresented) {
            ProductFormView(mode: .edit(productID), existing: viewModel.product) {
                Task { await viewModel.load(id: productID, authManager: authManager) }
            }
        }
        .task { await viewModel.load(id: productID, authManager: authManager) }
    }
}
