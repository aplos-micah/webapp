import SwiftUI

struct SectionHeader: View {
    let title: String

    init(_ title: String) {
        self.title = title
    }

    var body: some View {
        Text(title)
            .font(AplosFont.headline(13, weight: .semibold))
            .foregroundStyle(Color.aplosMidBlue)
    }
}

struct LabeledRow: View {
    let label: String
    let value: String?

    init(_ label: String, _ value: String?) {
        self.label = label
        self.value = value
    }

    var body: some View {
        if let value, !value.isEmpty {
            HStack {
                Text(label)
                    .font(AplosFont.body(14))
                    .foregroundStyle(Color.aplosMidBlue)
                Spacer()
                Text(value)
                    .font(AplosFont.body(15))
                    .foregroundStyle(Color.aplosNavy)
            }
        }
    }
}

struct ContentUnavailableMessage: View {
    let error: String

    var body: some View {
        VStack(spacing: 8) {
            Image(systemName: "exclamationmark.triangle")
                .font(.largeTitle)
                .foregroundStyle(.secondary)
            Text(error)
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 32)
        }
    }
}

struct LabeledLinkRow: View {
    let label: String
    let urlString: String

    init(_ label: String, _ urlString: String) {
        self.label = label
        self.urlString = urlString
    }

    var body: some View {
        HStack {
            Text(label)
                .font(AplosFont.body(14))
                .foregroundStyle(Color.aplosMidBlue)
            Spacer()
            if let url = URL(string: urlString.hasPrefix("http") ? urlString : "https://\(urlString)") {
                Link(urlString, destination: url)
                    .font(AplosFont.body(15))
                    .tint(Color.aplosGreen)
                    .lineLimit(1)
            } else {
                Text(urlString)
                    .font(AplosFont.body(15))
            }
        }
    }
}
