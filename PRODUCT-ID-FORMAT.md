# POLAR PRODUCT ID FORMAT

## ✅ Correct Format

Polar Product IDs use **UUID (Universally Unique Identifier)** format:

```
xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

### Example (Real Product ID)
```
01234567-89ab-cdef-0123-456789abcdef
```

### Format Structure
```
8 chars - 4 chars - 4 chars - 4 chars - 12 chars
01234567-89ab-cdef-0123-456789abcdef
```

---

## 📋 Where to Find Product ID

### Method 1: Polar Dashboard
1. Go to https://polar.sh → **Products**
2. Click on your product
3. Look for **Product ID** in the product details
4. Copy the full UUID (e.g., `01234567-89ab-cdef-0123-456789abcdef`)

### Method 2: API Response
When you create a product via API, the response includes:
```json
{
  "id": "01234567-89ab-cdef-0123-456789abcdef",
  "name": "Premium Plan",
  ...
}
```

The `id` field is your Product ID.

---

## ⚠️ Common Mistakes

### ❌ Wrong Formats

**NOT like this:**
```
prod_xxx-xxx-xxx          ❌ Wrong prefix
product_123               ❌ Wrong format  
prod_12345                ❌ Too short
premium-monthly           ❌ Not UUID
```

**Correct:**
```
01234567-89ab-cdef-0123-456789abcdef  ✅
a1234567-89ab-cdef-0123-456789abcdef  ✅
```

---

## 🔧 Using Product IDs in Plugin

### In WooCommerce Product Settings

When configuring a product:

1. Edit product in WooCommerce
2. Find "Polar Payment Settings" meta box
3. Enter Product ID in this format:
   ```
   01234567-89ab-cdef-0123-456789abcdef
   ```
4. Save product

### In Plugin Settings

For default Product ID:

1. Go to **WooCommerce** → **Settings** → **Payments** → **Polar**
2. Enter "Default Polar Product ID":
   ```
   01234567-89ab-cdef-0123-456789abcdef
   ```
3. Save changes

---

## 📝 Examples in Documentation

All examples in plugin documentation now use correct UUID format:

### Single Product Example
```php
Default Polar Product ID: 01234567-89ab-cdef-0123-456789abcdef
```

### Multiple Products Example
```
Basic Plan:      a1234567-89ab-cdef-0123-456789abcdef
Pro Plan:        b2345678-90ab-cdef-0123-456789abcdef  
Enterprise Plan: c3456789-01ab-cdef-0123-456789abcdef
```

---

## 🔍 Validation

### PHP Validation (Example)
```php
function is_valid_polar_product_id($product_id) {
    // UUID v4 format
    $pattern = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
    return preg_match($pattern, $product_id);
}
```

### JavaScript Validation (Example)
```javascript
function isValidPolarProductId(productId) {
    const pattern = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i;
    return pattern.test(productId);
}
```

---

## 🎯 Quick Reference

| Component | Example Value |
|-----------|---------------|
| **Real Product ID** | `01234567-89ab-cdef-0123-456789abcdef` |
| **Format** | UUID v4 (36 characters including hyphens) |
| **Case** | Lowercase preferred, case-insensitive |
| **Segments** | 5 segments separated by hyphens |
| **Characters** | Hexadecimal (0-9, a-f) |

---

## ✅ Checklist

Before using a Product ID:

- [ ] ID is exactly 36 characters long (including hyphens)
- [ ] ID has 4 hyphens in correct positions
- [ ] ID contains only hexadecimal characters (0-9, a-f)
- [ ] ID is copied directly from Polar Dashboard
- [ ] No extra spaces before or after
- [ ] Format matches: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

---

## 🚨 Troubleshooting

### Error: "Invalid Product ID"

**Problem:** Product ID format is wrong

**Solution:**
1. Go to Polar Dashboard
2. Copy Product ID again
3. Paste exactly as shown (no modifications)
4. Verify format matches UUID pattern

### Error: "Product not found"

**Problem:** Product ID doesn't exist on Polar

**Solution:**
1. Check if you're using correct environment (Sandbox vs Production)
2. Verify Product exists in Polar Dashboard
3. Ensure Access Token has permission to access this Product

### Product ID Not Saving

**Problem:** Validation rejecting ID

**Solution:**
1. Check for extra spaces
2. Verify no special characters added
3. Ensure copying full UUID
4. Try typing manually following exact format

---

## 📚 Resources

**Polar Documentation:**
- https://polar.sh/docs

**UUID Format Specification:**
- RFC 4122 - A Universally Unique IDentifier (UUID) URN Namespace

**Plugin Documentation:**
- README.md
- SUBSCRIPTION-GUIDE.md
- WEBHOOK-SETUP.md

---

**Remember:** Always use the full UUID format provided by Polar! ✅
