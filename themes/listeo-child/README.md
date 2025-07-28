# GrabToGo - Mobile-First WordPress Platform

A mobile-optimized listing platform with Stories feature, built on WordPress with Listeo theme, inspired by Kaufda.de's mobile UX.

## 🚀 Features Implemented

### ✅ Core Platform
- **OTP-verified vendor registration** with document upload
- **Dokan Pro integration** for multi-vendor functionality
- **WooCommerce backend** (cart disabled, listing-only)
- **Auto-filled listing forms** from vendor data
- **Mobile-first responsive design**

### ✅ Stories Feature (NEW)
- **Instagram-style vendor stories** with 24-48h expiry
- **Location-based filtering** (city-wise)
- **Dashboard integration** for vendors
- **Auto-cleanup** of expired stories
- **Touch-optimized carousel** display

### ✅ Mobile UX (Kaufda-inspired)
- **App-like card layouts** with rounded corners and shadows
- **Story carousel** with horizontal scrolling
- **Category navigation bar** with sticky positioning
- **Touch feedback** and gesture support
- **PWA capabilities** with install prompt
- **Offline functionality** via service worker

## 📁 File Structure

```
themes/listeo-child/
├── functions.php           # Core functionality & Stories backend
├── assets/
│   ├── css/custom.css     # Mobile-first styling
│   └── js/custom.js       # Frontend interactions & PWA
├── Dokan/
│   └── Dashboard/
│       └── instant-offer.php  # Vendor dashboard customization
├── manifest.json          # PWA manifest
├── sw.js                  # Service worker for offline support
└── README.md              # This file
```

## 🛠 Setup Instructions

### 1. WordPress Configuration
Ensure you have these plugins active:
- **Dokan Pro** (multi-vendor)
- **WooCommerce** (backend only)
- **Listeo Core** (theme functionality)
- **Elementor** (page builder)

### 2. Stories Feature Setup
The Stories CPT (`vendor_story`) is automatically registered. In WordPress admin:

1. Go to **Listings > Vendor Stories** to manage all stories
2. Stories auto-expire based on vendor-set duration
3. Daily cleanup removes expired stories (runs via wp-cron)

### 3. Mobile Optimization
All mobile styles are in `custom.css` with breakpoint `@media (max-width: 768px)`.

Key classes:
- `.gtg-stories-carousel` - Story display container
- `.gtg-category-bar` - Horizontal category navigation
- `.gtg-mobile-listing-card` - Enhanced product cards

### 4. PWA Setup
The platform includes PWA capabilities:
- **Manifest**: `manifest.json` (update icons path as needed)
- **Service Worker**: `sw.js` (caches static files, enables offline)
- **Install prompt**: Appears automatically on mobile devices

## 📱 Shortcodes Available

### Stories Carousel
```php
[grabtogo_stories_carousel city="Mumbai" limit="10"]
```

### Category Bar
```php
[grabtogo_category_bar sticky="true"]
```

### Vendor Registration Form
```php
[grabtogo_vendor_registration_form]
```

## 🔧 Customization

### Adding New Story Durations
In `functions.php`, modify the story dashboard template:

```php
<select id="expiry_hours" name="expiry_hours">
    <option value="24">24 Hours</option>
    <option value="48">48 Hours</option>
    <option value="72">72 Hours</option> <!-- Add this -->
</select>
```

### Changing Mobile Breakpoints
In `custom.css`, adjust the media query:

```css
@media (max-width: 768px) {
    /* Change to 992px for tablet inclusion */
}
```

### Customizing Story Expiry
The daily cleanup function runs via WordPress cron. To change frequency:

```php
// In functions.php, change 'daily' to 'hourly' or 'twicedaily'
wp_schedule_event( time(), 'hourly', 'grabtogo_daily_cleanup' );
```

## 🎯 Key Mobile UX Features

### Touch Interactions
- **Tap feedback**: Cards scale down on touch
- **Swipe support**: Horizontal carousels respond to touch
- **Gesture navigation**: Story carousel with momentum scrolling

### App-Like Elements
- **Loading states**: Animated spinners and skeleton screens
- **Status indicators**: Connection status and success/error messages
- **Pull-to-refresh**: Visual feedback for refresh actions
- **Install prompt**: Custom PWA installation banner

### Performance
- **Lazy loading**: Images load as they enter viewport
- **Service worker caching**: Offline support for static assets
- **Optimized images**: Mobile-specific image sizes
- **Debounced scrolling**: Prevents excessive scroll events

## 🔄 AJAX Endpoints

### Stories
- `gtg_get_location_stories` - Get stories for user's city
- `gtg_upload_vendor_story` - Upload new story (vendor)
- `gtg_get_vendor_stories` - Get vendor's own stories (dashboard)

### Registration
- `gtg_send_otp` - Send OTP to email
- `gtg_verify_otp` - Verify entered OTP

## 🚨 Important Notes

### Theme Safety
- **Never modify** `themes/listeo/` (parent theme)
- **All changes** go in `themes/listeo-child/`
- **Plugin modifications** should be avoided

### Mobile-First Approach
- CSS is mobile-first with progressive enhancement
- JavaScript checks for touch support
- PWA features activate automatically on mobile

### AppMySite Compatibility
The mobile styling is optimized for AppMySite wrapper:
- Native app-like feel
- Proper touch targets (44px minimum)
- iOS/Android compatible meta tags
- App manifest for installation

## 🐛 Troubleshooting

### Stories Not Loading
1. Check if user has city set: `localStorage.getItem('gtg_user_city')`
2. Verify AJAX endpoint in browser console
3. Check WordPress error logs for PHP issues

### Mobile Layout Issues
1. Clear any caching plugins
2. Check for CSS conflicts in browser dev tools
3. Verify viewport meta tag is present

### PWA Not Installing
1. Ensure HTTPS is enabled
2. Check manifest.json is accessible
3. Verify service worker registration in dev tools

## 📊 Performance Recommendations

### Production Optimizations
1. **Enable caching**: LiteSpeed Cache is configured
2. **Optimize images**: Use WebP format where possible
3. **Minify assets**: Consider asset minification
4. **CDN setup**: BunnyCDN is configured

### Mobile-Specific
1. **Reduce animations** on low-end devices
2. **Lazy load** non-critical content
3. **Optimize font loading** with `font-display: swap`
4. **Enable compression** for API responses

---

**Built for GrabToGo by Cursor.com**  
Mobile-first platform optimized for AppMySite conversion