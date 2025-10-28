# Changelog
## Version 1.2.64 on 28 October 2025
- Fixed an error that occurred during reindexing when the flat catalog was enabled.

## Version 1.2.63 on 21 October 2025
- Added age verification feature.

## Version 1.2.62 on 6 October 2025
- Added a new feature to automatically update the order payment method to a specific method when placing an order with the main payment method.

## Version 1.2.61 on 1 August 2025
- Added csp_whitelist.xml to configure Content Security Policy (CSP) whitelisting

## Version 1.2.60 on 23 June 2025
- Rebranded Billwerk+ to Frisbii
- Magento Coding Standard

## Version 1.2.59 on 31 March 2025
- Fixed an issue where the Swish payment method fell back to the standard payment during checkout.

## Version 1.2.58 on 7 March 2025
- Updated order cancellation logic: Instead of deleting the session on Billwerk, the module now correctly performs invoice cancellation.

## Version 1.2.57 on 28 February 2025
- Added configuration option "Default value not checked?" to control whether the "Save credit card" checkbox is checked by default.

## Version 1.2.56 on 23 January 2025
- Added logic to handle refunds when canceling orders paid via auto-capture payment methods.
- Fixed the issue where the customer's address line 2 was incorrect in Billwerk when creating a new customer.

## Version 1.2.55 on 4 December 2024
- Fixed 'Update Webhook' button to support multiple store/domain setups with different API keys.

## Version 1.2.54 on 14 November 2024
- Added auto-cancel feature for unpaid orders.
- Added a new feature to prefill the phone number in the payment window for mobile payment types.
- Updated note for the webhook button.

## Version 1.2.53 on 15 October 2024
- Fixed the error when saving the module configuration in the store or website scopes.
- Fixed incorrect currency in order comments for multiple currency setups.
- Fixed incorrect amount shown on embedded/overlay display for multiple currency setups.

## Version 1.2.52 on 1 October 2024
- Fixed the issue with retrieving the order currency code for Vipps MobilePay.
- Changed iDEAL payment method to display only in the "Window" display type.

## Version 1.2.51 on 10 September 2024
- New payment method added: "Vipps MobilePay"
- Warning messages added for "Mobilepay" to encourage switch to "Vipps Mobilepay"
- Removed payment method "Resurs bank"
- Fix: Refreshing the success page now redirects the user to the basket and restores the previous cart

## Version 1.2.50 on 31 July 2024
- Fixed the error for guzzlehttp/guzzle 7.8.2+

## Version 1.2.49 on 26 July 2024
- Fixed the XML error

## Version 1.2.48 on 24 July 2024
- Added "Upload custom icon" function. Admins can override the default icons.
- Added the admin notice label to each payment method.

## Version 1.2.47 on 26 June 2024
- Fixed JavaScript validation for Google Pay and Apple Pay to be compatible with onestepcheckout.
- Changed the module name to Billwerk+ Pay.

## Version 1.2.46 on 20 May 2024
- Removed PHP payment validation
- Added compatibility with Billwerk+ Subscription for Magento 2 module

## Version 1.2.45 on 10 May 2024
- Fixed surcharge fee calculation on admin order invoice creating

## Version 1.2.44 on 29 April 2024
- Added new payment methods "EPS", "Estonian Banks", "Latvian Banks", "Lithuanian Banks", "MB Way", "Multibanco", "mBank", "Payconiq", "Paysafecard", "Paysera", "PostFinance", "Satisfy", "Trustly", "WeChat Pay", "Bank Transfer", "Cash" and "Other".

## Version 1.2.43 on 23 April 2024
- Added Santander payment method.
- Fixed the warning for the deprecated function: "Operation is deprecated; use documented list operation".

## Version 1.2.42 on 6 December 2023
- Resolved JS library loading issue on certain themes by switching to requireJS.

## Version 1.2.41 on 17 November 2023
- Changed "BLIK One Click" payment to the "auto_capture" payment flow.

## Version 1.2.40 on 14 November 2023
- Changed "Reepay" labels to "Billwerk+"
- Prevent webhook retrying when cancel order from Magento

## Version 1.2.39 on 10 November 2023
- Changed "BLIK" payment to "BLIK One Click" payment

## Version 1.2.38 on 8 November 2023
- Changed "Przelewy24 (P24)" payment to the "auto_capture" payment flow.

## Version 1.2.37 on 30 August 2023
- Fixed cancel payment issue when the payment status on Reepay is "failed"
- Hidden the "Send payment link" button on the order view backend when the order stage is "canceled"

## Version 1.2.36 on 19 June 2023
- Added Swish, iDEAL, Verkkopankki, SEPA Direct Debit and Bancontact to payment options of main method.

## Version 1.2.35 on 9 June 2023
- Fixed the logo to be compatible with Magento 2.4.6 and higher.

## Version 1.2.34 on 30 May 2023
- Handle PHP notice in Google Pay and Apple Pay
- Updated the payment link local based on the store associated with the order.

## Version 1.2.33 on 17 February 2023
- Changed "AnyDay" to "Anyday"

## Version 1.2.32 on 27 January 2023
- Supported Magento 2.2.x and below

## Version 1.2.31 on 24 January 2023
- Checked compatible with Magento 2.4.5

## Version 1.2.30 on 13 January 2023
- Changed "iDEAL" payment to the "auto_capture" payment flow.

## Version 1.2.29 on 11 January 2023
- Added new payment method "Bancontact".
- Remove "SEPA Direct Debit" and "Verkkopankki" from payment options of main method.

## Version 1.2.28 on 4 January 2023
- Added new payment method "AnyDay".

## Version 1.2.27 on 20 December 2022
- Added new payment methods "Klarna Direct Bank Transfer" and "Klarna Direct Debit".

## Version 1.2.26 on 3 October 2022
- Fixed PHP error when get error response from Reepay API.
- Added automatic create invoice option when full amount is settled in Reepay.
- Changed "SEPA Direct Debit" and "Verkkopankki" payments to the "auto_capture" payment flow.
- Automatic create invoice for the "auto_capture" payments ("Swish", "SEPA Direct Debit" and "Verkkopankki").

## Version 1.2.25 on 15 September 2022
- Fixed order status issue for Swish payment.

## Version 1.2.24 on 20 July 2022
- Add all payment source data to payment additional information.

## Version 1.2.23 on 11 July 2022
- Add new payment methods "iDEAL", "BLIK", "Przelewy24 (P24)", "Verkkopankki", "giropay" and "SEPA Direct Debit".

## Version 1.2.22 on 20 June 2022
- Fixed duplicate credit card saving issue for CIT.

## Version 1.2.21 on 13 June 2022
- Fixed canceleration order for pending order.
- Fixed restore the last order when back from window payment when use swissup firecheckout.
- Fixed typo in PaymentException path

## Version 1.2.20 on 26 April 2022
- Not allowed order cancelation via payment window for the order that is paid.
- Compatible with Magento 2.4.4

## Version 1.2.19 on 23 March 2022
- Magento coding standard for Magento Marketplace.
- Fixed error when checkout with multiple shipping address action.

## Version 1.2.18 on 9 February 2022
- Change capture amount condition in capture function 

## Version 1.2.17 on 9 February 2022
- Fixed parse int issue.

## Version 1.2.16 on 16 December 2021
- Fixed Toolbar Plugin error in order view backend.

## Version 1.2.15 on 14 December 2021
- Add "Send payment link" button in order view backend.

## Version 1.2.14 on 1 November 2021
- Fixed wrong prices and total on multistore setup

## Version 1.2.13 on 27 October 2021
- Allowed blank value for payment icons configuration.
- Fixed payment icons issue when the payment icons setting is empty.
- Updated the "reepay_surcharge_fee" extension attribute.

## Version 1.2.12 on 25 October 2021
- Added module version in the backend.

## Version 1.2.11 on 14 October 2021
- Fixed restore cart issue (restore when empty cart only). The issue related to V 1.2.8

## Version 1.2.10 on 7 October 2021
- Delete the charge when order is canceled from Magento (only not paid order). So, user cannot pay after cancel order.

## Version 1.2.9 on 6 October 2021
- Fix deprecated load method in SurchargeFee.php by @puntable

## Version 1.2.8 on 8 September 2021
- Restore the last order when back from window payment.

## Version 1.2.7 on 11 August 2021
- Fixed VAT issue on order lines of the settle request

## Version 1.2.6 on 10 August 2021
- Implement order lines for the settle request.
- Remove "key" argument from settle and refund request. 

## Version 1.2.5 on 29 June 2021
- Fixed create online refund issue

## Version 1.2.4 on 25 June 2021
- Fixed invoice is not created automatically for some orders when the auto capture function is enabled.
- Fixed error when throwing a LocalizedException

## Version 1.2.3 on 14 June 2021
- Create invoice in Magento automatically when auto capture is enabled.
- Update default configuration for "Change order status to cancelled if the customer is redirected to the cancel_url".

## Version 1.2.2 on 9 June 2021
- Fixed refund online issue when the auto capture function is enabled.

## Version 1.2.1 on 7 June 2021
- Add "Google Pay" payment method.
- Allowed "Apple Pay" payment only Safari browser.

## Version 1.2.0 on 5 May 2021
- Implement save credit cards function.
- Show payment instructions on checkout page.
- Mobilepay icon label fixing.

## Version 1.1.11 on 15 February 2021
- Add backend configuration to control order cancellation.

## Version 1.1.10 on 18 January 2021
- Implement customer handle solution.
- Implement webhook setting button in Magento backend.
- Fix order status for auto capture mode.

## Version 1.1.9 on 14 December 2020
- Change admin label and set default "send_order_email_when_success"

## Version 1.1.8 on 4 December 2020
- Fixed double order comments history

## Version 1.1.7 on 3 December 2020
- Add delay to webhooks to avoid immediately call back

## Version 1.1.6 on 27 November 2020
- block refund request for offline refund

## Version 1.1.5 on 27 October 2020
- Fix shipping address error for virtual product.

## Version 1.1.4 on 22 October 2020
- Force Vipps, Resurs Bank and Apple Pay to be opened in "Window" display type.
- Disable cache for Reepay block

## Version 1.1.3 on 16 October 2020
- Fix 'ordertext' blank issue.

## Version 1.1.2 on 28 September 2020
- add "Klarna Slice It" and "Vipps" payment options.

## Version 1.1.1 on 21 September 2020
- Not delete Reepay session when payment success.

## Version 1.1.0 on 10 September 2020
- Implement surcharge fee
- Fixed invoice issue for Swish payment.

## Version 1.0.14 on 10 August 2020
- Add "Send order lines" option.
- Prevent capture amount more than authorized amount.

## Version 1.0.13 on 23 June 2020
- Add "Swish Bank", "ApplePay", "Paypal", "Klarna Pay Now", "Klarna Pay Later", "Resurs" and "Forbrugsforeningen" payment methods.

## Version 1.0.12 on 28 May 2020
- Fixed invoice issue and credit memo issue for multi currencies. 

## Version 1.0.11 on 27 February 2020
- Add "Resurs Bank" payment option.
- Fix checkout session for thank you page.

## Version 1.0.10 on 26 February 2020
- add payment method validation on cancel order observer.
- Fix checkout session for thank you page.

## Version 1.0.9 on 21 February 2020
- Add error handle for capture and refund from Magento
- Change logic to calculate "Other" line on order lines.
- Fixed integer parse issue

## Version 1.0.8 on 12 February 2020
- Fixed payment_link email template issue for other languages.

## Version 1.0.7 on 9 January 2020
- Save payment additional data when authorize, settled and refund 
- Fixed PHP Notice: Undefined index

## Version 1.0.6 on 18 September 2019
- Add new payment options (Apple Pay, Paypal)

## Version 1.0.5 on 3 July 2019
 - Add billing address and terms and conditions on payment methods in the checkout page.
	
## Version 1.0.4 on 21 June 2019
 - Implement payment link
 - Implement vat in order lines
 - Implement other line in the order lines
 - Implement partial capture/refund
 - Restore quote when cancel payment from payment window
 - Implement "invoice_authorized" webhook
 - Add Klarna and FFK payment options
 - Override send order confirmation email.

## Version 1.0.3 on 21 March 2019
 - Fixed text translation issue

## Version 1.0.2 on 5 March 2019
 - remove order_status_before_payment configuration (only allow pending status)

## Version 1.0.1 on 28 February 2019
 - fixed multi API keys issue for multi stores

## Version 1.0.0 on 22 February 2019
 - First release
