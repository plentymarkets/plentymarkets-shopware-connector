// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/dx/Continuous}

/**
 * The continuous store is used to load the continuous import and export data
 * and is extended by the Ext data store "Ext.data.Store". With Ext stores you
 * can handle model data like adding, getting and removing models in a defined
 * store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.dx.Continuous', {
	
	extend: 'Ext.data.Store',
	
	model: 'Shopware.apps.Plentymarkets.model.dx.Continuous',
	
	groupField: 'Section',
	
	storeId: 'plentymarkets-store-dx-continuous',
	
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getDxContinuous}'
		},
		reader: {
			type: 'json',
			root: 'data',
			totalProperty: 'total'
		}
	}
});
// {/block}
