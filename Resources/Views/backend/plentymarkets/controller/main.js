// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/main}

/**
 * The main controller builds the main window of plentymarkets plugin and initializes settings and
 * is extended by die Ext app controller "Ext.app.Controller".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.controller.Main', {

	extend: 'Ext.app.Controller',

	mainWindow: null,

	init: function()
	{
		var me = this;

		var store = me.subApplication.getStore('Settings');

		me.mainWindow = me.subApplication.getView('Main').create().show();
		me.mainWindow.setLoading(true);

		store.load({
			callback: function(records)
			{
				var settings = records[0];
				me.mainWindow.settingsStore = store;
				me.mainWindow.settings = settings;
				me.mainWindow.createTabPanel();
				me.mainWindow.setLoading(false);
				me.subApplication.setAppWindow(me.mainWindow);
			}
		});

		me.callParent(arguments);
	}

});
// {/block}
