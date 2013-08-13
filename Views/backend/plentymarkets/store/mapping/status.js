// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Status}

/**
 * The status store is used to load the status model for data mapping and
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle
 * model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.mapping.Status', {
	extend: 'Ext.data.Store',
	autoLoad: false,
	model: 'Shopware.apps.Plentymarkets.model.mapping.Status',

	proxy: {
		type: 'ajax',

		api: {
			read: '{url  action="getMappingStatus"}'
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
