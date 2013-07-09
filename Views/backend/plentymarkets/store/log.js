// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Log}
Ext.define('Shopware.apps.Plentymarkets.store.Log', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Log',
	batch: true,
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getLog}'
		},
		reader: {
			type: 'json',
			root: 'data',
			totalProperty: 'total'
		}
	}
});
// {/block}
