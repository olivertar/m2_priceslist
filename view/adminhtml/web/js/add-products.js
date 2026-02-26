define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Button, registry, $, alert, modal, $t) {
    'use strict';

    var discountTypes = [
        { value: 'fixed_price', label: $t('Fixed Price Override') },
        { value: 'fixed_amount', label: $t('Fixed Amount Discount') },
        { value: 'percentage', label: $t('Percentage Discount') }
    ];

    function buildSelectHtml(selectedValue) {
        var html = '<select class="admin__control-select pricing-discount-type" style="width:180px">';
        discountTypes.forEach(function (opt) {
            var sel = opt.value === selectedValue ? ' selected' : '';
            html += '<option value="' + opt.value + '"' + sel + '>' + opt.label + '</option>';
        });
        html += '</select>';
        return html;
    }

    function buildDialog(products, onConfirm) {
        var $table = $([
            '<table class="data-grid" style="width:100%;border-collapse:collapse;margin-top:10px">',
            '<thead><tr>',
            '<th style="padding:6px 8px;text-align:left">' + $t('Product') + '</th>',
            '<th style="padding:6px 8px;text-align:left">' + $t('SKU') + '</th>',
            '<th style="padding:6px 8px;text-align:left">' + $t('Discount Type') + '</th>',
            '<th style="padding:6px 8px;text-align:right">' + $t('Amount') + '</th>',
            '<th style="padding:6px 8px;text-align:right">' + $t('Qty') + '</th>',
            '<th style="padding:6px 8px;text-align:right">' + $t('Price') + '</th>',
            '</tr></thead>',
            '<tbody id="pricing-rows"></tbody>',
            '</table>'
        ].join(''));

        var $tbody = $table.find('#pricing-rows');
        products.forEach(function (p) {
            var $row = $([
                '<tr data-sku="' + p.sku + '">',
                '<td style="padding:6px 8px">' + p.name + '</td>',
                '<td style="padding:6px 8px">' + p.sku + '</td>',
                '<td style="padding:6px 8px">' + buildSelectHtml('fixed_price') + '</td>',
                '<td style="padding:6px 8px;text-align:right">',
                '  <input type="number" step="0.01" class="admin__control-text pricing-amount"',
                '    value="' + p.price.toFixed(2) + '" style="width:90px;text-align:right">',
                '</td>',
                '<td style="padding:6px 8px;text-align:right">',
                '  <input type="number" step="0.0001" class="admin__control-text pricing-qty"',
                '    value="1.0000" style="width:90px;text-align:right">',
                '</td>',
                '<td style="padding:6px 8px;text-align:right">',
                '  <input type="number" step="0.01" class="pricing-price"',
                '    value="' + p.price.toFixed(2) + '" style="width:90px;text-align:right">',
                '</td>',
                '</tr>'
            ].join(''));
            $tbody.append($row);
        });

        var $wrap = $('<div></div>').append($table);

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $t('Set Pricing for Selected Products'),
            buttons: [
                {
                    text: $t('Cancel'),
                    class: 'action-secondary',
                    click: function () { this.closeModal(); }
                },
                {
                    text: $t('Confirm & Add'),
                    class: 'action-primary',
                    click: function () {
                        var self = this;
                        var rows = [];
                        $wrap.find('tr[data-sku]').each(function () {
                            var $tr = $(this);
                            rows.push({
                                sku: $tr.data('sku'),
                                discount_type: $tr.find('.pricing-discount-type').val(),
                                amount: parseFloat($tr.find('.pricing-amount').val()) || 0,
                                qty: parseFloat($tr.find('.pricing-qty').val()) || 1,
                                price: parseFloat($tr.find('.pricing-price').val()) || 0
                            });
                        });
                        self.closeModal();
                        onConfirm(rows);
                    }
                }
            ]
        };

        modal(options, $wrap);
        $wrap.modal('openModal');
    }

    return Button.extend({
        defaults: {
            addUrl: '',
            getProductsUrl: '',
            itemListingName: 'priceslist_form.priceslist_form.items.priceslist_item_listing',
            modalName: 'priceslist_form.priceslist_form.items.modal',
            formProviderName: 'priceslist_form.priceslist_form_data_source'
        },

        /**
         * Click handler – collect selections, fetch product details, show pricing dialog
         */
        action: function () {
            var self = this,
                modal = registry.get(this.modalName),
                itemListing = registry.get(this.itemListingName),
                formProvider = registry.get(this.formProviderName),
                selectionsProvider = registry.get('product_listing.product_listing.product_columns.ids');

            if (!selectionsProvider) {
                console.error('Selections provider not found');
                alert({ content: $t('Could not read product selections. Please try again.') });
                return;
            }

            var selectionsData = selectionsProvider.getSelections();
            var selectedIds = selectionsData.selected;

            if (!selectedIds || selectedIds.length === 0) {
                alert({ content: $t('Please select at least one product.') });
                return;
            }

            var priceListId = formProvider ? formProvider.get('data.entity_id') : null;
            if (!priceListId) {
                alert({ content: $t('Please save the Price List first before adding products.') });
                return;
            }

            // 1. Close the product selection modal
            if (modal) {
                modal.closeModal();
            }

            // 2. Fetch product details to pre-fill the pricing dialog
            $.ajax({
                url: self.getProductsUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    selected: selectedIds,
                    form_key: window.FORM_KEY
                },
                success: function (response) {
                    if (response.error || !response.items) {
                        alert({ content: response.message || $t('Could not load product details.') });
                        return;
                    }

                    // 3. Show pricing dialog; onConfirm receives the rows
                    buildDialog(response.items, function (rows) {
                        $.ajax({
                            url: self.addUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                price_list_id: priceListId,
                                products: rows,
                                form_key: window.FORM_KEY
                            },
                            success: function (addResponse) {
                                if (addResponse.error) {
                                    alert({ content: addResponse.message || $t('An error occurred.') });
                                } else if (itemListing) {
                                    itemListing.reload();
                                }
                            },
                            error: function () {
                                alert({ content: $t('An AJAX error occurred while saving.') });
                            }
                        });
                    });
                },
                error: function () {
                    alert({ content: $t('An AJAX error occurred while loading products.') });
                }
            });
        }
    });
});
