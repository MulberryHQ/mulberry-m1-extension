# Mulberry Warranty Extension

The Mulberry Warranty extension allows the addition of an additional warranty product to an original Magento product.

## Installation

1. Download the module from [https://github.com/MulberryHQ/mulberry-m1-extension/](https://github.com/MulberryHQ/mulberry-m1-extension/)
2. Extract the module contents into the Magento root folder
3. Configure the module settings in the admin panel under ​System → Configuration

## Configuration

### Module Configuration

A merchant (admin user) can configure the following fields in Magento admin, which are required to initialize a Mulberry warranty iframe on the Product Details Page:

- **Enable Mulberry Warranty Block**
    - Enables/disables the Mulberry module.
- **Mulberry API URL**
    - Sets base URL used for API requests (e.g. `https://www.getmulberry.com`).
- **Mulberry Partner Base URL**
    - Sets the Mulberry Partner URL. This URL is used to perform backend API requests as well as to initialize the iframe on the Product Details Page (PDP). e.g `partner.getmulberry.com`.
- **Mulberry Retailer ID**
    - Sets the retailer ID generated in the Mulberry system.
- **Private Token**
    - Sets the Mulberry Private Token for merchant authorization, when sending API calls through the backend.
- **Public Token**
  - Sets the Mulberry Public Token for merchant authorization, when requesting warranty product information on the PDP.
- **Enable Post Purchase**
    - Enables/disables the Mulberry "Post Purchase" hook.

### Warranty Product Configuration

When the module is installed, it automatically creates

- A custom virtual product type called `Warranty Product`.
- A product placeholder that is used to store Mulberry warranty information during the customer journey.

When warranty information is retrieved from the Mulberry service, the product name and price are updated on-the-fly. These product placeholders can be found with the following SKUs:

- `mulberry-warranty-product`
- `mulberry-warranty-24-months`
- `mulberry-warranty-36-months`
- `mulberry-warranty-48-months`
- `mulberry-warranty-60-months`

To set a custom image for a warranty product, use the [default Magento product image functionality](https://docs.magento.com/m1/ce/user_guide/catalog/product-images.html).

**IMPORTANT!!!**

Please do **not** modify the SKU of the placeholder product. Otherwise the system won't be able to recognize and add a warranty product for the original Magento product.

## Technical Documentation

### Product Details Page
As soon as the DOM is fully loaded on the Product Details Page, the Mulberry iframe displaying warranty products is initialized.

### Magento event observers

In order to add a warranty product to the cart, as well as process it during the customer journey, the Mulberry module listens to the following Magento event observers:

- `order_cancel_after` On this event, the module checks if there's any warranty product available on the order. If so, it sends Mulberry cancel API request.

- `sales_order_place_before` On this event, we generate the unique order identifier aka UUID (available by default in Magento 2, but was not there for Magento 1)

- `checkout_cart_product_add_after` On this event, the module checks if the warranty product's hash has been passed as a form request. If so, a Magento warranty product placeholder is loaded using its SKU. Next, a REST API request is made to retrieve the warranty product's information (e.g. name, price, service_type, etc.). All of this data is stored under `warranty_information` of the particular quote item within the `quote_item_option` table.

- `sales_quote_item_set_product` On this event, the module updates the product name of the warranty product (quote item).

- `checkout_submit_all_after` On this event, the module runs the checkout success & post purchase hook. As soon as the order is placed, Magento makes an API call to the Mulberry platform, notifying it that the warranty product has been purchased. The API call is made only if the Magento order contains a warranty product.

### Quote item options modifications

In order to store a warranty product's data, the module uses the following custom product options:

- `warranty_information` This option contains a parsed API response about the warranty product added to the cart (name, price & other information).

- `additional_options` This option uses the default Magento functionality to display custom options applied to the product. This information is displayed on the shopping cart, checkout, and order pages. If there are warranty products, the module stores and displays the following information:

- `Service type`, e.g. "Accidental Damage Replacement".

- `Duration Months`, an integer value that specifies duration of the extended warranty for particular product in months (for example, "36").
