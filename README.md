# Polar WooCommerce Payment Gateway by ALT

Accept payments via Polar.sh Checkout for your WooCommerce store.

**Author:** Nam Truong  
**Website:** https://namncn.com  
**Plugin URI:** https://namncn.com/product/alt-apwg-gateway

## Features

- Complete Polar.sh Checkout API integration
- **Subscription support** - Sell recurring products
- Dynamic pricing with ad-hoc prices
- Multiple product mapping modes
- Per-product Polar configuration
- Webhook support for payment confirmation
- Multi-currency support
- Debug logging

## Requirements

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+
- Polar.sh account with Access Token

## Installation

1. Upload plugin ZIP via **Plugins** → **Add New** → **Upload Plugin**
2. Activate the plugin
3. Go to **WooCommerce** → **Settings** → **Payments** → **Polar**

## Quick Setup

### Step 1: Create Product on Polar

1. Go to https://polar.sh → **Products** → **Create Product**
2. Set price type to "Pay what you want" or "Custom"
3. Copy the Product ID (format: `prod_xxx-xxx-xxx`)

### Step 2: Get Access Token

1. Go to Polar Dashboard → **Settings** → **API Tokens**
2. Create new token with checkout permissions
3. Copy the Access Token

### Step 3: Configure Plugin

1. **WooCommerce** → **Settings** → **Payments** → **Polar** → **Manage**
2. Enter:
   - **Polar Access Token**: Your API token
   - **Product Mapping**: Select "Single Product + Dynamic Pricing"
   - **Default Polar Product ID**: Paste Product ID from Step 1
3. Save changes

## Product Mapping Modes

### Single Product + Dynamic Pricing (Recommended)

Uses one Polar Product with dynamically calculated prices based on cart total.

**Best for:** Most WooCommerce stores

### Single Product

Uses one Polar Product with fixed catalog price.

**Best for:** Fixed-price subscriptions

### From Product Meta Field

Maps each WooCommerce product to a Polar Product using `_polar_product_id` meta field.

**Best for:** Advanced setups with multiple Polar products

## Webhook Configuration

1. Polar Dashboard → **Settings** → **Webhooks**
2. Add URL: `https://yoursite.com/wc-api/wc_polar_gateway`
3. Select event: `checkout.completed`

## Translation

The plugin is translation-ready with English as default.

**Vietnamese translation included:**
- File: `languages/apwg-vi_VN.po` (editable)
- File: `languages/apwg-vi_VN.mo` (compiled)

The plugin automatically switches to Vietnamese when WordPress language is set to Vietnamese.

### Adding New Languages

1. Copy `languages/apwg-vi_VN.po` to `apwg-{locale}.po`
2. Translate using Poedit or text editor
3. Compile to .mo file

## Troubleshooting

### Error 422: UUID parsing error

**Solution:** Enter a valid Polar Product ID in settings. Create a product on Polar.sh first.

### Access Token not configured

**Solution:** Enter your Polar Access Token in plugin settings.

### Debug Logging

Enable in settings, then check **WooCommerce** → **Status** → **Logs** → `polar-*` file

## Support

- Documentation: https://polar.sh/docs
- Polar Support: support@polar.sh

## License

GPL v2 or later

## Changelog

### 1.1.0 (2024-12-22)
- ✨ **NEW:** Subscription products support
- ✨ **NEW:** Product meta box for per-product Polar configuration
- ✨ **NEW:** Enable subscription checkbox on products
- ✨ **NEW:** Polar Product ID field per product
- ✨ **NEW:** Mixed cart support (subscription + one-time)
- ✨ **NEW:** Subscription webhook handling
- 🔧 Enhanced payment logic with subscription priority
- 🔧 Improved webhook matching
- 📚 Added SUBSCRIPTION-GUIDE.md
- 🌐 7 new Vietnamese translations

### 1.0.0 (2024-12-21)
- Initial release
- Polar Checkout API integration
- Dynamic pricing support
- Webhook handling
- Vietnamese translation

## Subscription Products

The plugin supports selling products as subscriptions. 

**Quick Setup:**
1. Create a recurring Product on Polar.sh
2. Edit your WooCommerce product
3. Enable "Polar Subscription" in the Polar Payment Settings meta box
4. Enter the Polar Product ID
5. Save

See **SUBSCRIPTION-GUIDE.md** for complete instructions.

