// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/export}

/**
 * The export controller handles all kinds of data export events. For example
 * data, that has to be exported initialy to plentymarkets, runs throw this
 * export controller by eventhandling. It is extended by the Ext app controller
 * "Ext.app.Controller".
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

	handle: function(record, action, view)
	{
		record.save({
			params: {
				doAction: action
			},
			success: function(a, operation, c)
			{
				view.store.load();
				view.wizardStore.load({
					callback: function(data)
					{
						view.wizard = data[0];
						view.setToolbarText();
					}
				});
				Shopware.Notification.createGrowlMessage(
					'Aktion ausgeführt', operation.request.scope.reader.jsonData["message"]
				);
			}
		});
	}

});
// {/block}
