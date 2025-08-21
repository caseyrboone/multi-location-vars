# Multi Location Vars (WordPress Plugin)

A lightweight WordPress plugin that lets you manage **location‑specific variables** (address, phone, social links, hours, etc.) as a custom post type and output them anywhere via a single shortcode. The active location can be resolved from either a **cookie** or the **first URL path segment**, with a configurable default fallback.

---

## Key Features

- Custom Post Type: `multi_location_vars` with common business fields (address, phone, socials, hours, etc.).
- Shortcode: `[multi_location_vars]` to print a specific field for the current location.
- Location resolution:
  - **Cookie**: `STYXKEY_qv_location` (common on WP Engine) if enabled.
  - **URL**: first path segment (e.g., `/atlanta/...`) if cookie mode is off.
  - **Default fallback**: use a sitewide default location if neither cookie nor URL matches.
- Admin settings page to choose cookie vs. URL mode and set the default location.
- Works in posts, pages, widgets, and templates (`do_shortcode`).

---

## Requirements

- WordPress 5.0+
- PHP 7.4+ (8.x compatible in typical setups)
- (Bundled) ACF v4 library for legacy fields in `lib/acf/`

> Note: ACF is bundled for historical reasons. For modern projects, you can replace it with your own ACF installation if desired.

---

## Installation

1. Copy the plugin folder `multi-location-vars/` into `wp-content/plugins/`.
2. Activate **Multi Location Vars** from **Plugins** in wp‑admin.
3. In **Settings → Multi Location Vars Config**:
   - Choose **Cookie** or **URL** mode for location resolution.
   - Set a **Default Location** (used as a fallback).

---

## Creating Locations

1. Go to **Multi Location Vars** in the admin menu.
2. **Add New** and fill in the fields (see field keys below).
3. The **Location** field should match how you identify the location:
   - **URL mode**: set it to the first path segment (e.g., `atlanta` for `https://example.com/atlanta/`).
   - **Cookie mode**: set a cookie named `STYXKEY_qv_location` with the same value (e.g., `atlanta`).

---

## Usage

### Shortcode (in content)
```txt
[multi_location_vars id="phone_number"]
[multi_location_vars id="address_1"]
[multi_location_vars id="store_hours"]
```

### In PHP templates
```php
echo do_shortcode('[multi_location_vars id="phone_number"]');
```

### Setting the cookie (example)
```js
// Set cookie for 7 days (adjust as needed)
document.cookie = "STYXKEY_qv_location=atlanta; path=/; max-age=" + 60*60*24*7;
```

> The plugin will resolve the current location by cookie (if enabled), else by URL segment, else by the configured default location.

---

## Field Keys (for `id` attribute)

Use these keys with the shortcode or in code. Examples:

- **Location** → `location`
- **Phone Number** → `phone_number`
- **Formal Business Name** → `business_name`
- **Address 1** → `address_1`
- **Address 2** → `address_2`
- **City** → `city_1`
- **State** → `state_1`
- **Zip code** → `zip_1`
- **Facebook URL** → `facebook`
- **Twitter URL** → `twitter`
- **LinkedIn URL** → `linkedin`
- **Youtube URL** → `youtube`
- **Pinterest URL** → `pintrest`
- **Instagram URL** → `instagram`
- **Yelp URL** → `yelp`
- **Location Site Name & custom yoast variable value** → `location_custom`
- **Email** → `email`
- **FPC Location Name** → `fpc_location_name`
- **FPC Group** → `fpc_group`
- **FPC Auto Responder ID** → `fpc_responder`
- **FPC Account API Key** → `fpc_api_key`
- **Calendar Type** → `calendar_type`
- **Fit Pro Tracker URL** → `fit_pro_tracker_url`
- **Calendar API Key** → `cal_api_key`
- **Territory** → `territory`
- **Google Map URL (only if dynamic map is not working)** → `additional_field`
- **Latitude** → `additional_field_2`
- **Longitude** → `additional_field_3`
- **Service Area City 1** → `additional_field_4`
- **Store Hours** → `store_hours`
- **Service Area City 2** → `custom_text_2`
- **Service Area City 3** → `custom_text_3`
- **Wiki City link** → `custom_text_4`
- **Additional text field 5** → `custom_text_5`
- **Current Promo** → `current_promo`
- **Fit Body Forever AR ID** → `forever_ar_id`
- **Multi Location FB** → `multi_location_fb`
- **Fbbc FB** → `fbbc_fb`
- **Fbbc FB1** → `fbbc_fb1`
- **Grand Open** → `grandopen`

> You can add more fields via ACF as needed. Keep the **field name** (not the label) consistent when using the shortcode `id`.

---

## Admin & Settings

- **Settings page** (`Settings → Multi Location Vars Config`):
  - Toggle **Cookie vs URL** mode.
  - Set the **Default Location** (post ID used when nothing else matches).
- **List Table Enhancements**: Custom columns for quick scanning of locations.

---

## Developer Notes

- Helper used internally: `get_post_id_by_meta_key_and_value( $key, $value )` for resolving a location by its meta (defaults to `location` key).
- The shortcode ultimately calls ACF’s `get_field( $field_key, $location_post_id )`.
- If you don’t use WP Engine, you may still set the same cookie name (`STYXKEY_qv_location`) or adapt the code to a different cookie name.

---

## Folder Structure

```
multi-location-vars/
├─ multi-location-vars.php          # Main plugin bootstrap
├─ lib/
│  ├─ cpt/multi_location_vars.php   # CPT + ACF field registrations
│  └─ acf/                          # Bundled ACF v4 (legacy)
└─ README.md                        # This file
```

---

## Changelog

- **1.2** – Stable internal release; settings page; shortcode; cookie/URL resolver; default fallback.

---

## Contributing

1. Fork the repo and create a feature branch.
2. Keep changes focused and documented.
3. Open a PR with a clear description and testing notes.
