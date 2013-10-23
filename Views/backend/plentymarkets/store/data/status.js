// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/data/Status}

/**
 * The data store is used to load the data model data and is extended by the Ext
 * data store "Ext.data.Store". With Ext stores you can handle model data like
 * adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.data.Status', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.data.Status',
	batch: true,
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getDataIntegrityInvalidList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
