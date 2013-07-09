// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Plentymarkets}
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Plentymarkets', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/plentymarkets/model/mapping/Plentymarkets/fields"}{/block}
	{
		name: 'id',
		type: 'string'
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
