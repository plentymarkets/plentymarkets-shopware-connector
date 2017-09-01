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
                        displayField: 'label',
                        valueField: 'id',
                        store: new Ext.data.SimpleStore({
                            fields:['id', 'label'],
                            data: [
                                [0,  '{s namespace=backend/article/view/main name=variant/configurator/types/standard}{/s}'],
                                [1, '{s namespace=backend/article/view/main name=variant/configurator/types/selection}{/s}'],
                                [2, '{s namespace=backend/article/view/main name=variant/configurator/types/picture}{/s}']
                            ]
                        })
                    }
                ]
            }
            // {block name="backend/plentyconnector/view/settings/fields"}{/block}
        ];
    }
});
// {/block}
