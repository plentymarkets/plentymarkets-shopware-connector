// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Orderstatus}
Ext.define('Shopware.apps.Plentymarkets.model.Orderstatus', {

	extend: 'Ext.data.Model',

	fields: [{
		name: 'status',
		type: 'float'
	}, {
		name: 'name',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
