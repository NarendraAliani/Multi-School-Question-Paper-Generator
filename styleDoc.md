# Style Documentation (styleDoc.md)

## 1. Color Palette

The application uses a modern, professional color scheme with a focus on usability and visual hierarchy.

### Brand Colors
| Color Name | Hex Code | Usage |
| :--- | :--- | :--- |
| **Primary** | `#667eea` | Main buttons, active states, highlights |
| **Secondary** | `#764ba2` | Gradients, secondary actions |
| **Background**| `#f4f6f9` | Page background |
| **Dark** | `#343a40` | Text, refined borders |
| **Light** | `#f8f9fa` | Card backgrounds, light sections |

### Functional Colors
| Color Name | Hex Code | Usage |
| :--- | :--- | :--- |
| **Success** | `#28a745` | Success messages, valid states |
| **Danger** | `#dc3545` | Error messages, delete actions |
| **Warning** | `#ffc107` | Alerts, warnings |
| **Info** | `#17a2b8` | Informational badges/alerts |

---

## 2. Typography

The application uses a clean, system-based font stack for maximum performance and readability across devices.

- **Font Family**: `'Segoe UI', Tahoma, Geneva, Verdana, sans-serif`
- **Base Size**: 1rem (16px)
- **Print Size**: 12pt

---

## 3. UI Components

### Cards
- **Structure**: Rounded corners (`10px`), no border.
- **Shadow**: `box-shadow: 0 2px 10px rgba(0,0,0,0.1)`.
- **Hover Effect**: Elevates (`-5px`) with increased shadow size.
- **Headers**: Gradient background (Primary to Secondary).

### Buttons (`.btn`)
- **Primary Button**: 
  - Gradient background: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`.
  - Border radius: `6px`.
  - **Hover**: Swaps gradient direction and lifts (`-2px translateY`).

### Tables
- **Header**: Primary color background, white text, uppercase, `0.85rem` font size.
- **Rows**: Hover effect (`#f0f2ff`) for better readability.
- **Background**: White.

### Forms
- **Input Fields**: 
  - Rounded borders (`6px`).
  - **Focus State**: Primary color border with subtle shadow glow (`rgba(102, 126, 234, 0.25)`).

### Sidebar
- **Background**: White with shadow.
- **Links**: Dark text.
- **Active/Hover**: Gradient background (Primary to Secondary), white text.

---

## 4. Layout & Grid

- **Container**: Bootstrap's fluid container (`.container-fluid`) with `20px` padding.
- **Grid System**: Uses Bootstrap 5 grid system.
- **Responsiveness**: 
  - Mobile adapters for dashboard cards and alert containers.

---

## 5. Print Styles (A4)

Optimized for printing question papers on A4 sheets.

- **Page Size**: A4 with `15mm` margins.
- **Visibility**: Navigation, sidebars, and buttons are hidden (`display: none`).
- **Typography**: 
  - Body: `12pt`, Line Height: `1.5`.
  - School Name: `20pt Bold`.
- **Layout**:
  - `page-break-inside: avoid` for questions to prevent splitting.
  - Footer fixed at the bottom of the page.
  - Section headers with underlines.

---

## 6. CSS Variables
Defined in `:root` for easy theming:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --dark-color: #343a40;
    --light-color: #f8f9fa;
}
```
