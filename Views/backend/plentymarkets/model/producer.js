// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Producer}

/**
 * The producer data model defines the different data fields for producer lists and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Producer', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Producer/fields"}{/block}
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
			read: '{url action="getProducerList"}'
        },

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
