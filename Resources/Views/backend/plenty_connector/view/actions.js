// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/view/actions}

Ext.define('Shopware.apps.PlentyConnector.view.Actions', {
    extend: 'Ext.form.Panel',

    alias: 'widget.plentymarkets-view-actions',

    title: '{s name=plentyconnector/view/actions/title}{/s}',
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
        this.addEvents('syncItem');
    },

    build: function () {
        var me = this;

        if (me.isBuilt) {
            return;
        }

        me.setLoading(true);
        me.add(me.getFieldSets());
        me.loadRecord(me.actions);
        me.isBuilt = true;
        me.setLoading(false);
    },

    /**
     * Creates the rows of the actions view.
     */
    getFieldSets: function () {
		var me = this;
		
        return [
            {
                xtype: 'fieldset',
                title: '{s name=plentyconnector/view/actions/item_import}{/s}',
                layout: 'anchor',

                defaults: {
                    labelWidth: 155,
                    anchor: '100%'
                },

                items: [
                    {
                    	xtype: 'textfield',
                        fieldLabel: '{s name=plentyconnector/view/actions/item_import/item_id}{/s}',
                        emptyText: '{s name=plentyconnector/view/actions/item_import/item_id}{/s}',
                        name: 'item_id',
                        width: '10%',
                        allowBlank: false,
						maskRe: /[0-9]/
                  	},
                  	{
                      	xtype: 'button',
                      	text: 'Jetzt abgleichen',
                     	cls: 'primary small',
                        handler: function () {
							var form = me.getForm();
							var itemId = form.findField("item_id").getValue();
							
							if (itemId.length > 0 && itemId > 0) {
								me.fireEvent('syncItem', me);
							}
                   		}
                  	}
                ]
            }
            // {block name="backend/plentyconnector/view/actions/fields"}{/block}
        ];
    }
});
// {/block}
