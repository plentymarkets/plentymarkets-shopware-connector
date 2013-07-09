// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Referrer}
Ext.define('Shopware.apps.Plentymarkets.model.Referrer', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Referrer/fields"}{/block}
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
