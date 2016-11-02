// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/settings/Batch}

/**
 * The batch store is used to load the batch model, which contains model, function and key associations and 
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle model data like adding, 
 * getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.settings.Batch', {

	extend: 'Ext.data.Store',

	model: 'Shopware.apps.Plentymarkets.model.settings.Batch',

	proxy: {

		type: 'ajax',

		url: '{url action=getSettingsViewData}',

		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}

