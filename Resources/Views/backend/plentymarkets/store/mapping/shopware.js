// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Shopware}

/**
 * The shopware store is used to load the data for mapping on the side of shopware and
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle
 * model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.mapping.Shopware', {
	extend: 'Ext.data.Store',
	autoLoad: false,
	model: 'Shopware.apps.Plentymarkets.model.mapping.Shopware',
	remoteFilter: false,
	remoteSort: false,

	groupField: 'groupName',

	pageSize: 100,

	proxy: {
		type: 'ajax',

		api: {
			read: '{url  action="getMappingData"}'
		},

		reader: {
			type: 'json',
			root: 'data',
			totalProperty: 'total'
		}
	}
});
// {/block}
