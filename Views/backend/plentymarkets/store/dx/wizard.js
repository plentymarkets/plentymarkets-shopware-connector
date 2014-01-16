// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/dx/Wizard}

/**
 * The Wizard store is used to load the Wizard import and export data
 * and is extended by the Ext data store "Ext.data.Store". With Ext stores you
 * can handle model data like adding, getting and removing models in a defined
 * store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.dx.Wizard', {
	
	extend: 'Ext.data.Store',
	
	model: 'Shopware.apps.Plentymarkets.model.dx.Wizard',
	
	storeId: 'plentymarkets-store-dx-wizard',
	
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getDxWizard}'
		},
		reader: {
			type: 'json',
			root: 'data'
        }
	}
});
// {/block}
