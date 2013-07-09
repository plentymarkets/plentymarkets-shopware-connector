// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Producer}
Ext.define('Shopware.apps.Plentymarkets.store.Producer', {
	extend: 'Ext.data.Store',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getProducerList}',
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	},
	model: 'Shopware.apps.Plentymarkets.model.Producer'
});
// {/block}
