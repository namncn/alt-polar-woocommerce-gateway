# WEBHOOK CONFIGURATION GUIDE

## Why Orders Don't Complete Automatically

The issue is that the webhook needs to be properly configured for Polar to notify your WooCommerce store when payment succeeds.

## ✅ Complete Webhook Setup

### Step 1: Configure Webhook on Polar.sh

1. Go to https://polar.sh → Your Organization → **Settings** → **Webhooks**
2. Click **Add Endpoint**
3. Enter Webhook URL:
   ```
   https://yoursite.com/wc-api/wc_polar_gateway
   ```
   Replace `yoursite.com` with your actual domain

4. **Select Events** (IMPORTANT):
   - ✅ `order.created` ← Main event for one-time payments
   - ✅ `checkout.updated` ← Backup event for checkout status
   - ✅ `subscription.created` ← For subscription products

5. **Webhook Secret** (Optional but recommended):
   - Generate or enter a secret
   - Save it for future webhook signature verification

6. Click **Save**

### Step 2: Test Webhook

#### Method 1: Use Polar Sandbox
1. Make a test purchase in Polar Sandbox
2. Check if order status changes to "Processing" in WooCommerce

#### Method 2: Use Webhook Test Tool
1. In Polar Webhooks settings, find your endpoint
2. Click **Test** or **Send Test Event**
3. Select event type: `order.created`
4. Check WooCommerce logs

### Step 3: Verify in WooCommerce

1. Enable **Debug Log** in plugin settings
2. Make a test purchase
3. Check logs at **WooCommerce** → **Status** → **Logs** → `polar-*`

Look for:
```
Webhook received: {"type":"order.created",...}
Saved checkout ID: chkt_xxx
Order #123 marked as completed via webhook
```

## 🔍 How It Works Now (Fixed)

### When Customer Checks Out:

1. Plugin creates Polar checkout session
2. **Saves `checkout_id`** to order meta (`_polar_checkout_id`)
3. Redirects customer to Polar payment page

### When Customer Completes Payment:

4. Polar sends webhook to `https://yoursite.com/wc-api/wc_polar_gateway`
5. Plugin receives webhook with event type: `order.created` or `checkout.updated`
6. Plugin finds WooCommerce order using `checkout_id`
7. Plugin marks order as completed: `$order->payment_complete()`
8. Customer receives confirmation email

## 🚨 Common Issues & Solutions

### Issue 1: Webhook Returns 404

**Problem:** Webhook URL is incorrect

**Solution:**
- URL format: `https://yoursite.com/wc-api/wc_polar_gateway`
- NO trailing slash
- Make sure WordPress permalinks are enabled (not default `?p=123`)

### Issue 2: Order Still Pending After Payment

**Problem:** Webhook not configured or wrong events selected

**Solution:**
1. Check Polar webhook settings
2. Make sure `order.created` event is enabled
3. Check webhook delivery logs in Polar dashboard

### Issue 3: Webhook Received But Order Not Completing

**Problem:** Checkout ID not matching

**Solution:**
1. Enable debug logs
2. Check if `checkout_id` is saved: `_polar_checkout_id` meta field
3. Verify webhook payload has correct `data.id`

### Issue 4: Multiple Orders Completing

**Problem:** Duplicate webhooks

**Solution:**
- Plugin now checks if order is already `processing` or `completed`
- Won't complete same order twice

## 📊 Webhook Events Explained

### `order.created`
**When:** Customer completes a one-time payment
**Action:** Plugin marks order as complete
**Payload:** Contains `checkout_id` and order details

### `checkout.updated`
**When:** Checkout status changes (e.g., from `open` to `succeeded`)
**Action:** Plugin checks status and completes order if `succeeded`
**Payload:** Contains `checkout_id` and `status`

### `subscription.created`
**When:** Customer subscribes (recurring payment)
**Action:** Plugin saves subscription ID and completes order
**Payload:** Contains `checkout_id` and `subscription_id`

## 🔒 Webhook Security (Recommended)

### Enable Signature Verification

Add to `wp-config.php`:
```php
define('POLAR_WEBHOOK_SECRET', 'your_webhook_secret_here');
```

Then plugin will verify webhook signatures (feature to be added in future version).

## ✅ Verification Checklist

- [ ] Webhook URL added to Polar.sh
- [ ] Events selected: `order.created`, `checkout.updated`, `subscription.created`
- [ ] Webhook endpoint is accessible (not behind auth)
- [ ] SSL certificate is valid (HTTPS required)
- [ ] WordPress permalinks enabled
- [ ] Debug logging enabled for testing
- [ ] Test purchase completed successfully
- [ ] Order status changed to Processing/Completed
- [ ] Customer received email confirmation

## 📝 Webhook Payload Examples

### order.created
```json
{
  "type": "order.created",
  "data": {
    "id": "chkt_xxx-xxx-xxx",
    "status": "succeeded",
    "amount": 500,
    "customer_email": "customer@example.com"
  }
}
```

### checkout.updated
```json
{
  "type": "checkout.updated",
  "data": {
    "id": "chkt_xxx-xxx-xxx",
    "status": "succeeded"
  }
}
```

## 🛠️ Manual Order Completion (Fallback)

If webhook fails, you can manually complete orders:

1. Go to **WooCommerce** → **Orders**
2. Find the pending order
3. Change status to **Processing** or **Completed**
4. Customer will receive confirmation email

## 📞 Still Not Working?

1. **Check Polar webhook delivery logs:**
   - Polar Dashboard → Settings → Webhooks → Click endpoint
   - View delivery attempts and responses

2. **Check WooCommerce logs:**
   - WooCommerce → Status → Logs → `polar-*`
   - Look for webhook received messages

3. **Test webhook manually:**
   ```bash
   curl -X POST https://yoursite.com/wc-api/wc_polar_gateway \
     -H "Content-Type: application/json" \
     -d '{"type":"order.created","data":{"id":"test_123"}}'
   ```
   Should return HTTP 200

4. **Contact support:**
   - Include webhook delivery logs from Polar
   - Include WooCommerce debug logs
   - Provide order ID that didn't complete

## 🎯 Summary

**Before Fix:**
- ❌ Used `external_customer_id` to find orders (unreliable)
- ❌ Only listened to `checkout.completed` event (wrong event)
- ❌ Didn't save `checkout_id` for matching
- ❌ Multiple customers = wrong order completed

**After Fix:**
- ✅ Saves `checkout_id` when creating session
- ✅ Listens to correct events: `order.created`, `checkout.updated`
- ✅ Finds exact order using `checkout_id`
- ✅ Prevents duplicate completions
- ✅ Supports both one-time and subscriptions

**Your orders will now complete automatically!** 🎉
