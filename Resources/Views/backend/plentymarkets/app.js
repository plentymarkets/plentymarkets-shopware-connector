// {namespace name=backend/Plentymarkets}
// {block name=backend/Plentymarkets/application}

/**
 * The class app defines the structure of the javascript classes, which are grouped as follows:
 * controllers, views, stores and models. The views build the graphical content and handle events.
 * The stores act as a data exchange layer between the views and the models. The models are well-defined
 * data structures, which are used for data exchange. And finally the controllers manage all processes.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets', {

	name: 'Shopware.apps.Plentymarkets',
	extend: 'Enlight.app.SubApplication',
	loadPath: '{url action=load}',
	bulkLoad: true,

	controllers: [
	    'Main',
	    'Settings'
	],

	views: [
	    'Api',
	    'Main',
	    'mapping.Tab',
	    'mapping.Main',
	    'Misc',
	    'Settings'
	],

	stores: [
		'settings.Batch',
		'mapping.Row',
		'mapping.Information',
		'mapping.TransferObject',
		'Settings'
	],

	models: [
	    'mapping.Row',
	    'mapping.Information',
	    'mapping.TransferObject',
	    'Multishop',
	    'Orderstatus',
	    'Manufacturer',
	    'Referrer',
	    'settings.Batch',
	    'Settings',
	    'Warehouse',
        'Payment'
	],

	launch: function()
	{
		var me = this, mainController = me.getController('Main');

		return mainController.mainWindow;
	}
});
// {/block}
