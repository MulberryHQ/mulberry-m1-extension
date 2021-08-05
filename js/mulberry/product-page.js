/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
jQuery(document).ready(function () {
    var MulberryProductPage = {
        element: jQuery("#product_addtocart_form"),
        productUpdateTimer: null,
        mulberryProductUpdateDelay: 1000,
        mulberryOverlayActive: false,
        swatchElement: '[data-role=swatch-options]',
        swatchAttributeElement: 'div.swatch-attribute',
        warrantyHashElement: '#warranty_hash',
        warrantySkuElement: '#warranty_sku',

        /**
         * Register events
         */
        addProductListeners: function addProductListeners() {
            this.prepareMulberryProduct();

            this.element.on('updateMulberryProduct', function (evt, newPrice) {
                this.updateMulberryProduct(newPrice);
            }.bind(this));

            this.element.on('toggleWarranty', function (evt, params) {
                this.toggleWarranty(params.data, params.isSelected);
            }.bind(this));
        },

        /**
         * Init Mulberry product
         */
        registerProduct: function registerProduct() {
            var self = this;

            window.mulberry.core.init({
                publicToken: window.mulberryConfigData.publicToken
            }).then(
                self.registerOffers()
            );
        },

        /**
         * Register inline & modal offers
         */
        registerOffers: function registerOffers() {
            var self = this;

            window.mulberry.core.getWarrantyOffer(window.mulberryProductData.product)
                .then(function (offers) {
                    if (offers.length) {
                        var settings = window.mulberry.core.settings;

                        if (settings.has_modal) {
                            window.mulberry.modal.init({
                                offers,
                                settings,
                                onWarrantySelect: function(warranty) {
                                    self.toggleMulberryWarranty(warranty, true);

                                    window.mulberry.modal.close();
                                    self.mulberryOverlayActive = true;

                                    if ("undefined" !== typeof productAddToCartForm && productAddToCartForm) {
                                        productAddToCartForm.submit();
                                    }

                                    self.mulberryOverlayActive = false;

                                    /**
                                     * Reset value for warranty element
                                     */
                                    jQuery(this.warrantyHashElement).val('');
                                },
                                onWarrantyDecline: function() {
                                    window.mulberry.modal.close();
                                    self.mulberryOverlayActive = true;

                                    if ("undefined" !== typeof productAddToCartForm && productAddToCartForm) {
                                        productAddToCartForm.submit();
                                    }

                                    self.mulberryOverlayActive = false;
                                }
                            });
                        }

                        if (settings.has_inline) {
                            window.mulberry.inline.init({
                                offers: offers,
                                settings: settings,
                                selector: '.mulberry-inline-container',
                                onWarrantyToggle: function(warranty) {
                                    self.toggleWarranty(warranty.offer, warranty.isSelected);
                                }
                            });
                        }
                    }
                });
        },

        /**
         * Update warranty product's hash
         *
         * @param data
         * @param isSelected
         */
        toggleWarranty: function toggleWarranty(data, isSelected) {
            var selectedWarrantyHash = '',
                warrantyHashElement = jQuery(this.warrantyHashElement),
                warrantySkuElement = jQuery(this.warrantySkuElement);

            if (data) {
                selectedWarrantyHash = isSelected && data ? data.warranty_hash : '';
            }

            warrantySkuElement.val(window.mulberryProductData.product.id);
            warrantyHashElement.val(selectedWarrantyHash);
        },

        /**
         * Init Mulberry API library
         */
        initLibrary: function initLibrary() {
            if (window.mulberry) {
                this.registerProduct();
                this.addProductListeners();
                this.registerPriceUpdate();
                this.registerModal();
            } else {
                setTimeout(
                    function () {
                        this.initLibrary();
                    }.bind(this),
                    50
                );
            }
        },

        /**
         * Retrieve selected simple product ID
         *
         * @returns {null}
         */
        getSimpleProductId: function getSimpleProductId() {
            var productCandidates = [];

            jQuery.each(spConfig.settings, function (selectIndex, select) {
                var attributeId = select.id.replace("attribute", "");
                var selectedValue = select.options[select.selectedIndex].value;

                jQuery.each(spConfig.config.attributes[attributeId].options, function (optionIndex,
                                                                                       option) {
                    if (option.id === selectedValue) {
                        var optionProducts = option.products;

                        if (productCandidates.length === 0) {
                            productCandidates = optionProducts;
                        } else {
                            var productIntersection = [];
                            jQuery.each(optionProducts, function (productIndex, productId) {
                                if (productCandidates.indexOf(productId) > -1) {
                                    productIntersection.push(productId);
                                }
                            });
                            productCandidates = productIntersection;
                        }
                    }
                });
            });

            return productCandidates.length === 1 ? productCandidates[0] : null;
        },

        /**
         * Prepare selected product SKU for Mulberry API
         */
        prepareSimpleSku: function prepareSimpleSku() {
            var configurableProductsHandler =
                typeof spConfig !== "undefined" ? spConfig : false;

            if (!configurableProductsHandler) {
                return;
            }

            return configurableProductsHandler
                ? configurableProductsHandler.config.simple_skus[
                    this.getSimpleProductId()
                    ]
                : window.mulberryProductData.originalSku;
        },

        /**
         * Prepare selected options SKU for Mulberry API
         */
        prepareOptionsSku: function prepareOptionsSku() {
            var customOptionsHandler =
                    typeof opConfig !== "undefined" ? opConfig : false,
                result = "";

            if (!customOptionsHandler) {
                return result;
            }

            var optionElements = jQuery(".product-custom-option"),
                customOptionsConfig = customOptionsHandler.config,
                self = this;

            optionElements.each(function () {
                var optionValue = self._getOptionSku(jQuery(this), customOptionsConfig);

                if (optionValue !== "") {
                    result += "-" + optionValue;
                }
            });

            return result;
        },

        /**
         * Prepare selected product variant to be passed to Mulberry API
         */
        prepareMulberryProduct: function prepareMulberryProduct(newPrice) {
            var sku = this.prepareSimpleSku()
                ? this.prepareSimpleSku()
                : window.mulberryProductData.originalSku,
                customOptionsSku = this.prepareOptionsSku();

            if (customOptionsSku !== "") {
                sku += customOptionsSku;
            }

            var updatedInformation = {
                id: sku,
                price: newPrice ? newPrice : window.mulberryProductData.originalPrice
            };

            return Object.assign(window.mulberryProductData.product, updatedInformation);
        },

        /**
         * Run Mulberry product update
         *
         * @param newPrice
         */
        updateMulberryProduct: function updateMulberryProduct(newPrice) {
            this.prepareMulberryProduct(newPrice);
            var settings = window.mulberry.core.settings;

            if (!window.mulberry || !settings || (!settings.has_modal && !settings.has_inline)) {
                return;
            }

            /**
             * Run update only when product configuration has been changed
             *
             * @type {number}
             */
            clearTimeout(this.productUpdateTimer);
            this.productUpdateTimer = setTimeout(
                function () {
                    if (this.hasConfigurationChanges()) {
                        window.mulberry.core.getWarrantyOffer(window.mulberryProductData.activeSelection).then(function (offers) {
                            var settings = window.mulberry.core.settings;

                            if (settings.has_modal) {
                                window.mulberry.modal.updateOffer(window.mulberry.core.offers);
                            }

                            if (settings.has_inline) {
                                window.mulberry.inline.updateOffer(window.mulberry.core.offers);
                            }
                        });

                        jQuery(this.warrantySkuElement).val(window.mulberryProductData.activeSelection.id);
                    }
                }.bind(this), this.mulberryProductUpdateDelay);
        },

        /**
         * Check, if product has configuration changes and we need to trigger Mulberry product update
         *
         * @returns {boolean}
         */
        hasConfigurationChanges: function hasConfigurationChanges() {
            /**
             * Make a copy of the new object rather than variable assignment,
             * this is required to avoid the issue when the currentConfig is updated along with the newConfig value
             * @type {any}
             */
            var newConfig = JSON.parse(JSON.stringify(window.mulberryProductData.product));
            var currentConfig = window.mulberryProductData.activeSelection;

            window.mulberryProductData.activeSelection = newConfig;

            return !this.isEqual(currentConfig, newConfig);
        },

        /**
         *
         * @param element
         * @param optionsConfig
         * @private
         */
        _getOptionSku: function getOptionValue(element, optionsConfig) {
            var optionValue = element.val(),
                optionId = this.findOptionId(element[0]),
                optionType = element.prop("type"),
                optionConfig = optionsConfig[optionId];

            switch (optionType) {
                case "text":
                case "textarea":
                    optionValue = optionValue ? optionConfig.value_sku : "";
                    break;
                case "radio":
                    optionValue =
                        element.is(":checked") &&
                        optionConfig[optionValue] &&
                        optionConfig[optionValue].value_sku
                            ? optionConfig[optionValue].value_sku
                            : "";
                    break;
                case "select-one":
                    optionValue =
                        optionConfig[optionValue] && optionConfig[optionValue].value_sku
                            ? optionConfig[optionValue].value_sku
                            : "";
                    break;
                case "select-multiple":
                    optionValue = "";

                    _.each(optionConfig, function (row, optionValueCode) {
                        optionValue +=
                            _.contains(optionValue, optionValueCode) && row.value_sku
                                ? row.value_sku
                                : "";
                    });
                    break;
                case "checkbox":
                    optionValue =
                        element.is(":checked") && optionConfig[optionValue].value_sku
                            ? optionConfig[optionValue].value_sku
                            : "";
                    break;
                case "file":
                    optionValue =
                        optionValue || element.prop("disabled")
                            ? optionConfig[optionValue].value_sku
                            : "";
                    break;
            }

            return optionValue;
        },

        /**
         * Function that allows to check, if objects/arrays are equal
         *
         * @param value
         * @param other
         * @returns {boolean}
         */
        isEqual: function (value, other) {
            // Get the value type
            var type = Object.prototype.toString.call(value);

            // If the two objects are not the same type, return false
            if (type !== Object.prototype.toString.call(other)) return false;

            // If items are not an object or array, return false
            if (["[object Array]", "[object Object]"].indexOf(type) < 0) return false;

            // Compare the length of the length of the two items
            var valueLen =
                type === "[object Array]" ? value.length : Object.keys(value).length;
            var otherLen =
                type === "[object Array]" ? other.length : Object.keys(other).length;
            if (valueLen !== otherLen) return false;

            // Compare two items
            var compare = function (item1, item2) {
                // Get the object type
                var itemType = Object.prototype.toString.call(item1);

                // If an object or array, compare recursively
                if (["[object Array]", "[object Object]"].indexOf(itemType) >= 0) {
                    if (!MulberryProductPage.isEqual(item1, item2)) return false;
                }

                // Otherwise, do a simple comparison
                else {
                    // If the two items are not the same type, return false
                    if (itemType !== Object.prototype.toString.call(item2)) return false;

                    // Else if it's a function, convert to a string and compare
                    // Otherwise, just compare
                    if (itemType === "[object Function]") {
                        if (item1.toString() !== item2.toString()) return false;
                    } else {
                        if (item1 !== item2) return false;
                    }
                }
            };

            // Compare properties
            if (type === "[object Array]") {
                for (var i = 0; i < valueLen; i++) {
                    if (compare(value[i], other[i]) === false) return false;
                }
            } else {
                for (var key in value) {
                    if (value.hasOwnProperty(key)) {
                        if (compare(value[key], other[key]) === false) return false;
                    }
                }
            }

            // If nothing failed, return true
            return true;
        },

        /**
         * Helper to find ID in name attribute
         * @param   {jQuery} element
         * @returns {undefined|String}
         */
        findOptionId: function (element) {
            var re, id, name;

            if (!element) {
                return id;
            }

            name = jQuery(element).attr("name");

            if (name.indexOf("[") !== -1) {
                re = /\[([^\]]+)?\]/;
            } else {
                re = /_([^\]]+)?_/; // just to support file-type-option
            }

            id = re.exec(name) && re.exec(name)[1];

            if (id) {
                return id;
            }
        },

        /**
         * Add listener for Magento priceUpdate method in order to update iframe with selected product configuration
         */
        registerPriceUpdate: function () {
            var self = this;

            if (Product && "OptionsPrice" in Product) {
                Product.OptionsPrice.prototype.formatPrice = Product.OptionsPrice.prototype.formatPrice.wrap(
                    function (parentMethod, price) {
                        self.element.trigger("updateMulberryProduct", [price.toFixed(2)]);

                        return parentMethod(price);
                    }
                );
            }
        },

        /**
         * Toggle warranty updated event
         *
         * @param data
         * @param isSelected
         */
        toggleMulberryWarranty: function (data, isSelected) {
            this.element.trigger("toggleWarranty", {
                data: data,
                isSelected: isSelected
            });
        },

        /**
         * Override original Magento add to cart action
         */
        registerModal: function () {
            var self = this;

            if ("undefined" !== typeof productAddToCartForm && productAddToCartForm) {
                productAddToCartForm.submit = function (button, url) {
                    if (this.validator.validate()) {
                        var form = this.form;
                        var oldUrl = form.action;

                        if (url) {
                            form.action = url;
                        }
                        var e = null;
                        try {
                            if (
                                self.mulberryOverlayActive ||
                                !window.mulberry ||
                                (!window.mulberry.modal && !window.mulberry.inline)
                            ) {
                                this.form.submit();
                            }

                            if (
                                !self.mulberryOverlayActive &&
                                jQuery(self.warrantyHashElement).val() === ""
                            ) {
                                window.mulberry.modal.open();
                            } else {
                                this.form.submit();
                            }
                        } catch (e) {
                        }
                        this.form.action = oldUrl;
                        if (e) {
                            throw e;
                        }

                        if (button && button !== "undefined") {
                            button.disabled = true;
                        }
                    }
                }.bind(productAddToCartForm);
            }
        }
    };

    MulberryProductPage.initLibrary();
});
