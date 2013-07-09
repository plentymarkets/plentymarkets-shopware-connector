// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Multishop}
Ext.define('Shopware.apps.Plentymarkets.model.Multishop', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Multishop/fields"}{/block}
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
