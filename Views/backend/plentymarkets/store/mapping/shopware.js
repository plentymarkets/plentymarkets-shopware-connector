// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Shopware}
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
