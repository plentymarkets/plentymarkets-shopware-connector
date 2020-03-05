// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/view/settings}

Ext.define('Shopware.apps.PlentyConnector.view.Settings', {
    extend: 'Ext.form.Panel',

    alias: 'widget.plentymarkets-view-settings',

    title: '{s name=plentyconnector/view/settings/title}{/s}',
    autoScroll: true,
    cls: 'shopware-form',
    layout: 'anchor',
    border: false,

    isBuilt: false,

    stores: {},

    defaults: {
        anchor: '100%',
        margin: 10
    },

    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function () {
        this.addEvents('save');
        this.addEvents('test');
    },

    build: function () {
        var me = this;

        if (me.isBuilt) {
            return;
        }

        me.setLoading(true);
        me.add(me.getFieldSets());
        me.addDocked(me.createToolbar());
        me.loadRecord(me.settings);
        me.isBuilt = true;
        me.setLoading(false);
    },

    /**
     * Creates the grid toolbar for the favorite grid
     *
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            cls: 'shopware-toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: ['->',
                {
                    xtype: 'button',
                    text: '{s name=plentyconnector/view/settings/button/test}{/s}',
                    cls: 'secondary',
                    handler: function () {
                        me.fireEvent('test', me);
                    }
                },
                {
                    xtype: 'button',
                    text: '{s name=plentyconnector/view/settings/button/save}{/s}',
                    cls: 'primary',
                    handler: function () {
                        me.fireEvent('save', me);
                    }
                }
            ]
        });
    },

    /**
     * Creates the rows of the settings view.
     */
    getFieldSets: function () {
        return [
            {
                xtype: 'fieldset',
                title: '{s name=plentyconnector/view/settings/credentials}{/s}',
                layout: 'anchor',

                defaults: {
                    labelWidth: 155,
                    anchor: '100%'
                },

                items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/rest_url}{/s}',
                        name: 'rest_url',
                        allowBlank: false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/rest_username}{/s}',
                        name: 'rest_username',
                        allowBlank: false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/rest_password}{/s}',
                        name: 'rest_password',
                        allowBlank: false,
                        inputType: 'password'
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: '{s name=plentyconnector/view/settings/additional}{/s}',
                layout: 'anchor',

                defaults: {
                    labelWidth: 155,
                    anchor: '100%'
                },

                items: [
                    {
                        xtype: 'combobox',
                        fieldLabel: '{s namespace=backend/article/view/main name=variant/settings/type/label}{/s}',
                        name: 'product_configurator_type',
                        allowBlank: false,
                        editable: false,
                        mode: 'local',
                        value: 0,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        store: [
                            [0, '{s namespace=backend/article/view/main name=variant/configurator/types/standard}{/s}'],
                            [1, '{s namespace=backend/article/view/main name=variant/configurator/types/selection}{/s}'],
                            [2, '{s namespace=backend/article/view/main name=variant/configurator/types/picture}{/s}']
                        ]
                    },
                    {
                        xtype: 'combobox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/product_configurator_type/label}{/s}',
                        name: 'variation_number_field',
                        allowBlank: false,
                        editable: false,
                        mode: 'local',
                        value: 'number',
                        displayField: 'label',
                        valueField: 'id',
                        triggerAction: 'all',
                        selectOnFocus: true,
                        store: [
                            ['number', '{s name=plentyconnector/view/settings/additional/product_configurator_type/number}{/s}'],
                            ['id', '{s name=plentyconnector/view/settings/additional/product_configurator_type/variation_id}{/s}']
                        ]
                    },
                    {
                        xtype: 'combobox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/order_origin/label}{/s}',
                        name: 'order_origin',
                        editable: false,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        displayField: 'name',
                        valueField: 'id',
                        value: '0.00',
                        store: Ext.create('Shopware.apps.PlentyConnector.store.additional.OrderOrigin').load()
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/check_price_origin/label}{/s}',
                        boxLabel: '{s name=plentyconnector/view/settings/additional/check_price_origin/boxLabel}{/s}',
                        uncheckedValue: 0,
                        inputValue: 1,
                        anchor: '100%',
                        name: 'check_price_origin'
                    },
                    {
                        xtype: 'combobox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/item_warehouse/label}{/s}',
                        name: 'item_warehouse',
                        editable: false,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        displayField: 'name',
                        valueField: 'id',
                        value: 0,
                        store: Ext.create('Shopware.apps.PlentyConnector.store.additional.ItemWarehouse').load()
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/item_notification/label}{/s}',
                        boxLabel: '{s name=plentyconnector/view/settings/additional/item_notification/boxLabel}{/s}',
                        uncheckedValue: 0,
                        inputValue: 1,
                        anchor: '100%',
                        name: 'item_notification'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/check_active_main_variation/label}{/s}',
                        boxLabel: '{s name=plentyconnector/view/settings/additional/check_active_main_variation/boxLabel}{/s}',
                        uncheckedValue: 0,
                        inputValue: 1,
                        anchor: '100%',
                        name: 'check_active_main_variation'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/import_variations_without_stock/label}{/s}',
                        boxLabel: '{s name=plentyconnector/view/settings/additional/import_variations_without_stock/boxLabel}{/s}',
                        uncheckedValue: 0,
                        inputValue: 1,
                        checked: true,
                        anchor: '100%',
                        name: 'import_variations_without_stock'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name=plentyconnector/view/settings/additional/surcharge_as_product/label}{/s}',
                        boxLabel: '{s name=plentyconnector/view/settings/additional/surcharge_as_product/boxLabel}{/s}',
                        uncheckedValue: 0,
                        inputValue: 1,
                        checked: false,
                        anchor: '100%',
                        name: 'surcharge_as_product'
                    },
                    {
                        xtype: 'combobox',
                        fieldLabel: '{s name=plentyconnector/view/settings/system_currency/label}{/s}',
                        name: 'system_currency',
                        editable: false,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        displayField: 'currency',
                        valueField: 'currency',
                        store: Ext.create('Shopware.apps.Base.store.Currency').load()
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/amazon_pay_key}{/s}',
                        name: 'amazon_pay_key',
                        allowBlank: true,
                        inputType: 'password'
                    },
					{
                        xtype: 'combobox',
                        fieldLabel: '{s name=plentyconnector/view/settings/rest_items_per_page}{/s}',
                        name: 'rest_items_per_page',
                        allowBlank: false,
                        editable: false,
                        mode: 'local',
                        value: 100,
						displayField: 1,
						valueField: 0,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        store: [
                            [50, '50'],
                            [100, '100'],
                            [150, '150'],
							[200, '200'],
							[250, '250'],
							[300, '300'],
							[350, '350'],
							[400, '400'],
							[450, '450'],
							[500, '500']
                        ]
					}
                ]
            },
            // {block name="backend/plentyconnector/view/settings/fields"}{/block}
            {
                xtype: 'label',
                text: '{s name=plentyconnector/view/settings/helperLink/text}{/s}',
                cls: 'x-form-item-label',
                autoEl: {
                    tag: 'a',
                    href: 'https://github.com/arvatis/shopware-psc7-helper-plugin',
                    html: ' plentymarkets Shopware Connector Helper',
                    target: '_blank'
                }
            }
        ];
    }
});
// {/block}
