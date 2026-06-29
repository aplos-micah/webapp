import SwiftUI

struct LoginView: View {
    @EnvironmentObject private var authManager: AuthManager

    var body: some View {
        ZStack {
            Color.aplosIce.ignoresSafeArea()

            VStack(spacing: 24) {
                Spacer()

                Image(systemName: "building.2.crop.circle")
                    .resizable()
                    .scaledToFit()
                    .frame(width: 72, height: 72)
                    .foregroundStyle(Color.aplosNavy)

                Text("AplosCRM")
                    .font(AplosFont.headline(34, weight: .bold))
                    .foregroundStyle(Color.aplosNavy)

                Text("Sign in with your AplosCRM account to view your accounts.")
                    .font(AplosFont.body(15))
                    .foregroundStyle(Color.aplosNavy.opacity(0.7))
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 32)

                if let error = authManager.lastError {
                    Text(error)
                        .font(AplosFont.body(13))
                        .foregroundStyle(Color.aplosOrange)
                        .multilineTextAlignment(.center)
                        .padding(.horizontal, 32)
                }

                Button(action: authManager.signIn) {
                    Text("Sign In")
                        .font(AplosFont.body(17, weight: .semibold))
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(Color.aplosGreen)
                .padding(.horizontal, 48)

                Spacer()
            }
        }
    }
}
