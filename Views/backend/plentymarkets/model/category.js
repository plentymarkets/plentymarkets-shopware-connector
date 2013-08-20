// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Category}

/**
 * The category data model defines the different data fields for a category list and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Category', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Category/fields"}{/block}
	{
		name: 'id',
		type: 'integer'
	}, {
		name: 'name',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		api: {
			read: '{url action="getCategoryList"}',
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
