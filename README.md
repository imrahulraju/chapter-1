# Chapter 1 — Reading Room & Study Space
## Premium Landing Page

A premium, minimal, editorial-style landing page for **Chapter 1 Reading Room & Study Space**, Tripunithura, Ernakulam.

---

## 📁 Project Structure

```
Librory/
├── index.html              ← Main HTML file (open this in browser)
├── css/
│   ├── style.css           ← Production CSS (compiled, ready to use)
│   └── style.scss          ← SCSS source (for future editing)
├── js/
│   └── main.js             ← All interactions & animations
└── assets/
    └── images/
        ├── logo.png         ← Chapter 1 logo (REQUIRED)
        ├── hero-bg.jpg      ← Hero background — study room interior
        ├── about-interior.jpg  ← About section photo
        ├── gallery-1.jpg    ← Gallery main (large)
        ├── gallery-2.jpg    ← Gallery top-right
        ├── gallery-3.jpg    ← Gallery bottom-right
        ├── gallery-4.jpg    ← Gallery strip 1
        ├── gallery-5.jpg    ← Gallery strip 2
        ├── gallery-6.jpg    ← Gallery strip 3
        ├── audience-1.jpg   ← Students card
        ├── audience-2.jpg   ← Competitive exam aspirants card
        ├── audience-3.jpg   ← Professionals card
        └── audience-4.jpg   ← Self-learners card
```

---

## 🖼️ Image Guidelines

### Hero Background (`hero-bg.jpg`)
- **Size**: 1920 × 1080px minimum (or 2560 × 1440 for retina)
- **Content**: Wide-angle interior — desks, warm lighting, calm atmosphere
- **Tip**: The image has a deep navy overlay; darker/moodier images work best

### About Interior (`about-interior.jpg`)
- **Size**: 800 × 1067px (portrait 3:4 ratio)
- **Content**: Close shot of desk setup or reading area

### Gallery Images
- `gallery-1.jpg` — Main large image, portrait preferred (3:4)
- `gallery-2.jpg`, `gallery-3.jpg` — Landscape (4:3)
- `gallery-4.jpg` through `gallery-6.jpg` — Wide (16:9)

### Audience Cards (optional)
- Atmospheric, monochrome-friendly interior/people shots
- Displayed with dark navy overlay and desaturated filter
- Can be skipped — the navy gradient backgrounds look great by themselves

### Logo (`logo.png`)
- Use the supplied Chapter 1 logo with transparent background
- Recommended height: 80–100px at 2x resolution
- The navbar will auto-invert it to white on the dark hero, then restore color on scroll

---

## 🚀 Getting Started

1. **Drop your images** into `assets/images/` following the filenames above
2. **Open `index.html`** in any modern browser — no server required
3. **Customise** via `css/style.css` (or `style.scss` + Sass compiler)

### Optional: Sass Compilation
```sh
npx sass css/style.scss css/style.css --style=compressed --watch
```

---

## 🎨 Brand Tokens

| Token | Value | Usage |
|---|---|---|
| `--navy-900` | `#111A2E` | Hero overlay, nav dark, card bg |
| `--navy-800` | `#17243D` | Dark sections |
| `--navy-700` | `#1F2D46` | Hover states |
| `--ivory` | `#FAF8F3` | Page background |
| `--ivory-50` | `#F4F1E9` | Alternate section bg |
| `--charcoal` | `#151515` | Body text |
| `--gold` | `#B99452` | Accent, eyebrows, icons |
| `--gold-lt` | `#D4B07A` | Gold hover states |

---

## 📱 Responsive Breakpoints

| Breakpoint | Layout changes |
|---|---|
| `≥ 1025px` | Full desktop: sticky nav with links, 2-col intro, 4-col audience grid |
| `≤ 1024px` | Hamburger menu, single-col intro, 2-col audience grid |
| `≤ 768px` | 2-col features, single-col gallery, single-col audience |
| `≤ 480px` | Single-col features, stacked CTA buttons, gallery strip hidden |

---

## ✏️ Content Updates

All content is in `index.html`. To change:
- **Price** → Membership section, `membership__amount` span
- **Phone** → Update all `href="tel:..."` and text instances
- **Email** → Update all `href="mailto:..."` and text
- **Hours** → Search for `6:00 AM – 10:30 PM` and update
- **Seats** → Search for `41` and update
- **Map** → Replace the Google Maps embed `src` in the location section

---

## 🗺️ Map Embed

Replace the iframe `src` in the Location section with your actual Google Maps embed URL:
1. Go to [maps.google.com](https://maps.google.com)
2. Search for your exact address
3. Click Share → Embed a map → Copy HTML → Use only the `src` URL

---

## ⚡ Performance Notes

- Fonts loaded via Google Fonts with `display=swap`
- Hero image uses `fetchpriority="high"` for LCP optimization
- All other images use `loading="lazy"` + `decoding="async"`
- Scroll animations use `IntersectionObserver` with CSS `animation-timeline` progressive enhancement
- No external JS dependencies — pure vanilla JS
- CSS uses `will-change` only on animated elements
