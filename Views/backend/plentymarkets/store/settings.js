// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Settings}

/**
 * The settings store is used to load and save the settings model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Settings', {
	extend: 'Ext.data.Store',
	storeId: 'plentymarkets-store-settings',
	model: 'Shopware.apps.Plentymarkets.model.Settings',
	batch: true,
	autoLoad: false,
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getSettingsList}',
			save: '{url action=setSettingsList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
