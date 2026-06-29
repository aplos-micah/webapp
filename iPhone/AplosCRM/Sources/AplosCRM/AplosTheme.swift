import SwiftUI

/// Colors and type mirrored from AplosSuite Brand Style Guide v1.0
/// (public_html/assets/css/main.css design tokens).
extension Color {
    static let aplosNavy = Color(red: 0x0B / 255, green: 0x3D / 255, blue: 0x6B / 255)
    static let aplosNavbar = Color(red: 0x06 / 255, green: 0x1F / 255, blue: 0x38 / 255)
    static let aplosGreen = Color(red: 0x2E / 255, green: 0xCC / 255, blue: 0x71 / 255)
    static let aplosOrange = Color(red: 0xFF / 255, green: 0x8C / 255, blue: 0x00 / 255)
    static let aplosMidBlue = Color(red: 0x15 / 255, green: 0x6B / 255, blue: 0xA5 / 255)
    static let aplosSky = Color(red: 0xD6 / 255, green: 0xEA / 255, blue: 0xF8 / 255)
    static let aplosIce = Color(red: 0xF0 / 255, green: 0xF7 / 255, blue: 0xFF / 255)
}

enum AplosFont {
    static func headline(_ size: CGFloat, weight: Font.Weight = .regular) -> Font {
        .custom("Lora-Regular", size: size).weight(weight)
    }

    static func body(_ size: CGFloat, weight: Font.Weight = .regular) -> Font {
        .custom("OpenSans-Regular", size: size).weight(weight)
    }
}
