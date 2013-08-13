// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/export}

/**
 * The export controller handles all kinds of data export events. For example data, that has to be exported initialy
 * to plentymarkets, runs throw this export controller by eventhandling. It is extended by the Ext app controller "Ext.app.Controller".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
