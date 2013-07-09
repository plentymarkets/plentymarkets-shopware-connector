// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/export}
Ext.define('Shopware.apps.Plentymarkets.controller.Export', {

	extend: 'Ext.app.Controller',

	init: function()
	{
		var me = this;

		//
		me.control({
			'plentymarkets-view-dx-initial': {
				handle: me.handle
			}
		});

		me.callParent(arguments);
	},

	handle: function(record, action)
	{
		record.set('ExportAction', action)
		record.save();
	}

});
// {/block}
