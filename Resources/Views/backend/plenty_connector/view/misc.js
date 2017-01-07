// {namespace name=backend/plentyconnector/view}
// {block name=backend/plentyconnector/view/misc}

Ext.define('Shopware.apps.PlentyConnector.view.Misc', {
    extend: 'Ext.form.Panel',

    alias: 'widget.plentymarkets-view-misc',

    title: '{s name=plentyconnector/view/misc/title}Verschiedenes{/s}',
    autoScroll: true,
    cls: 'shopware-form',
    layout: 'anchor',
    border: false,

    isBuilt: false,

    defaults: {
        anchor: '100%',
        margin: 10,
        labelWidth: '33%'
    },

    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.init();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function () {
        this.addEvents('syncItem');
    },

    init: function () {
        var me = this;

        if (!me.isBuilt) {
            me.build();
        }
    },

    build: function () {
        var me = this;

        me.items = [{
            xtype: 'fieldset',
            title: 'Datenabgleich',
            defaults: {
                anchor: '100%',
                labelWidth: '33%'
            },
            items: [{
                xtype: 'fieldcontainer',
                fieldLabel: 'Artikel abgleichen',
                layout: 'hbox',
                items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: '',
                        emptyText: 'plentymarkets Artikel ID',
                        id: 'plenty-itemId',
                        width: '50%',
                        allowBlank: true
                    },
                    {
                        xtype: 'button',
                        text: 'Jetzt abgleichen',
                        cls: 'primary small',
                        handler: function () {
                            me.fireEvent('syncItem', me);
                        }
                    }
                ]
            }]
        }];

        me.isBuilt = true;
    }
});
// {/block}
