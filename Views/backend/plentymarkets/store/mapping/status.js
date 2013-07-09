// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Status}
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
