// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Status}
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Status', {

	extend: 'Ext.data.Model',

	fields: [{
		name: 'name',
		type: 'string'
	}, {
		name: 'open',
		type: 'integer'
	}],

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
