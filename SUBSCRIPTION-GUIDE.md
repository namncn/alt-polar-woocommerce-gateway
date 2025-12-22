# SUBSCRIPTION PRODUCTS GUIDE

## 🔄 Overview

The plugin now supports **both one-time payments and subscription products**. You can configure individual WooCommerce products to be sold as subscriptions via Polar.

---

## 🎯 How It Works

### One-Time Products (Default)
- Product sold once
- Customer pays one time
- Uses dynamic pricing or default Product ID

### Subscription Products (New Feature)
- Product sold as recurring subscription
- Customer pays monthly/yearly (based on Polar Product config)
- Uses specific Polar Product ID per product
- **Overrides default payment gateway settings**

---

## ⚙️ Setup Subscription Products

### Step 1: Create Subscription Product on Polar.sh

1. Go to https://polar.sh → **Products** → **Create Product**
2. Configure:
   - **Name**: e.g., "Premium Monthly Plan"
   - **Billing**: **Recurring** (monthly, yearly, etc.)
   - **Price**: Set your subscription price
3. **Copy Product ID** (format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)

### Step 2: Configure WooCommerce Product

1. Go to **Products** → Edit your WooCommerce product
2. Find **"Polar Payment Settings"** meta box on the right sidebar
3. Configure:
   - ✅ **Check** "Enable Polar Subscription"
   - **Enter** the Polar Product ID you copied
4. Click **Update** to save

### Step 3: Test Purchase

1. Add the product to cart
2. Go to checkout
3. Select Polar payment method
4. Complete payment
5. ✅ Subscription will be created on Polar
6. ✅ Customer will be charged automatically based on billing cycle

---

## 📋 Product Settings Meta Box

### Location
Right sidebar on Product Edit page

### Fields

#### 1. Enable Polar Subscription
- **Type:** Checkbox
- **Purpose:** Mark this product as a subscription
- **Default:** Unchecked (one-time payment)

#### 2. Polar Product ID
- **Type:** Text field
- **Purpose:** Link to Polar Product ID
- **Format:** `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- **Required when:**
  - Subscription is enabled
  - OR using "From Product Meta Field" mapping mode

---

## 🔍 Payment Logic Priority

The plugin checks products in this order:

### Priority 1: Subscription Products (Highest)
```
IF any product has "_polar_is_subscription" = yes
  → Use that product's Polar Product ID
  → Ignore gateway default settings
```

### Priority 2: Meta Field Mapping
```
IF "Product Mapping" = "From Product Meta Field"
  → Use each product's "_polar_product_id"
```

### Priority 3: Default Settings
```
IF "Product Mapping" = "Single Product + Dynamic Pricing"
  → Use default Product ID with ad-hoc prices
```

**Example:**
```
Cart contains:
- Product A (subscription enabled, Polar ID: 01234567-89ab-cdef-0123-456789abcdef)
- Product B (regular product)

Result: Checkout uses 01234567-89ab-cdef-0123-456789abcdef (subscription takes priority)
```

---

## 🛒 Cart Scenarios

### Scenario 1: Pure Subscription Cart
```
Cart: 1x Premium Plan (subscription)
Result: Creates subscription checkout with recurring billing
```

### Scenario 2: Pure One-Time Cart
```
Cart: 1x eBook (one-time)
Result: Creates one-time payment checkout
```

### Scenario 3: Mixed Cart (Subscription + One-Time)
```
Cart: 
- 1x Premium Plan (subscription)
- 1x Setup Fee (one-time)

Result: Subscription checkout takes priority
Note: The one-time product will be processed as part of subscription
```

### Scenario 4: Multiple Subscriptions
```
Cart:
- 1x Basic Plan (subscription, a1234567-89ab-cdef-0123-456789abcdef)
- 1x Pro Plan (subscription, b2345678-90ab-cdef-0123-456789abcdef)

Result: Multiple products checkout with both Product IDs
```

---

## ⚠️ Important Notes

### 1. Subscription Products Override Settings

When a cart contains subscription products:
- ❌ Default Product ID is **ignored**
- ❌ Dynamic pricing is **ignored**
- ✅ Subscription Product IDs are **used directly**

### 2. Polar Product Must Be Recurring

The Polar Product ID you enter MUST be configured as:
- **Billing Type:** Recurring
- **Interval:** Monthly, Yearly, etc.
- **Price:** Fixed or tiered

### 3. One Product ID Per WooCommerce Product

Each WooCommerce product can only have ONE Polar Product ID.

### 4. Subscription Management

After purchase:
- Customer can manage subscription in Polar Customer Portal
- Subscription auto-renews based on Polar settings
- Cancellation handled through Polar

---

## 🔧 Configuration Examples

### Example 1: Simple Monthly Subscription

**WooCommerce Product:**
- Name: "Premium Membership"
- Regular price: $29/month (display only)

**Polar Settings:**
- ✅ Enable Polar Subscription
- Polar Product ID: `e5678901-23ab-cdef-0123-456789abcdef`

**Polar Product (on Polar.sh):**
- Billing: Recurring - Monthly
- Price: $29.00

### Example 2: Multiple Subscription Tiers

**Products Setup:**

| WooCommerce | Polar Product ID | Polar Billing |
|-------------|------------------|---------------|
| Basic Plan | a1234567-89ab-cdef-0123-456789abcdef | $10/month |
| Pro Plan | b2345678-90ab-cdef-0123-456789abcdef | $25/month |
| Enterprise | c3456789-01ab-cdef-0123-456789abcdef | $100/month |

Each product configured with:
- ✅ Enable Polar Subscription
- Corresponding Polar Product ID

### Example 3: Mixed Product Catalog

| WooCommerce Product | Type | Polar Config |
|---------------------|------|--------------|
| Premium Plan | Subscription | ✅ Enabled, 01234567-89ab-cdef-0123-456789abcdef |
| eBook | One-time | ❌ Disabled |
| Course | One-time | ❌ Disabled |
| VIP Access | Subscription | ✅ Enabled, d4567890-12ab-cdef-0123-456789abcdef |

---

## 🐛 Troubleshooting

### Error: "Product has no Polar Product ID"

**Problem:** Subscription enabled but Product ID not set

**Solution:**
1. Edit the product
2. Enter Polar Product ID in the meta box
3. Save product

### Subscription Not Creating

**Problem:** Webhook not configured

**Solution:**
1. Configure webhook on Polar.sh
2. Enable event: `subscription.created`
3. See WEBHOOK-SETUP.md for details

### Wrong Product Charged

**Problem:** Using wrong Polar Product ID

**Solution:**
1. Verify Product ID on Polar.sh matches WooCommerce
2. Check Product ID format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
3. Enable debug logging to verify

### Customer Charged Wrong Amount

**Problem:** Polar Product has different price than WooCommerce

**Solution:**
- Polar subscription price takes precedence
- Update WooCommerce price to match (display only)
- Or update Polar Product price

---

## 📊 Webhooks for Subscriptions

### Required Events

Enable these webhook events on Polar.sh:

1. `subscription.created` - When subscription starts
2. `subscription.updated` - When subscription changes
3. `subscription.canceled` - When subscription ends
4. `order.created` - For renewal payments

### Webhook URL
```
https://yoursite.com/wc-api/wc_polar_gateway
```

---

## ✅ Quick Checklist

Before selling subscriptions:

- [ ] Created Polar Product with **Recurring** billing
- [ ] Copied Polar Product ID
- [ ] Enabled "Polar Subscription" on WooCommerce product
- [ ] Entered Polar Product ID in meta box
- [ ] Saved product
- [ ] Configured webhook with `subscription.created` event
- [ ] Tested checkout with subscription product
- [ ] Verified subscription created on Polar
- [ ] Customer can access Polar Customer Portal

---

## 🎯 Summary

**Before This Feature:**
- ❌ Only one-time payments supported
- ❌ No subscription functionality
- ❌ All products use same Polar Product ID

**After This Feature:**
- ✅ Subscription products supported
- ✅ Per-product Polar Product ID configuration
- ✅ Automatic subscription creation
- ✅ Mixed cart handling (one-time + subscription)
- ✅ Multiple subscriptions in one cart

**Your store can now sell subscriptions!** 🎉

---

## 📞 Need Help?

**Documentation:**
- README.md - Main plugin documentation
- WEBHOOK-SETUP.md - Webhook configuration guide

**Support:**
- Author: Nam Truong
- Website: https://namncn.com
- Polar Docs: https://polar.sh/docs

---

**Enjoy selling subscriptions with Polar!** 🚀
