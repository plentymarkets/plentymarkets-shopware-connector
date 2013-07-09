// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Warehouse}
Ext.define('Shopware.apps.Plentymarkets.model.Warehouse', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Warehouse/fields"}{/block}
	{
		name: 'id',
		type: 'integer'
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
