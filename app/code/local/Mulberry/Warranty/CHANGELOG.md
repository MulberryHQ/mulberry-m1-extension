Mulberry_Warranty changelog
========================

1.3.0:
- Added validation for the "add to cart" action for the warranty product
- Reworked post-purchase hook functionality to exclude order items with the warranty products associated from the payload
- Improved messaging when the warranty item is added to the shopping cart
- Added error message whenever warranty product failed to add to the shopping cart
- Adjusted token field namings for the system config

1.2.5:
- Added "warranty_offer_id" to the corresponding payloads

1.2.4:
- Use order increment_id instead of order_identifier in send cart hook

1.2.3:
- Updated Mulberry Warranty API to the latest state
- Added queue functionality for the API webhooks aka send cart/send order
- Updated fronted library to the latest version
- Reworked add-to-cart logic to use 2 different parameters for the warranty hash & sku instead of 1 as it conflicts if the SKU is too long
- Added "12 months" product placeholder to the setup script

1.1.0:
- Added order UUID generation functionality
- Changed warranty product mapping (now it uses 2-5 years warranty product placeholders accordingly)
- Separated event observers for API calls and product add-to-cart actions
- Fixed issue with the "price" attribute being unset for default product types
- Send cart hook is now sent for admin created orders as well

1.0.0:
- Initial Magento functionality
