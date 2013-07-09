// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/settings/Batch}
Ext.define('Shopware.apps.Plentymarkets.store.settings.Batch', {

	extend: 'Ext.data.Store',

	model: 'Shopware.apps.Plentymarkets.model.settings.Batch',

	proxy: {

		type: 'ajax',

		url: '{url action=getSettingsStores}',

		reader: {
			type: 'json',
			root: 'data',
			totalProperty: 'total'
		}
	}
});
// {/block}

