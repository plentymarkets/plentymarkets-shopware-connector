// {namespace name=backend/plentyconnector/view}
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

        if (me.isBuilt == true) {
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
                title: '{s name=plentyconnector/view/settings/title}{/s}',
                layout: 'anchor',

                defaults: {
                    labelWidth: 155,
                    anchor: '100%'
                },

                items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/textfield/ApiUrl}{/s}',
                        supportText: 'Tragen Sie hier die URL Ihres plentymarkets-Systems ein. Sie finden diese Information in der plentymarkets-Administration unter <b>Einstellungen » Grundeinstellungen » API-Daten » Host</b>. Die URL muss mit <b>http://</b> oder <b>https://</b> beginnen.',
                        emptyText: 'https://www.ihr-plentymarkets-system.de/',
                        name: 'ApiUrl',
                        allowBlank: false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/textfield/ApiUsername}{/s}',
                        name: 'ApiUsername',
                        allowBlank: false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/settings/textfield/ApiPassword}{/s}',
                        name: 'ApiPassword',
                        allowBlank: false,
                        inputType: 'password'
                    }
                ]
            }
        ];
    }
});
// {/block}
