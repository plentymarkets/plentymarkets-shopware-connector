// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Multishop}

/**
 * This data model class defines the different data fields for shops and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
