// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Settings}
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
