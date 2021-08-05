Mulberry_Warranty changelog
========================

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
