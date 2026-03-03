# Changelog

All notable changes to this project will be documented in this file.

## [1.2.0] - 2026-03-03

### 🔧 Fixed
- Applied WordPress Coding Standards (WPCS) across the codebase
- Fixed docblocks, missing translators comments, Yoda conditions, and strict comparisons

## [1.1.1] - 2024-12-22

### 🔧 Fixed
- Corrected Product ID format in all documentation and examples
- Changed from incorrect `prod_xxx-xxx-xxx` format to correct UUID format
- Updated placeholder in product meta box to use example UUID
- All Product ID examples now use proper UUID v4 format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

### 📚 Documentation
- Added PRODUCT-ID-FORMAT.md - Complete guide about Polar Product ID format
- Updated all examples to use correct UUID format
- Clarified that Polar uses standard UUID v4 for Product IDs

### 🔒 Security
- Removed any real Product IDs from documentation
- All examples now use safe, fictional UUIDs

## [1.1.0] - 2024-12-22

### 🎉 Added
- **Subscription Products Support** - Products can now be sold as recurring subscriptions
- Product meta box "Polar Payment Settings" on product edit page
- Checkbox to enable Polar Subscription per product
- Field to enter Polar Product ID per product (UUID format)
- Smart payment logic with subscription priority
- Support for mixed carts (subscription + one-time products)
- Multiple subscriptions in single cart
- Webhook handling for `subscription.created` event
- Subscription ID saved to order meta
- 7 new translation strings (Vietnamese 100% translated)
- Complete SUBSCRIPTION-GUIDE.md documentation

### 🔧 Changed
- `prepare_checkout_data()` now checks for subscription products first
- Webhook handler now processes `subscription.created` events
- Enhanced error messages for missing Polar Product IDs
- Updated all documentation with correct Product ID format (UUID)

### 📚 Documentation
- Added SUBSCRIPTION-GUIDE.md with complete setup instructions
- Updated README.md with subscription feature information
- Updated Vietnamese translations (42 total strings)
- Corrected Product ID format examples (UUID: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)

### 🐛 Fixed
- Improved webhook matching using checkout_id
- Better validation for subscription products
- Duplicate order completion prevention
- Product ID format examples now use correct UUID format

## [1.0.0] - 2024-12-21

### 🎉 Initial Release
- Complete Polar.sh Checkout API integration
- Dynamic pricing with ad-hoc prices
- 3 product mapping modes:
  - Single Product + Dynamic Pricing
  - Single Product (catalog price)
  - From Product Meta Field
- Webhook support for payment confirmation
- External Customer ID support
- Multi-currency support
- Debug logging
- Full English and Vietnamese translations
- Comprehensive documentation

### 📦 Features
- One-time payment support
- Automatic order completion via webhooks
- Configurable payment gateway settings
- Test mode support

---

**Plugin URI:** https://namncn.com/product/alt-polar-woocommerce-gateway  
**Author:** Nam Truong  
**Website:** https://namncn.com
