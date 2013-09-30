// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/settings}

/**
 * The settings controller mainly handles event functions like saving data from different views and
 * is extended by the Ext app controller "Ext.app.controller".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.controller.Settings', {

	extend: 'Ext.app.Controller',

	init: function()
	{
		var me = this;

		me.control({
			'plentymarkets-view-settings': {
				save: me.onSave,
				refresh: me.onRefresh
			},
			'plentymarkets-view-api': {
				save: me.onSave,
				test: me.onTest
			},
			'plentymarkets-view-start': {
				save: me.onSave,
				check: me.onCheck
			}
		});

		me.callParent(arguments);
	},

	onTest: function(view)
	{
		var form = view.getForm();
		Ext.Ajax.request({
			url: '{url action=testApiCredentials}',
			params: {
				ApiWsdl: form.findField("ApiWsdl").getValue(),
				ApiUsername: form.findField("ApiUsername").getValue(),
				ApiPassword: form.findField("ApiPassword").getValue()
			},
			success: function(response)
			{
				response = Ext.decode(response.responseText);
				if (response.success)
				{
					Shopware.Notification.createGrowlMessage('Daten gültig', 'Die Daten sind gültig');
				}
				else
				{
					Shopware.Notification.createGrowlMessage('Daten ungültig', 'Die Daten sind ungültig');
				}
			}
		});
	},

	onCheck: function(view)
	{
		view.setLoading(true);
		view.main.settingsStore.load({
			callback: function(data, operation)
			{
				view.settings = data[0];
				view.loadRecord(data[0]);
				view.main.setTabAvailability();
				view.setLoading(false);
			}
		});
	},

	onRefresh: function(view)
	{
		view.loadStores();
	},

	onSave: function(view)
	{
		view.setLoading(true);
		view.getForm().updateRecord(view.settings);
		view.settings.save({
			callback: function(data, operation)
			{
				view.loadRecord(data);
				if (view != view.main.start)
				{
					view.main.start.loadRecord(data);
				}
				view.main.setTabAvailability();
				view.setLoading(false);
				Shopware.Notification.createGrowlMessage('Einstellungen gespeichert', 'Die Einstellungen wurden gespeichert');
			}
		});
	}

});
// {/block}
