// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Plentymarkets}

/**
 * The plentymarkets data model defines the different data fields for data mapping on the side of plentymarkets and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
